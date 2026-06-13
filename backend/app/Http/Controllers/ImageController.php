<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\ImageResource;
use App\Jobs\GenerateThumbnail;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    use InteractsWithFamily;

    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return ImageResource::collection(
            Image::where('family_id', $familyId)->latest()->get()
        );
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
        // Premium ist unbegrenzt.
        $family = $request->user()->family;
        if ($family !== null && ! $family->isPremium()) {
            $limit = (int) config('features.free_limits.gallery_images');
            abort_if(
                Image::where('family_id', $familyId)->count() >= $limit,
                403,
                "Galerie-Limit ($limit Bilder) erreicht. Mit Premium ist der Speicher unbegrenzt.",
            );
        }

        $path = $request->file('image')->store("gallery/{$familyId}", config('filesystems.media'));

        $image = Image::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'title' => $data['title'] ?? null,
            'path' => $path,
        ]);

        // Thumbnail entkoppelt erzeugen (lokal sync, in Produktion async via Worker).
        GenerateThumbnail::dispatch($image);

        return (new ImageResource($image->fresh()))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, Image $image): Response
    {
        $this->authorize('delete', $image);

        Storage::disk(config('filesystems.media'))
            ->delete(array_filter([$image->path, $image->thumbnail_path]));
        $image->delete();

        return response()->noContent();
    }
}
