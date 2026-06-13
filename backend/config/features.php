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

];
