<?php

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Erzeugt das Galerie-Thumbnail (ADR-0014: entkoppelte Bildverarbeitung).
 *
 * Liest das Original aus der media-Disk (lokal oder S3) und legt das Thumbnail
 * daneben ab. Lokal läuft der Job synchron (QUEUE_CONNECTION=sync), in Produktion
 * asynchron über Redis + Worker.
 */
class GenerateThumbnail implements ShouldQueue
{
    use Queueable;

    public function __construct(public Image $image) {}

    public function handle(): void
    {
        $disk = Storage::disk(config('filesystems.media'));

        $original = $disk->get($this->image->path);
        if ($original === null) {
            return;
        }

        try {
            $thumbnail = (new ImageManager(new Driver))
                ->decode($original)
                ->scaleDown(width: 600)
                ->encode(new JpegEncoder(quality: 75));
        } catch (\Throwable $e) {
            // Nicht verarbeitbares Bild -> Galerie nutzt das Original als Vorschau.
            Log::warning("Thumbnail fehlgeschlagen (Bild {$this->image->id}): {$e->getMessage()}");

            return;
        }

        $directory = dirname($this->image->path);
        $filename = pathinfo($this->image->path, PATHINFO_FILENAME).'.jpg';
        $thumbnailPath = "{$directory}/thumbs/{$filename}";

        $disk->put($thumbnailPath, (string) $thumbnail);

        $this->image->update(['thumbnail_path' => $thumbnailPath]);
    }
}
