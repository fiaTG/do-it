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
        'gallery_images' => 30,
    ],

    // Papierkorb (ADR-0020): so lange bleiben gelöschte Bilder wiederherstellbar,
    // danach entfernt `model:prune` Rows UND Dateien endgültig.
    'trash_retention_days' => 30,

    // Registrierungs-Modus (ADR-0025): 'open' = jeder darf sich registrieren
    // (Entwicklung), 'invite' = nur mit gültigem, E-Mail-gebundenem
    // Einladungs-Token (geschlossene Beta / Kill-Switch in Produktion).
    'registration' => env('NIDULA_REGISTRATION', 'open'),

];
