<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

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
     * Bild hochladen – Datei landet im Storage (public-Disk), in der DB steht
     * nur der Pfad (ADR-0006).
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

        $file = $request->file('image');
        $path = $file->store("gallery/{$familyId}", 'public');

        // Thumbnail (max. 600px Breite) erzeugen und ablegen – schnellere Galerie
        // (ADR-0014). Verarbeitung hier synchron; später per Queue/Worker auslagerbar.
        $thumbnail = (new ImageManager(new Driver))
            ->decodePath($file->getRealPath())
            ->scaleDown(width: 600)
            ->encode(new JpegEncoder(quality: 75));
        $thumbnailPath = "gallery/{$familyId}/thumbs/".pathinfo($path, PATHINFO_FILENAME).'.jpg';
        Storage::disk('public')->put($thumbnailPath, (string) $thumbnail);

        $image = Image::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'title' => $data['title'] ?? null,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
        ]);

        return (new ImageResource($image))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, Image $image): Response
    {
        $this->authorize('delete', $image);

        Storage::disk('public')->delete(array_filter([$image->path, $image->thumbnail_path]));
        $image->delete();

        return response()->noContent();
    }
}
