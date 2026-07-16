<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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
     *
     * Das Aufnahmedatum wird VOR dem Strip aus dem EXIF gelesen und separat
     * zurückgegeben (fürs chronologische Einsortieren in der Galerie nach
     * Aufnahme- statt Upload-Zeitpunkt) – es landet nicht in der Bilddatei.
     *
     * @return array{path: string, width: ?int, height: ?int, taken_at: ?Carbon}
     */
    public static function storeStripped(UploadedFile $file, string $directory): array
    {
        $disk = config('filesystems.media');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $takenAt = self::readCaptureDate($file->getRealPath());

        try {
            $image = (new ImageManager(new Driver))
                ->decode(file_get_contents($file->getRealPath()));
            $width = $image->width();
            $height = $image->height();

            [$encoder, $extension] = match ($extension) {
                'png' => [new PngEncoder, 'png'],
                'webp' => [new WebpEncoder(quality: 85), 'webp'],
                default => [new JpegEncoder(quality: 85), 'jpg'],
            };

            $path = $directory.'/'.Str::random(40).'.'.$extension;
            Storage::disk($disk)->put($path, (string) $image->encode($encoder));

            return ['path' => $path, 'width' => $width, 'height' => $height, 'taken_at' => $takenAt];
        } catch (\Throwable $e) {
            // Fail closed (Review H-02, ADR-0015): Wenn der Metadaten-Strip
            // fehlschlägt, wird NIEMALS das Original (potenziell mit GPS/EXIF)
            // gespeichert – der Upload wird abgelehnt.
            Log::warning('Bildverarbeitung fehlgeschlagen, Upload abgelehnt.');

            abort(422, 'Das Bild konnte nicht verarbeitet werden – bitte ein anderes Format (JPEG/PNG/WebP) versuchen.');
        }
    }

    /**
     * Liest "DateTimeOriginal" (Aufnahmezeitpunkt) aus dem EXIF, falls vorhanden.
     * Nur JPEG/TIFF tragen EXIF – bei PNG/WebP oder Bildern ohne Kamera-Metadaten
     * (Screenshots, Downloads) gibt es schlicht keins; die Galerie fällt dann auf
     * das Upload-Datum zurück.
     */
    private static function readCaptureDate(string $path): ?Carbon
    {
        if (! function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($path);
        $raw = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null;
        if (! is_string($raw)) {
            return null;
        }

        // EXIF-Format: "2026:06:17 14:32:10".
        $date = \DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $raw);
        if ($date === false || $date->format('Y') < 1995) {
            return null; // Kaputte/Platzhalter-Daten mancher Kameras ("0000:00:00 …").
        }

        return Carbon::instance($date);
    }
}
