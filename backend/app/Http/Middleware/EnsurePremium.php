<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Schützt reine Premium-Endpunkte (ADR-0022). Feature-LIMITS (z. B. das
 * Galerie-Kontingent) bleiben bewusst Controller-Sache – diese Middleware ist
 * für Endpunkte gedacht, die es NUR mit Premium gibt (z. B. Kalender-Sync).
 * Route-Alias: `premium`.
 */
class EnsurePremium
{
    public function handle(Request $request, Closure $next): Response
    {
        $family = $request->user()?->family;

        abort_unless(
            $family !== null && $family->isPremium(),
            403,
            'Diese Funktion ist Teil von Nidula Premium.',
        );

        return $next($request);
    }
}
