<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\CalendarFeedResource;
use App\Models\CalendarFeed;
use App\Support\IcsExpander;
use App\Support\IcsFetcher;
use App\Support\IcsFetchException;
use App\Support\IcsParseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Kalender-Abos (ADR-0023, Premium): externe iCal-Kalender (Schule, Verein,
 * Abfallkalender …) als schreibgeschützte Ebene im Familienkalender. Verwalter
 * pflegen die Abos, alle Mitglieder sehen die Termine. Fremde URLs laufen
 * ausschließlich durch den SSRF-geprüften IcsFetcher; abgerufen wird NUR
 * on-demand beim Kalender-Aufruf (kein Cron, kein Polling).
 */
class CalendarFeedController extends Controller
{
    use InteractsWithFamily;

    /** URL-Abos werden beim Lesen höchstens alle 6 h neu geholt. */
    private const STALE_MINUTES = 360;

    private const MAX_FEEDS_PER_FAMILY = 5;

    /** Frontend fragt -1 Jahr bis +2 Jahre ab; etwas Luft obendrauf. */
    private const MAX_WINDOW_DAYS = 1300;

    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return CalendarFeedResource::collection(
            CalendarFeed::where('family_id', $familyId)->orderBy('name')->get()
        );
    }

    public function store(Request $request, IcsFetcher $fetcher, IcsExpander $expander): JsonResponse
    {
        $familyId = $this->familyId($request);
        abort_unless($request->user()->isGuardian(), 403, 'Nur Verwalter können Kalender-Abos verwalten.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'url' => ['required_without:file', 'prohibits:file', 'nullable', 'string', 'max:2048'],
            'file' => ['required_without:url', 'nullable', 'file', 'max:2048'], // 2 MB
        ]);

        abort_if(
            CalendarFeed::where('family_id', $familyId)->count() >= self::MAX_FEEDS_PER_FAMILY,
            422,
            'Maximal '.self::MAX_FEEDS_PER_FAMILY.' Kalender-Abos je Familie.',
        );

        // Quelle sofort laden und parsen – ein kaputtes Abo soll gar nicht erst
        // entstehen, und der Fehler landet als 422 direkt am richtigen Feld.
        $fromUrl = ($data['url'] ?? null) !== null;
        try {
            $ics = $fromUrl
                ? $fetcher->fetch($data['url'])
                : (string) $request->file('file')->get();
            $expander->assertParses($ics);
        } catch (IcsFetchException|IcsParseException $e) {
            throw ValidationException::withMessages([
                $fromUrl ? 'url' : 'file' => $e->getMessage(),
            ]);
        }

        $feed = CalendarFeed::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'color' => $data['color'],
            'url' => $fromUrl ? $data['url'] : null,
            'ics_data' => $ics,
            'last_synced_at' => now(),
        ]);

        return (new CalendarFeedResource($feed))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, CalendarFeed $feed): Response
    {
        $this->authorize('delete', $feed);

        $feed->delete();

        return response()->noContent();
    }

    /** Manuelles "Jetzt aktualisieren" aus der Abo-Verwaltung. */
    public function refresh(Request $request, CalendarFeed $feed, IcsFetcher $fetcher, IcsExpander $expander): CalendarFeedResource
    {
        $this->authorize('update', $feed);

        abort_unless($feed->isSubscription(), 422, 'Datei-Importe haben keine Quelle zum Aktualisieren.');

        $this->sync($feed, $fetcher, $expander);
        abort_if($feed->last_error !== null, 502, $feed->last_error);

        return new CalendarFeedResource($feed);
    }

    /**
     * Alle Feed-Termine der Familie im Zeitfenster – abgelaufene URL-Abos
     * werden dabei on-demand aktualisiert (Nutzeraktion = Kalender öffnen).
     */
    public function events(Request $request, IcsFetcher $fetcher, IcsExpander $expander): JsonResponse
    {
        $familyId = $this->familyId($request);

        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
        ]);
        $from = Carbon::parse($data['from']);
        $to = Carbon::parse($data['to']);
        abort_if($from->diffInDays($to) > self::MAX_WINDOW_DAYS, 422, 'Das Zeitfenster ist zu groß.');

        $events = [];
        foreach (CalendarFeed::where('family_id', $familyId)->get() as $feed) {
            if ($feed->isSubscription() && $this->isStale($feed)) {
                $this->sync($feed, $fetcher, $expander);
            }
            if ($feed->ics_data === null) {
                continue;
            }

            try {
                $occurrences = $expander->expand($feed->ics_data, $from, $to);
            } catch (IcsParseException $e) {
                $feed->update(['last_error' => $e->getMessage()]);

                continue;
            }

            foreach ($occurrences as $occurrence) {
                $events[] = [
                    // Eindeutig über Feed + UID + Start (UIDs sind nur je Feed eindeutig).
                    'id' => "feed{$feed->id}:{$occurrence['uid']}:{$occurrence['starts_at']}",
                    'feed_id' => $feed->id,
                    ...$occurrence,
                ];
            }
        }

        return response()->json(['data' => ['events' => $events]]);
    }

    private function isStale(CalendarFeed $feed): bool
    {
        return $feed->ics_data === null
            || $feed->last_synced_at === null
            || $feed->last_synced_at->lt(now()->subMinutes(self::STALE_MINUTES));
    }

    /**
     * Holt ein URL-Abo neu. Bei Fehlern bleiben die alten Daten stehen und der
     * Fehler wird fürs Verwalter-UI gemerkt; last_synced_at wird AUCH dann
     * gesetzt, damit ein toter Server nicht bei jedem Kalender-Aufruf erneut
     * mit voller Timeout-Wartezeit angefragt wird.
     */
    private function sync(CalendarFeed $feed, IcsFetcher $fetcher, IcsExpander $expander): void
    {
        try {
            $ics = $fetcher->fetch((string) $feed->url);
            $expander->assertParses($ics);
            $feed->update(['ics_data' => $ics, 'last_synced_at' => now(), 'last_error' => null]);
        } catch (IcsFetchException|IcsParseException $e) {
            $feed->update(['last_synced_at' => now(), 'last_error' => $e->getMessage()]);
        }
    }
}
