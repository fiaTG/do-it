<?php

use App\Models\Image;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Papierkorb-Purge (ADR-0020): abgelaufene Galerie-Bilder endgültig entfernen.
// Braucht in Produktion einen laufenden Scheduler (cron: `schedule:run` minütlich
// oder `schedule:work` als Prozess) – lokal läuft keiner, Purge passiert dort nur
// bei manuellem `php artisan model:prune`.
Schedule::command('model:prune', ['--model' => [Image::class]])->daily();
