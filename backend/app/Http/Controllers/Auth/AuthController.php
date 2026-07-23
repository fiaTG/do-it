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
use Illuminate\Support\Facades\DB;
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

        // Geschlossene Beta (ADR-0025 Stufe 1): Ohne gültige Einladung gibt es
        // keine Registrierung – neue Familien kann dann niemand von außen
        // anlegen. Schalter: NIDULA_REGISTRATION=invite (Produktion) | open (Dev).
        abort_if(
            config('features.registration') === 'invite' && empty($data['token']),
            403,
            'Die Registrierung ist zurzeit nur mit persönlicher Einladung möglich.',
        );

        // Review H-01: Invite-Prüfung, User-Anlage und Einlösung atomar –
        // zwei parallele Registrierungen können denselben Token nicht doppelt
        // konsumieren (lockForUpdate in validInviteFor).
        $user = DB::transaction(function () use ($data): User {
            $invite = $this->validInviteFor($data['token'] ?? null, $data['email']);

            // Im invite-only-Modus prüft die FormRequest die Eindeutigkeit
            // bewusst NICHT (Enumeration). Hier – hinter dem gültigen, E-Mail-
            // gebundenen Token – sauber statt DB-Constraint-500 abfangen.
            if (User::where('email', $data['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Für diese Adresse besteht bereits ein Konto.',
                ]);
            }

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'], // wird durch 'hashed'-Cast gehasht
                'family_id' => $invite?->family_id,
                // Rolle kommt aus der Einladung (ADR-0021); ohne Einladung Verwalter.
                'role' => $invite?->role ?? 'guardian',
            ]);

            $invite?->forceFill(['accepted_at' => now()])->save();

            return $user;
        });

        // Native Clients: API-Token ausstellen (symmetrisch zu login()).
        if (! empty($data['device_name'])) {
            return response()->json([
                'token' => $user->createToken($data['device_name'])->plainTextToken,
            ], 201);
        }

        // Web-SPA: Session-Login mit Regeneration.
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
        $user = $request->user();
        $user->update([
            'password' => $request->validated('password'), // 'hashed'-Cast
        ]);

        // Review M-06/H-03: Passwortwechsel widerruft alle ANDEREN API-Tokens
        // (kompromittierte Geräte fliegen raus); der aktuelle Token/die
        // aktuelle Web-Session bleibt bestehen.
        $current = $user->currentAccessToken();
        $query = $user->tokens();
        if ($current instanceof PersonalAccessToken) {
            $query->where('id', '!=', $current->id);
        }
        $query->delete();

        return response()->noContent();
    }

    /**
     * Liefert die Einladung zum Token. Review H-01: Ein MITGESCHICKTER, aber
     * ungültiger Token (unbekannt/abgelaufen/eingelöst) ist ein harter Fehler
     * (422) – sonst entstünde stillschweigend ein familienloser Account.
     * Muss innerhalb einer Transaktion laufen (lockForUpdate gegen doppelte
     * Einlösung durch parallele Registrierungen).
     */
    private function validInviteFor(?string $token, string $email): ?Invite
    {
        if (! $token) {
            return null;
        }

        $invite = Invite::where('token', $token)->lockForUpdate()->first();

        if (! $invite || $invite->isAccepted() || $invite->isExpired()) {
            throw ValidationException::withMessages([
                'token' => 'Diese Einladung ist ungültig oder wurde bereits verwendet.',
            ]);
        }

        // Review H-01 (Timos Entscheidung 2026-07-16): Der Link ist an die
        // eingeladene Adresse gebunden – ein abgefangener/weitergeleiteter
        // Link nützt Fremden nichts.
        if (mb_strtolower(trim($invite->email)) !== mb_strtolower(trim($email))) {
            throw ValidationException::withMessages([
                'email' => 'Diese Einladung gilt für eine andere E-Mail-Adresse.',
            ]);
        }

        return $invite;
    }
}
