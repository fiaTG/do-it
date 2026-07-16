<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Spritpreise übers Tankerkönig-API (CC BY 4.0, Daten: MTS-K) – Premium-Feature
 * hinter der `premium`-Middleware (ADR-0022). Nutzungsregeln (2026-07-17):
 * Abfragen NUR auf Nutzeraktion, Server-Cache statt Polling, Radius ≤ 25 km,
 * Quellenangabe in der UI. Der API-Key steht ausschließlich in der .env.
 */
class FuelController extends Controller
{
    private const CACHE_MINUTES = 10;

    public function index(Request $request): JsonResponse
    {
        // premium-Middleware garantiert Familie + aktives Abo.
        $family = $request->user()->family;
        abort_if(
            $family->latitude === null || $family->longitude === null,
            409,
            'Bitte zuerst den Familienort festlegen (Familienseite) – er bestimmt die Tankstellen-Umgebung.',
        );

        $data = $request->validate([
            'rad' => ['nullable', 'numeric', 'min:1', 'max:25'],
        ]);
        $rad = (float) ($data['rad'] ?? 5);

        // Ein Cache-Eintrag je Region/Radius/Sorte: egal wie viele Familien in
        // derselben Gegend klicken, Tankerkönig sieht höchstens eine Anfrage
        // pro Fenster (Timos 10.000-User-Frage).
        $lat = round((float) $family->latitude, 3);
        $lng = round((float) $family->longitude, 3);
        $cacheKey = "fuel:{$lat}:{$lng}:{$rad}";

        $payload = Cache::get($cacheKey);
        if ($payload === null) {
            // Thundering-Herd-Schutz: nur EINE Anfrage holt, alle anderen
            // warten kurz und lesen dann aus dem Cache.
            $lock = Cache::lock("lock:{$cacheKey}", 10);
            try {
                $lock->block(8);
                $payload = Cache::get($cacheKey);
                if ($payload === null) {
                    $payload = $this->fetch($lat, $lng, $rad);
                    Cache::put($cacheKey, $payload, now()->addMinutes(self::CACHE_MINUTES));
                }
            } finally {
                $lock->release();
            }
        }

        return response()->json(['data' => $payload]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetch(float $lat, float $lng, float $rad): array
    {
        // Immer type=all: liefert e5/e10/diesel je Station (bei Einzelsorten
        // hieße das Feld stattdessen "price" – Timos No-Preise-Bug 2026-07-16)
        // und halbiert nebenbei die Cache-Vielfalt. Sortiert wird im Frontend.
        $response = Http::timeout(8)->get(config('services.tankerkoenig.base').'/list.php', [
            'lat' => $lat,
            'lng' => $lng,
            'rad' => $rad,
            'type' => 'all',
            'apikey' => config('services.tankerkoenig.key'),
        ]);

        // Das ok-Flag ist laut Doku immer zu prüfen.
        abort_unless(
            $response->successful() && $response->json('ok') === true,
            502,
            'Spritpreise sind gerade nicht verfügbar – bitte später erneut versuchen.',
        );

        return [
            'stations' => $response->json('stations') ?? [],
            'fetched_at' => now()->toIso8601String(),
        ];
    }
}
