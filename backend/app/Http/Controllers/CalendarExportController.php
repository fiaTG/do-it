<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Models\Family;
use App\Support\IcsBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Kalender-Freigabe (ADR-0024, Premium): Die Familie bekommt eine geheime
 * .ics-Abo-URL, die Google/Apple/Outlook abonnieren können – Nidula-Termine
 * erscheinen im Handy-Kalender. Das Token ist die einzige Zugangskontrolle
 * des öffentlichen Feeds (bewusst im Klartext gespeichert, damit die URL
 * jederzeit wieder anzeigbar ist; Verwalter können sie rotieren).
 */
class CalendarExportController extends Controller
{
    use InteractsWithFamily;

    /** Kurzer Server-Cache: blunted aggressive Poller, Termine sind kein Livestream. */
    private const CACHE_MINUTES = 10;

    /** Status + URL der Freigabe – sichtbar für ALLE Mitglieder (jedes Handy soll abonnieren). */
    public function show(Request $request): JsonResponse
    {
        $this->familyId($request);

        return response()->json(['data' => $this->payload($request->user()->family)]);
    }

    /** Freigabe aktivieren bzw. Adresse neu erzeugen – alte URL wird sofort ungültig. */
    public function rotate(Request $request): JsonResponse
    {
        $this->familyId($request);
        abort_unless($request->user()->isGuardian(), 403, 'Nur Verwalter können die Kalender-Freigabe verwalten.');

        $family = $request->user()->family;
        $family->update(['calendar_token' => bin2hex(random_bytes(32))]);
        Cache::forget("calendar-export:{$family->id}");

        return response()->json(['data' => $this->payload($family)]);
    }

    /** Freigabe beenden – der Feed antwortet ab sofort mit 404. */
    public function disable(Request $request): Response
    {
        $this->familyId($request);
        abort_unless($request->user()->isGuardian(), 403, 'Nur Verwalter können die Kalender-Freigabe verwalten.');

        $family = $request->user()->family;
        $family->update(['calendar_token' => null]);
        Cache::forget("calendar-export:{$family->id}");

        return response()->noContent();
    }

    /**
     * Öffentlicher Feed für Kalender-Apps (kein Login möglich – das Token ist
     * die Zugangskontrolle). Ohne aktives Premium pausiert der Feed (404, die
     * App-UI erklärt das ehrlich).
     */
    public function ics(string $token, IcsBuilder $builder): Response
    {
        $family = Family::where('calendar_token', $token)->first();
        abort_if($family === null || ! $family->isPremium(), 404);

        $ics = Cache::remember(
            "calendar-export:{$family->id}",
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $builder->build($family),
        );

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="nidula.ics"',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Family $family): array
    {
        $enabled = $family->calendar_token !== null;

        return [
            'enabled' => $enabled,
            'url' => $enabled ? route('calendar.export', ['token' => $family->calendar_token]) : null,
        ];
    }
}
