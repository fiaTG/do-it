<?php

namespace App\Http\Resources;

use App\Models\Invite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * ÖFFENTLICHE Einladungs-Vorschau (GET /invites/{token}), für die
 * Registrierungsseite. Bewusst minimal: nur Familienname, Rolle und die
 * MASKIERTE E-Mail – kein Klartext und KEIN Link/Token (die verriete, welche
 * Adresse einzugeben ist). Vollständige Daten sieht nur der Verwalter über
 * InviteResource (authentifizierte Liste).
 *
 * @mixin Invite
 */
class InvitePreviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'email_masked' => self::maskEmail($this->email),
            'role' => $this->role,
            'family' => new FamilyResource($this->whenLoaded('family')),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }

    /** max@example.de -> m***@example.de ; a@x.de -> *@x.de */
    public static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        if ($domain === '') {
            return '***';
        }
        $head = mb_strlen($local) > 1 ? mb_substr($local, 0, 1) : '';

        return $head.'***@'.$domain;
    }
}
