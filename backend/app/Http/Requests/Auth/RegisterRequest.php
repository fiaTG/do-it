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
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            // Optionaler Einladungs-Token: tritt der Familie der Einladung bei.
            'token' => ['nullable', 'string'],
            // Gesetzt von nativen Clients -> Antwort enthält dann einen API-Token.
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
