<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'family_id' => $this->family_id,
            'family' => new FamilyResource($this->whenLoaded('family')),
            'avatar_path' => $this->avatar_path,
            'birthdate' => $this->birthdate?->toDateString(),
            'gender' => $this->gender,
            'socials' => [
                'facebook' => $this->facebook,
                'instagram' => $this->instagram,
                'linkedin' => $this->linkedin,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
