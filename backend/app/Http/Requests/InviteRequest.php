<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteRequest extends FormRequest
{
    /**
     * Nur Nutzer, die bereits einer Familie angehören, dürfen einladen.
     */
    public function authorize(): bool
    {
        return $this->user()?->family_id !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
