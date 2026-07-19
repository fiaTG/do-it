<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature-Grenzen je Plan (ADR-0013)
    |--------------------------------------------------------------------------
    |
    | Der Free-Plan ist in den Alltagsfunktionen voll nutzbar, hat aber Grenzen
    | bei Komfort-/Speicher-Features. Premium hebt diese auf. Werte hier zentral
    | pflegbar; durchgesetzt z. B. im ImageController (Galerie-Speicher).
    |
    */

    'free_limits' => [
        // Timos Beta-Entscheidung 2026-07-18: 100 statt 30 – echtes
        // Familien-Erleben möglich, Premium bleibt für Foto-Familien spürbar.
        'gallery_images' => 100,
    ],

    // Fair-Use-Obergrenze für PREMIUM (Timo 2026-07-18): schützt die
    // Server-Platte (40 GB), bis der Object Storage angebunden ist – danach
    // anheben. Ehrlich kommuniziert (PremiumPage/Hilfe), kein "unbegrenzt".
    'premium_limits' => [
        'gallery_images' => 2500,
    ],

    // Max. Mitglieder je Familie inkl. offener Einladungen (Timo 2026-07-18):
    // deckt Kernfamilie + Großeltern; schützt UI-Annahmen und den Server.
    'family_max_members' => 8,

    // Papierkorb (ADR-0020): so lange bleiben gelöschte Bilder wiederherstellbar,
    // danach entfernt `model:prune` Rows UND Dateien endgültig.
    'trash_retention_days' => 30,

    // Registrierungs-Modus (ADR-0025): 'open' = jeder darf sich registrieren
    // (Entwicklung), 'invite' = nur mit gültigem, E-Mail-gebundenem
    // Einladungs-Token (geschlossene Beta / Kill-Switch in Produktion).
    'registration' => env('NIDULA_REGISTRATION', 'open'),

];
