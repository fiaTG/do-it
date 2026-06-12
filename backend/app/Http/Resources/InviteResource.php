<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Invite
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
            'family' => new FamilyResource($this->whenLoaded('family')),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
