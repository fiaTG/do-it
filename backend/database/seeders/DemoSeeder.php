<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\Event;
use App\Models\Family;
use App\Models\Shop;
use App\Models\ShoppingItem;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Demo-Familie mit Testdaten – angelehnt an die alten Beispieldaten und den
     * Testzugang aus der README (dozent@example.com / test123!).
     *
     * Setzt AppSeeder und ShopSeeder voraus.
     */
    public function run(): void
    {
        $family = Family::firstOrCreate(['name' => 'Musterfamilie']);

        $dozent = User::updateOrCreate(
            ['email' => 'dozent@example.com'],
            [
                'first_name' => 'Doris',
                'last_name' => 'Dozent',
                'password' => Hash::make('test123!'),
                'family_id' => $family->id,
                'email_verified_at' => now(),
            ],
        );

        $partner = User::updateOrCreate(
            ['email' => 'max@example.com'],
            [
                'first_name' => 'Max',
                'last_name' => 'Mustermann',
                'password' => Hash::make('test123!'),
                'family_id' => $family->id,
                'email_verified_at' => now(),
            ],
        );

        // Beide Mitglieder bekommen alle vier Apps aufs Dashboard.
        $allApps = App::pluck('id');
        $dozent->apps()->syncWithoutDetaching($allApps);
        $partner->apps()->syncWithoutDetaching($allApps);

        // Einkaufsliste (familiengebunden)
        $rewe = Shop::where('name', 'Rewe')->first();
        $aldi = Shop::where('name', 'Aldi')->first();

        foreach ([
            ['name' => 'Apfel', 'quantity' => 3, 'shop_id' => $rewe?->id, 'user_id' => $dozent->id],
            ['name' => 'Zucker', 'quantity' => 1, 'shop_id' => $aldi?->id, 'user_id' => $partner->id],
        ] as $item) {
            ShoppingItem::firstOrCreate(
                ['family_id' => $family->id, 'name' => $item['name']],
                $item + ['family_id' => $family->id],
            );
        }

        // ToDos
        foreach (['Beispiel-Aufgabe', 'Beispiel-Aufgabe 2'] as $title) {
            Todo::firstOrCreate(
                ['family_id' => $family->id, 'title' => $title],
                ['user_id' => $dozent->id],
            );
        }

        // Kalender-Event mit Auto-Reservierung
        Event::firstOrCreate(
            ['family_id' => $family->id, 'title' => 'Familienausflug'],
            [
                'user_id' => $dozent->id,
                'starts_at' => now()->addDays(3)->setTime(10, 0),
                'ends_at' => now()->addDays(3)->setTime(16, 0),
                'category' => 'Freizeit',
                'car_reserved' => true,
            ],
        );
    }
}
