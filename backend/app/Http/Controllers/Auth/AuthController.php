<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordUpdateRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Registrierung. Optionaler Einladungs-Token lässt den Nutzer einer
     * bestehenden Familie beitreten.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $invite = $this->validInviteFor($data['token'] ?? null);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'], // wird durch 'hashed'-Cast gehasht
            'family_id' => $invite?->family_id,
        ]);

        $invite?->forceFill(['accepted_at' => now()])->save();

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return (new UserResource($user->load('family.subscription')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Login – cookie-/sessionbasiert fürs SPA, oder Token bei `device_name`
     * (native Clients). Rate-Limiting erfolgt über die Route (throttle:auth).
     */
    public function login(LoginRequest $request): JsonResponse|UserResource
    {
        $data = $request->validated();

        // Generische Fehlermeldung – keine Auskunft, ob die E-Mail existiert (S10).
        if (! Auth::validate(['email' => $data['email'], 'password' => $data['password']])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $user */
        $user = User::where('email', $data['email'])->firstOrFail();

        // Native Clients: API-Token ausstellen.
        if (! empty($data['device_name'])) {
            return response()->json([
                'token' => $user->createToken($data['device_name'])->plainTextToken,
            ]);
        }

        // Web-SPA: Session-Login mit Regeneration (verhindert Session-Fixation, S3).
        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return new UserResource($user->load('family.subscription'));
    }

    /**
     * Logout – löscht den API-Token (native) bzw. invalidiert die Session (Web).
     */
    public function logout(Request $request): Response
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }

        return response()->noContent();
    }

    /**
     * Aktuell authentifizierter Nutzer.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('family.subscription'));
    }

    /**
     * Passwort ändern – prüft das aktuelle Passwort und die zentrale Policy.
     */
    public function updatePassword(PasswordUpdateRequest $request): Response
    {
        $request->user()->update([
            'password' => $request->validated('password'), // 'hashed'-Cast
        ]);

        return response()->noContent();
    }

    /**
     * Liefert die Einladung zum Token, sofern gültig (nicht abgelaufen/eingelöst).
     */
    private function validInviteFor(?string $token): ?Invite
    {
        if (! $token) {
            return null;
        }

        $invite = Invite::where('token', $token)->first();

        if (! $invite || $invite->isAccepted() || $invite->isExpired()) {
            return null;
        }

        return $invite;
    }
}
