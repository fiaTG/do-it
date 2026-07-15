<?php

namespace App\Http\Resources;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @mixin Contact
 */
class ContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'address' => $this->address,
            'notes' => $this->notes,
            // Signierte URL wie bei Avataren (ADR-0015) – Speicher bleibt privat.
            'photo_url' => $this->photo_path
                ? URL::temporarySignedRoute('media.contact-photo', now()->addHour(), ['contact' => $this->id])
                : null,
            'created_by' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
