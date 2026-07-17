<?php

namespace App\Http\Resources;

use App\Models\Invite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invite
 */
class InviteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'family' => new FamilyResource($this->whenLoaded('family')),
            // Teilbarer Registrierungs-Link (gleiche URL wie in der Mail):
            // nötig, solange in der Beta kein Mail-Versand läuft. Ungefährlich,
            // weil die Einladung ohnehin an die E-Mail gebunden ist (H-01).
            'link' => rtrim((string) config('app.frontend_url'), '/').'/register?token='.$this->token,
            'created_at' => $this->created_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
