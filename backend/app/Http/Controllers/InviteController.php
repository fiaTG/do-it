<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteRequest;
use App\Http\Resources\InviteResource;
use App\Mail\InvitationMail;
use App\Models\Invite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    /**
     * Einladung erstellen und per Mail (Mailpit) verschicken.
     * Nur Familienmitglieder dürfen einladen (siehe InviteRequest::authorize).
     */
    public function store(InviteRequest $request): JsonResponse
    {
        $user = $request->user();

        $invite = Invite::create([
            'family_id' => $user->family_id,
            'email' => $request->validated('email'),
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invite->email)->send(new InvitationMail($invite));

        return (new InviteResource($invite->load('family')))
            ->response()
            ->setStatusCode(201);
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
