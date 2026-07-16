<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    // Tankerkönig-Spritpreis-API (CC BY 4.0, Daten: MTS-K). Persönlichen Key
    // unter https://creativecommons.tankerkoenig.de registrieren – Key gehört
    // NUR in die .env, niemals ins Repo (Nutzungsbedingungen).
    'tankerkoenig' => [
        'key' => env('TANKERKOENIG_API_KEY'),
        'base' => env('TANKERKOENIG_BASE', 'https://creativecommons.tankerkoenig.de/json'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
