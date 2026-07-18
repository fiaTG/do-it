<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // Gängige Ketten in DE (Beta-Feedback 2026-07-18: "mehr Supermärkte").
        // Eigene Läden je Familie stehen im Backlog (docs/roadmap.md).
        $shops = [
            'Aldi', 'Lidl', 'Rewe', 'Edeka', 'Kaufland', 'Netto', 'Penny',
            'Norma', 'dm', 'Rossmann', 'Müller', 'Getränkemarkt', 'Apotheke',
            'Bäcker', 'Metzger', 'Wochenmarkt', 'Sonstiges',
        ];

        foreach ($shops as $name) {
            Shop::updateOrCreate(['name' => $name]);
        }
    }
}
