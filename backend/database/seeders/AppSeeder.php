<?php

namespace Database\Seeders;

use App\Models\App;
use Illuminate\Database\Seeder;

class AppSeeder extends Seeder
{
    /**
     * Die auswählbaren Dashboard-Apps. Idempotent über den Slug.
     */
    public function run(): void
    {
        $apps = [
            ['slug' => 'gallery', 'name' => 'Galerie', 'icon' => 'fa-solid fa-image'],
            ['slug' => 'shopping-list', 'name' => 'Einkaufsliste', 'icon' => 'fa-solid fa-cart-shopping'],
            ['slug' => 'todo', 'name' => 'ToDo-Liste', 'icon' => 'fa-solid fa-list-check'],
            ['slug' => 'calendar', 'name' => 'Kalender', 'icon' => 'fa-solid fa-calendar'],
            ['slug' => 'contacts', 'name' => 'Adressbuch', 'icon' => 'fa-solid fa-address-book'],
            ['slug' => 'games', 'name' => 'Fun Area', 'icon' => 'fa-solid fa-gamepad'],
            ['slug' => 'fuel', 'name' => 'Spritpreise', 'icon' => 'fa-solid fa-gas-pump'],
        ];

        foreach ($apps as $app) {
            App::updateOrCreate(['slug' => $app['slug']], $app);
        }
    }
}
