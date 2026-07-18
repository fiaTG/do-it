<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Produktions-SICHERE Referenzdaten (App-Katalog + Läden) – KEINE Demo-Daten.
 * Beide Seeder sind idempotent (updateOrCreate über den Slug/Namen), laufen
 * also bei jedem Deploy mit (deploy.sh) und ergänzen fehlende Einträge.
 *
 * Abgrenzung: DemoSeeder (Testfamilie) gehört NUR in die Entwicklung und
 * wird in Produktion nie ausgeführt (ADR-0025: keine Seed-/Demodaten in Prod).
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AppSeeder::class,
            ShopSeeder::class,
        ]);
    }
}
