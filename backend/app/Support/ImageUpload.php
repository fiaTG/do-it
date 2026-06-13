<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ImageUpload
{
    /**
     * Speichert ein hochgeladenes Bild auf der media-Disk und entfernt dabei
     * sämtliche eingebetteten Metadaten (EXIF inkl. GPS) durch Neukodierung –
     * Privacy-by-Design (ADR-0015). GD trägt EXIF nicht mit, daher reicht das
     * Decode+Encode. Das Format (JPEG/PNG/WebP) bleibt erhalten.
     */
    public static function storeStripped(UploadedFile $file, string $directory): string
    {
        $disk = config('filesystems.media');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        try {
            $image = (new ImageManager(new Driver))
                ->decode(file_get_contents($file->getRealPath()));

            [$encoder, $extension] = match ($extension) {
                'png' => [new PngEncoder, 'png'],
                'webp' => [new WebpEncoder(quality: 85), 'webp'],
                default => [new JpegEncoder(quality: 85), 'jpg'],
            };

            $path = $directory.'/'.Str::random(40).'.'.$extension;
            Storage::disk($disk)->put($path, (string) $image->encode($encoder));

            return $path;
        } catch (\Throwable $e) {
            // Best effort: Strip fehlgeschlagen -> Original speichern, Warnung loggen.
            Log::warning('Bild-Metadaten konnten nicht entfernt werden: '.$e->getMessage());

            return $file->store($directory, $disk);
        }
    }
}
