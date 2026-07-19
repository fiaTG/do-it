<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Requests\InviteRequest;
use App\Http\Resources\InviteResource;
use App\Mail\InvitationMail;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    use InteractsWithFamily;

    /**
     * Offene (nicht eingelöste, nicht abgelaufene) Einladungen der Familie –
     * sichtbar für alle Mitglieder, zurückziehen dürfen nur Verwalter (ADR-0021).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return InviteResource::collection(
            Invite::where('family_id', $familyId)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->get()
        );
    }

    /**
     * Einladung erstellen und per Mail (Mailpit) verschicken. Nur Verwalter,
     * inklusive Rollen-Wahl für den Eingeladenen (InviteRequest, ADR-0021).
     */
    public function store(InviteRequest $request): JsonResponse
    {
        $user = $request->user();

        // Familien-Obergrenze (Timo 2026-07-18): Mitglieder + offene, nicht
        // abgelaufene Einladungen zählen zusammen – deckt Kernfamilie plus
        // Großeltern und schützt UI-Annahmen und Server.
        $max = (int) config('features.family_max_members');
        $occupied = User::where('family_id', $user->family_id)->count()
            + Invite::where('family_id', $user->family_id)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->count();
        abort_if(
            $occupied >= $max,
            422,
            "Eine Familie kann höchstens {$max} Mitglieder haben (offene Einladungen zählen mit).",
        );

        $invite = Invite::create([
            'family_id' => $user->family_id,
            'email' => $request->validated('email'),
            'role' => $request->validated('role') ?? 'guardian',
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);

        // Review M-05: Versand entkoppelt über die Queue (Worker läuft) –
        // ein lahmer/kaputter Mailserver blockiert den Request nicht mehr.
        Mail::to($invite->email)->queue(new InvitationMail($invite));

        return (new InviteResource($invite->load('family')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Einladung zurückziehen (nur Verwalter, nur eigene Familie, nicht mehr
     * möglich, wenn sie bereits angenommen wurde).
     */
    public function destroy(Request $request, Invite $invite): Response
    {
        $user = $request->user();

        abort_unless(
            $user->family_id !== null && (int) $user->family_id === (int) $invite->family_id,
            403,
        );
        abort_unless($user->isGuardian(), 403, 'Nur Verwalter können Einladungen zurückziehen.');
        abort_if($invite->isAccepted(), 409, 'Diese Einladung wurde bereits angenommen.');

        $invite->delete();

        return response()->noContent();
    }

    /**
     * Öffentliche Vorschau einer Einladung anhand des Tokens (für die
     * Registrierungsseite). 404, wenn ungültig/abgelaufen/eingelöst.
     */
    public function show(string $token): InviteResource
    {
        $invite = Invite::where('token', $token)->with('family')->first();

        abort_if(! $invite || $invite->isAccepted() || $invite->isExpired(), 404);

        return new InviteResource($invite);
    }
}
