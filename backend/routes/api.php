<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShoppingItemController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserAppController;
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

    // Medien-Proxy (ADR-0015): nur über gültige Signatur erreichbar, kein Cookie
    // nötig (funktioniert für <img>). Speicher bleibt privat.
    Route::middleware('signed')->group(function () {
        // withTrashed: auch Papierkorb-Bilder (ADR-0020) brauchen Vorschaubilder;
        // die signierten URLs dafür entstehen nur in der Trash-Liste für
        // berechtigte Familienmitglieder.
        Route::get('/media/images/{image}', [MediaController::class, 'image'])
            ->name('media.image')->withTrashed();
        Route::get('/media/images/{image}/thumbnail', [MediaController::class, 'thumbnail'])
            ->name('media.thumbnail')->withTrashed();
        Route::get('/media/images/{image}/variant/{width}', [MediaController::class, 'variant'])
            ->whereNumber('width')->name('media.variant')->withTrashed();
        Route::get('/media/avatars/{user}', [MediaController::class, 'avatar'])->name('media.avatar');
    });

    // --- Geschützt (Sanctum: Cookie fürs SPA oder Bearer-Token) ---------------
    Route::middleware('auth:sanctum')->group(function () {
        // Auth/Profil
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/auth/password', [AuthController::class, 'updatePassword']);

        // Profil
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'avatar']);

        // Abo / Premium (ADR-0013)
        Route::get('/subscription', [SubscriptionController::class, 'show']);
        Route::post('/subscription', [SubscriptionController::class, 'store']);
        Route::delete('/subscription', [SubscriptionController::class, 'destroy']);

        // Familie & Einladungen
        Route::post('/family', [FamilyController::class, 'store']);
        Route::get('/family/members', [FamilyController::class, 'members']);
        Route::patch('/family/members/{member}/role', [FamilyController::class, 'updateRole']);
        Route::post('/invites', [InviteController::class, 'store']);

        // Dashboard-Apps (Katalog + eigene Auswahl)
        Route::get('/apps', [UserAppController::class, 'index']);
        Route::get('/me/apps', [UserAppController::class, 'mine']);
        Route::post('/me/apps', [UserAppController::class, 'store']);
        Route::delete('/me/apps/{app}', [UserAppController::class, 'destroy']);

        // Stammdaten
        Route::get('/shops', [ShopController::class, 'index']);

        // Feature-Apps (familiengebunden, via Policies abgesichert)
        Route::get('shopping-items/pdf', [ShoppingItemController::class, 'pdf']);
        Route::apiResource('shopping-items', ShoppingItemController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::apiResource('todos', TodoController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::apiResource('events', EventController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::post('images/batch-delete', [ImageController::class, 'batchDestroy']);
        // Papierkorb (ADR-0020) – Literal-Routen VOR der Resource, sonst
        // schluckt images/{image} das "trash"-Segment.
        Route::get('images/trash', [ImageController::class, 'trash']);
        Route::post('images/restore', [ImageController::class, 'restore']);
        Route::post('images/purge', [ImageController::class, 'purge']);
        Route::apiResource('images', ImageController::class)
            ->only(['index', 'show', 'store', 'destroy']);
    });
});
