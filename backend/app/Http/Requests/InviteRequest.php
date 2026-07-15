<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteRequest extends FormRequest
{
    /**
     * Nur Verwalter dürfen einladen (ADR-0021) – und legen dabei die Rolle
     * des Eingeladenen fest. Kinder können keine Mitglieder hinzufügen.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->family_id !== null && $user->isGuardian();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['nullable', Rule::in(['guardian', 'child'])],
        ];
    }
}
