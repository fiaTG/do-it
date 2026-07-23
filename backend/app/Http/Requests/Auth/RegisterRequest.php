<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Im invite-only-Modus KEINE unique-Prüfung: sonst antwortet der
        // Endpoint für bereits registrierte Adressen mit 422 (Validierung) und
        // für unbekannte mit 403 (Invite-Gate) -> E-Mail-Enumeration. Ohne
        // gültiges Token gibt es dort so oder so nur 403; die Eindeutigkeit
        // sichern das E-Mail-gebundene Token + die DB-Constraint ab.
        $emailRules = ['required', 'string', 'email', 'max:255'];
        if (config('features.registration') !== 'invite') {
            $emailRules[] = 'unique:users,email';
        }

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'confirmed', Password::defaults()],
            // Optionaler Einladungs-Token: tritt der Familie der Einladung bei.
            'token' => ['nullable', 'string'],
            // Gesetzt von nativen Clients -> Antwort enthält dann einen API-Token.
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
