<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @mixin User
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
            'role' => $this->role,
            'family' => new FamilyResource($this->whenLoaded('family')),
            'avatar_url' => $this->avatar_path
                ? URL::temporarySignedRoute('media.avatar', now()->addHour(), ['user' => $this->id])
                : null,
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
