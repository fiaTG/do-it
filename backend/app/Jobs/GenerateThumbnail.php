<?php

namespace App\Jobs;

use App\Models\Image;
use App\Support\ImageVariants;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Erzeugt das Galerie-Thumbnail und die responsiven Bildvarianten (ADR-0014/0015:
 * entkoppelte, async Bildverarbeitung).
 *
 * Liest das Original aus der media-Disk (lokal oder S3) und legt Thumbnail + Varianten
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

        $manager = new ImageManager(new Driver);

        // Erst validieren (eine Decodierung). scaleDown mutiert in-place, darum
        // dekodieren wir je Ausgabe frisch aus den (geprüften) Originalbytes.
        try {
            $manager->decode($original);
        } catch (\Throwable $e) {
            // Nicht verarbeitbares Bild -> Galerie nutzt das Original als Vorschau.
            Log::warning("Bildverarbeitung fehlgeschlagen (Bild {$this->image->id}): {$e->getMessage()}");

            return;
        }

        // Thumbnail (Vorschau im Grid).
        $directory = dirname($this->image->path);
        $filename = pathinfo($this->image->path, PATHINFO_FILENAME);
        $thumbnailPath = "{$directory}/thumbs/{$filename}.jpg";

        $thumbnail = $manager->decode($original)->scaleDown(width: 600)->encode(new JpegEncoder(quality: 75));
        $disk->put($thumbnailPath, (string) $thumbnail);

        // Responsive WebP-Varianten (scaleDown vergrößert nie über das Original).
        foreach (ImageVariants::WIDTHS as $width) {
            $variant = $manager->decode($original)->scaleDown(width: $width)->encode(new WebpEncoder(quality: 80));
            $disk->put(ImageVariants::path($this->image->path, $width), (string) $variant);
        }

        $this->image->update(['thumbnail_path' => $thumbnailPath]);
    }
}
