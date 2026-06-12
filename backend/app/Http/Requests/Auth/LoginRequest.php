<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            // Wenn gesetzt, wird statt Cookie-Login ein API-Token ausgestellt
            // (für native Clients, ADR-0004).
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
