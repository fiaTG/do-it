<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AppSeeder::class,   // 4 Dashboard-Apps
            ShopSeeder::class,  // Aldi / Lidl / Rewe
            DemoSeeder::class,  // Demo-Familie + Testdaten (setzt App/Shop voraus)
        ]);
    }
}
