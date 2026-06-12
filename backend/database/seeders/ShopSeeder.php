<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Aldi', 'Lidl', 'Rewe'] as $name) {
            Shop::updateOrCreate(['name' => $name]);
        }
    }
}
