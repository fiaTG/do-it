<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Health-Endpunkte (ADR-0027). Zwei Bedeutungen bewusst getrennt:
 * - live():  Der Webprozess antwortet überhaupt (Liveness). Trivial, ungedrosselt.
 * - ready(): Die App kann arbeiten (Readiness) – DB + Cache/Redis erreichbar.
 *            Der externe Monitor zeigt hierauf, damit auch ein DB-/Redis-Ausfall
 *            sichtbar wird. Gedrosselt (throttle:30,1), weil jeder Aufruf echte
 *            Abhängigkeiten anfasst. Nach außen keine Details (kein Info-Leak).
 */
class HealthController extends Controller
{
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
            'time' => now()->toIso8601String(),
        ]);
    }

    public function ready(): JsonResponse
    {
        $failed = null;

        try {
            $this->checkDatabase();
        } catch (\Throwable $e) {
            $failed = 'db';
            Log::warning('Readiness-Check: Datenbank nicht erreichbar', ['exception' => $e->getMessage()]);
        }

        if ($failed === null) {
            try {
                $this->checkCache();
            } catch (\Throwable $e) {
                $failed = 'cache';
                Log::warning('Readiness-Check: Cache/Redis nicht erreichbar', ['exception' => $e->getMessage()]);
            }
        }

        if ($failed !== null) {
            return response()->json(['status' => 'degraded'], 503);
        }

        return response()->json(['status' => 'ready']);
    }

    private function checkDatabase(): void
    {
        DB::select('select 1');
    }

    /**
     * Echter Lese-/Schreib-Round-Trip gegen den konfigurierten Cache-Store
     * (Prod: Redis, Test: array). Eindeutiger Key je Request verhindert ein
     * Race bei parallelen Monitor-Checks; String-Token überlebt die
     * Redis-Serialisierung (ein UUID-Objekt käme deserialisiert ungleich zurück).
     */
    private function checkCache(): void
    {
        $token = (string) Str::uuid();
        $key = 'health:readiness:'.$token;

        $written = Cache::store()->put($key, $token, 60);
        if ($written === false || Cache::store()->get($key) !== $token) {
            throw new RuntimeException('Cache readiness check failed.');
        }

        Cache::store()->forget($key);
    }
}
