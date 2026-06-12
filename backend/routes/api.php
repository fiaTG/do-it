<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
| Versionierte API gemäß ADR-0011. Alle Endpunkte liegen unter /api/v1.
*/
Route::prefix('v1')->group(function () {
    // Health-Check: beweist, dass die API erreichbar ist (Phase 0).
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
            'time' => now()->toIso8601String(),
        ]);
    });

    // Aktuell eingeloggter Nutzer (Sanctum) – wird ab Phase 2 genutzt.
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
