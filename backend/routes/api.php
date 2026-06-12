<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\InviteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
| Versionierte API gemäß ADR-0011. Alle Endpunkte liegen unter /api/v1.
*/
Route::prefix('v1')->group(function () {
    // Health-Check: beweist, dass die API erreichbar ist.
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
            'time' => now()->toIso8601String(),
        ]);
    });

    // --- Authentifizierung (öffentlich) --------------------------------------
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth'); // Brute-Force-Schutz (S4)

    // Öffentliche Einladungs-Vorschau (für die Registrierungsseite).
    Route::get('/invites/{token}', [InviteController::class, 'show']);

    // --- Geschützt (Sanctum: Cookie fürs SPA oder Bearer-Token) ---------------
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/auth/password', [AuthController::class, 'updatePassword']);

        // Familienmitglied einladen.
        Route::post('/invites', [InviteController::class, 'store']);
    });
});
