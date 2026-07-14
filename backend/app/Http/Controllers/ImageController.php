<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\ImageResource;
use App\Jobs\GenerateThumbnail;
use App\Models\Image;
use App\Support\ImageUpload;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    use InteractsWithFamily;

    /**
     * Chronologisch nach Aufnahmedatum (Fallback: Upload-Datum), seitenweise
     * für Infinite Scroll. `meta.total`/`meta.limit` treiben die
     * Freemium-Quota-Anzeige im Frontend (ADR-0013). Papierkorb-Bilder sind
     * durch SoftDeletes automatisch ausgeblendet.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);
        $family = $request->user()->family;

        $images = Image::where('family_id', $familyId)
            ->orderByRaw('COALESCE(taken_at, created_at) DESC')
            ->paginate(60);

        return ImageResource::collection($images)->additional([
            'meta' => [
                'limit' => $family !== null && ! $family->isPremium()
                    ? (int) config('features.free_limits.gallery_images')
                    : null,
            ],
        ]);
    }

    public function show(Request $request, Image $image): ImageResource
    {
        $this->authorize('view', $image);

        return new ImageResource($image);
    }

    /**
     * Bild hochladen – die Datei landet auf der media-Disk (lokal/S3, ADR-0014),
     * in der DB steht nur der Pfad (ADR-0006). Das Thumbnail erzeugt ein Job.
     */
    public function store(Request $request): JsonResponse
    {
        $familyId = $this->familyId($request);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:5120'], // max. 5 MB
        ]);

        // Entitlement-Gate (ADR-0013): Free-Familien haben ein Galerie-Limit,
        // Premium ist unbegrenzt. Papierkorb-Bilder zählen nicht mit (ADR-0020).
        $family = $request->user()->family;
        if ($family !== null && ! $family->isPremium()) {
            $limit = (int) config('features.free_limits.gallery_images');
            abort_if(
                Image::where('family_id', $familyId)->count() >= $limit,
                403,
                "Galerie-Limit ($limit Bilder) erreicht. Mit Premium ist der Speicher unbegrenzt.",
            );
        }

        $meta = ImageUpload::storeStripped($request->file('image'), "gallery/{$familyId}");

        $image = Image::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'title' => $data['title'] ?? null,
            'path' => $meta['path'],
            'width' => $meta['width'],
            'height' => $meta['height'],
            'taken_at' => $meta['taken_at'],
        ]);

        // Thumbnail entkoppelt erzeugen (lokal sync, in Produktion async via Worker).
        GenerateThumbnail::dispatch($image);

        return (new ImageResource($image->fresh()))->response()->setStatusCode(201);
    }

    /**
     * Löschen = in den Papierkorb (ADR-0020): Soft-Delete, die Dateien bleiben
     * bis zum Purge erhalten, damit Wiederherstellen möglich ist.
     */
    public function destroy(Request $request, Image $image): Response
    {
        $this->authorize('delete', $image);

        $image->delete();

        return response()->noContent();
    }

    public function batchDestroy(Request $request): Response
    {
        $images = $this->trashableImages($request);

        foreach ($images as $image) {
            $image->delete();
        }

        return response()->noContent();
    }

    /** Papierkorb der Familie, zuletzt gelöschte zuerst. */
    public function trash(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return ImageResource::collection(
            Image::onlyTrashed()
                ->where('family_id', $familyId)
                ->orderByDesc('deleted_at')
                ->get()
        );
    }

    /**
     * Wiederherstellen aus dem Papierkorb. Prüft für Free-Familien das
     * Galerie-Limit, sonst ließe es sich über den Papierkorb umgehen (ADR-0020).
     */
    public function restore(Request $request): Response
    {
        $familyId = $this->familyId($request);
        $images = $this->trashableImages($request, onlyTrashed: true);

        $family = $request->user()->family;
        if ($images->isNotEmpty() && $family !== null && ! $family->isPremium()) {
            $limit = (int) config('features.free_limits.gallery_images');
            abort_if(
                Image::where('family_id', $familyId)->count() + $images->count() > $limit,
                403,
                "Galerie-Limit ($limit Bilder) erreicht. Bitte Platz schaffen oder Premium aktivieren.",
            );
        }

        foreach ($images as $image) {
            $image->restore();
        }

        return response()->noContent();
    }

    /** Endgültig löschen (nur aus dem Papierkorb): entfernt Dateien und Rows. */
    public function purge(Request $request): Response
    {
        $images = $this->trashableImages($request, onlyTrashed: true);

        foreach ($images as $image) {
            $image->purge();
        }

        return response()->noContent();
    }

    /**
     * Gemeinsames Muster der Batch-Endpunkte: ids validieren, Bilder laden
     * (nicht existierende IDs stillschweigend ignorieren – idempotent) und
     * ERST ALLE autorisieren, dann handeln – keine partielle Ausführung bei 403.
     *
     * @return Collection<int, Image>
     */
    private function trashableImages(Request $request, bool $onlyTrashed = false)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
        ]);

        $query = $onlyTrashed ? Image::onlyTrashed() : Image::query();
        $images = $query->whereIn('id', $data['ids'])->get();

        foreach ($images as $image) {
            $this->authorize('delete', $image);
        }

        return $images;
    }
}
