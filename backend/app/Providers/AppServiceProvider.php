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
    }
}
