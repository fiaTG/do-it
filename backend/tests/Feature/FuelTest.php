<?php

use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

// premiumFamilyMember() steht in tests/Pest.php (auch von CalendarFeedTest genutzt).

function fakeTankerkoenig(): void
{
    Http::fake([
        '*tankerkoenig*' => Http::response([
            'ok' => true,
            'license' => 'CC BY 4.0',
            'data' => 'MTS-K',
            'stations' => [
                [
                    'id' => 'aaa', 'name' => 'Test-Tanke', 'brand' => 'TEST',
                    'street' => 'Teststr.', 'houseNumber' => '1', 'place' => 'Heidelberg',
                    'postCode' => 69115, 'dist' => 1.2, 'isOpen' => true,
                    'e5' => 1.799, 'e10' => 1.739, 'diesel' => 1.649,
                ],
            ],
        ]),
    ]);
}

it('returns stations for a premium family and caches the upstream call', function () {
    fakeTankerkoenig();
    Sanctum::actingAs(premiumFamilyMember());

    $first = $this->getJson('/api/v1/fuel-stations?rad=5')->assertOk();
    expect($first->json('data.stations.0.name'))->toBe('Test-Tanke');
    expect($first->json('data.fetched_at'))->not->toBeNull();

    // Zweiter Abruf derselben Region kommt aus dem Cache -> nur EIN Upstream-Call.
    $this->getJson('/api/v1/fuel-stations?rad=5')->assertOk();
    Http::assertSentCount(1);
});

it('blocks free families via the premium middleware', function () {
    fakeTankerkoenig();
    Sanctum::actingAs(familyMember());

    $this->getJson('/api/v1/fuel-stations')->assertForbidden();
    Http::assertNothingSent();
});

it('asks for a family location first', function () {
    fakeTankerkoenig();
    Sanctum::actingAs(premiumFamilyMember(withLocation: false));

    $this->getJson('/api/v1/fuel-stations')->assertStatus(409);
    Http::assertNothingSent();
});

it('maps upstream failures to a 502 with a german message', function () {
    Http::fake(['*tankerkoenig*' => Http::response(['ok' => false, 'message' => 'apikey unknown'])]);
    Sanctum::actingAs(premiumFamilyMember());

    $this->getJson('/api/v1/fuel-stations')->assertStatus(502);
});

it('validates the radius', function () {
    fakeTankerkoenig();
    Sanctum::actingAs(premiumFamilyMember());

    $this->getJson('/api/v1/fuel-stations?rad=99')
        ->assertStatus(422)->assertJsonValidationErrorFor('rad');
});

it('always requests all fuel types upstream (price field trap)', function () {
    fakeTankerkoenig();
    Sanctum::actingAs(premiumFamilyMember());

    $this->getJson('/api/v1/fuel-stations')->assertOk();
    Http::assertSent(fn ($request) => $request['type'] === 'all');
});
