<?php

namespace App\Http\Resources;

use App\Models\Image;
use App\Support\ImageVariants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @mixin Image
 */
class ImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Signierte, zeitlich befristete URLs (60 min) – Medien bleiben privat (ADR-0015).
        $expiry = now()->addHour();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => URL::temporarySignedRoute('media.image', $expiry, ['image' => $this->id]),
            'thumbnail_url' => URL::temporarySignedRoute('media.thumbnail', $expiry, ['image' => $this->id]),
            // Responsive Varianten für srcset (ADR-0015): je Breite eine signierte URL.
            'srcset' => collect(ImageVariants::WIDTHS)->map(fn (int $width) => [
                'width' => $width,
                'url' => URL::temporarySignedRoute('media.variant', $expiry, [
                    'image' => $this->id,
                    'width' => $width,
                ]),
            ])->all(),
            'created_by' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'taken_at' => $this->taken_at?->toIso8601String(),
            'width' => $this->width,
            'height' => $this->height,
            // Blur-up-Platzhalter (data-URI); null bei Altbestand oder solange
            // der Thumbnail-Job noch läuft.
            'placeholder' => $this->placeholder,
            // Solange der Job das Thumbnail nicht erzeugt hat, streamt der
            // Proxy das Original – das Frontend zeigt "Wird verarbeitet".
            'processing' => $this->thumbnail_path === null,
            // Papierkorb (ADR-0020): nur in der Trash-Liste gesetzt.
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'expires_at' => $this->deleted_at
                ?->addDays((int) config('features.trash_retention_days'))
                ->toIso8601String(),
        ];
    }
}
