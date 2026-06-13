<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Auth-geschützter Medien-Proxy (ADR-0015). Die Routen sind nur über eine
 * gültige, zeitlich befristete Signatur erreichbar (middleware "signed");
 * signierte URLs werden ausschließlich in den API-Resources für berechtigte
 * Nutzer erzeugt. Der Objektspeicher (S3/MinIO) bleibt privat – die Dateien
 * werden serverseitig gelesen und gestreamt.
 */
class MediaController extends Controller
{
    public function image(Image $image): StreamedResponse
    {
        return $this->stream($image->path);
    }

    public function thumbnail(Image $image): StreamedResponse
    {
        return $this->stream($image->thumbnail_path ?? $image->path);
    }

    public function avatar(User $user): StreamedResponse
    {
        abort_if($user->avatar_path === null, 404);

        return $this->stream($user->avatar_path);
    }

    private function stream(string $path): StreamedResponse
    {
        $disk = Storage::disk(config('filesystems.media'));

        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    }
}
