<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Event;
use App\Models\GameScore;
use App\Models\Image;
use App\Models\ShoppingItem;
use App\Models\Todo;
use App\Models\TodoPoint;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Selbstbedienungs-Datenexport (DSGVO Art. 15/20). Liefert die
 * personenbezogenen Daten der anfragenden Person plus die von ihr angelegten
 * geteilten Inhalte als JSON – rein lesend. Fotos werden als Metadaten
 * exportiert (Binärdateien lädt man in der Galerie herunter).
 */
class DataExportController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $family = $user->family;

        $data = [
            'exported_at' => now()->toIso8601String(),
            'hinweis' => 'Enthält deine persönlichen Daten und die von dir angelegten '
                .'Familieninhalte. Fotos sind als Metadaten aufgeführt – die Bilddateien '
                .'selbst kannst du in der Galerie herunterladen.',

            'konto' => [
                'vorname' => $user->first_name,
                'nachname' => $user->last_name,
                'email' => $user->email,
                'rolle' => $user->role,
                'geburtsdatum' => $user->birthdate?->toDateString(),
                'geschlecht' => $user->gender,
                'farbe' => $user->color,
                'socials' => [
                    'facebook' => $user->facebook,
                    'instagram' => $user->instagram,
                    'linkedin' => $user->linkedin,
                ],
                'registriert_am' => $user->created_at?->toIso8601String(),
            ],

            'familie' => $family ? [
                'name' => $family->name,
                'ort' => $family->location_name,
                'koordinaten' => $family->latitude !== null
                    ? ['lat' => (float) $family->latitude, 'lng' => (float) $family->longitude]
                    : null,
            ] : null,

            'meine_inhalte' => $family ? [
                'todos' => Todo::where('family_id', $family->id)->where('user_id', $user->id)
                    ->get(['title', 'is_done', 'created_at']),
                'termine' => Event::where('family_id', $family->id)->where('user_id', $user->id)
                    ->get(['title', 'starts_at', 'ends_at', 'category', 'car_reserved', 'recurrence', 'recurrence_until']),
                'einkaufsliste' => ShoppingItem::where('family_id', $family->id)->where('user_id', $user->id)
                    ->get(['name', 'quantity', 'is_purchased', 'created_at']),
                'kontakte' => Contact::where('family_id', $family->id)->where('user_id', $user->id)
                    ->get(['name', 'category', 'phone', 'email', 'website', 'address', 'notes', 'created_at']),
                'fotos_metadaten' => Image::where('family_id', $family->id)->where('user_id', $user->id)
                    ->get(['title', 'taken_at', 'width', 'height', 'created_at']),
            ] : null,

            'meine_aktivitaet' => $family ? [
                'spiel_punkte' => GameScore::where('family_id', $family->id)->where('user_id', $user->id)
                    ->get(['game', 'score', 'created_at']),
                'nest_blaetter' => TodoPoint::where('family_id', $family->id)->where('user_id', $user->id)
                    ->get(['points', 'created_at']),
            ] : null,

            // Kein Zahlungsmittel gespeichert (Kauf simuliert, ADR-0022).
            'abo' => $family?->subscription ? [
                'plan' => $family->subscription->plan,
                'status' => $family->subscription->status,
                'laeuft_bis' => $family->subscription->expires_at?->toIso8601String(),
            ] : null,
        ];

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="nidula-datenexport.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
