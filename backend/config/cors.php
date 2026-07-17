<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Erlaubt dem React-SPA (eigener Origin) Zugriff auf die API. Für die
    | Sanctum-SPA-Cookie-Authentifizierung muss `supports_credentials` true
    | sein und der Origin explizit erlaubt werden (kein '*' mit Credentials).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter([
        env('FRONTEND_URL', 'http://localhost:5173'),
        // Native Hüllen (Capacitor WebView-Origins, ADR-0012). Token-Auth,
        // keine Cookies – aber die Origin muss für XHR/Upload erlaubt sein.
        // iOS nutzt capacitor://, Android-Release https://.
        'https://localhost',
        'capacitor://localhost',
        // Klartext-HTTP nur außerhalb Produktion (Android-Dev-Emulator,
        // ADR-0025: keine unverschlüsselten Origins in Prod).
        env('APP_ENV') === 'production' ? null : 'http://localhost',
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
