<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Support\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Profilfelder aktualisieren (Name, Geburtsdatum, Geschlecht, Social-Links).
     */
    public function update(Request $request): UserResource
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:m,w,other'],
            // Persönliche Kalenderfarbe (Hex, z. B. #3E7C9B).
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->update($data);

        return new UserResource($request->user()->fresh()->load('family.subscription'));
    }

    /**
     * Profilbild hochladen (ersetzt das bisherige). Datei landet im Storage,
     * in der DB steht nur der Pfad (ADR-0006).
     */
    public function avatar(Request $request): UserResource
    {
        $request->validate(['avatar' => ['required', 'image', 'max:5120']]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk(config('filesystems.media'))->delete($user->avatar_path);
        }

        $path = ImageUpload::storeStripped($request->file('avatar'), "avatars/{$user->id}")['path'];
        $user->update(['avatar_path' => $path]);

        return new UserResource($user->fresh()->load('family.subscription'));
    }
}
