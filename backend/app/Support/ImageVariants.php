<?php

namespace App\Support;

/**
 * Einzige Quelle der Wahrheit für die responsiven Bildvarianten (ADR-0015).
 *
 * Die Varianten werden asynchron erzeugt (GenerateThumbnail) und über deterministische
 * Pfade adressiert – so braucht es keine zusätzlichen DB-Spalten: Job, Media-Proxy und
 * API-Resource leiten denselben Pfad aus Originalpfad + Breite ab.
 */
class ImageVariants
{
    /** Ausgabebreiten in px (WebP). scaleDown vergrößert nie über das Original hinaus. */
    public const WIDTHS = [480, 960, 1440];

    /** Pfad einer Variante neben dem Original: dir/variants/name-480.webp */
    public static function path(string $originalPath, int $width): string
    {
        $directory = dirname($originalPath);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);

        return "{$directory}/variants/{$filename}-{$width}.webp";
    }
}
