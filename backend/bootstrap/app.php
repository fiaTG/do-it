<?php

use App\Http\Middleware\EnsurePremium;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aktiviert Sanctum-SPA-Authentifizierung (Cookie/Session) für Requests
        // von den konfigurierten Stateful-Domains (SANCTUM_STATEFUL_DOMAINS).
        $middleware->statefulApi();

        // Reine Premium-Endpunkte (ADR-0022): Route::middleware('premium').
        $middleware->alias([
            'premium' => EnsurePremium::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
