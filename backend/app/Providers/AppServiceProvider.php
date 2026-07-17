<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Zentrale Passwort-Policy (ADR-0004): mind. 8 Zeichen, Buchstabe, Zahl,
        // Sonderzeichen – einheitlich für Registrierung und Passwortänderung.
        Password::defaults(fn () => Password::min(8)->letters()->numbers()->symbols());

        // Rate-Limiter gegen Brute-Force am Login (Befund S4), pro E-Mail + IP.
        RateLimiter::for('auth', function (Request $request) {
            $key = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(6)->by($key);
        });

        // Massen-Registrierung bremsen (ADR-0025): pro IP, großzügig genug
        // für eine Familie am selben Anschluss.
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Einladungen verschicken Mails -> Spam-Vektor (ADR-0025): je Nutzer.
        RateLimiter::for('invites', function (Request $request) {
            return [
                Limit::perMinute(5)->by('invites-m:'.$request->user()?->id),
                Limit::perHour(20)->by('invites-h:'.$request->user()?->id),
            ];
        });

        // Uploads sind teuer (Bildverarbeitung im Worker, ADR-0025): je Nutzer,
        // mit Luft für Galerie-Batch-Uploads (sequenzielle Einzel-Requests).
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(60)->by('uploads:'.$request->user()?->id);
        });
    }
}
