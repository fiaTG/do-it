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

        $path = $request->file('image')->store("gallery/{$familyId}", 'public');

        $image = Image::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'title' => $data['title'] ?? null,
            'path' => $path,
        ]);

        return (new ImageResource($image))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, Image $image): Response
    {
        $this->authorize('delete', $image);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->noContent();
    }
}
