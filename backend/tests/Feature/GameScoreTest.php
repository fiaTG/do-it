<?php

use App\Models\Family;
use App\Models\GameScore;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('stores a score and reports personal and family records', function () {
    $user = familyMember();
    Sanctum::actingAs($user);

    // Erste Runde: automatisch persönlicher UND Familien-Rekord.
    $this->postJson('/api/v1/games/raupe/scores', ['score' => 12])
        ->assertCreated()
        ->assertJsonPath('data.personal_record', true)
        ->assertJsonPath('data.family_record', true);

    // Schlechtere Runde: kein Rekord, wird aber gespeichert.
    $this->postJson('/api/v1/games/raupe/scores', ['score' => 5])
        ->assertCreated()
        ->assertJsonPath('data.personal_record', false)
        ->assertJsonPath('data.family_record', false);

    expect(GameScore::count())->toBe(2);
});

it('returns the family top list with the best score per member', function () {
    $family = Family::factory()->create();
    $alice = User::factory()->create(['family_id' => $family->id]);
    $bob = User::factory()->create(['family_id' => $family->id]);

    foreach ([[$alice, 10], [$alice, 30], [$bob, 20]] as [$member, $score]) {
        GameScore::create([
            'family_id' => $family->id,
            'user_id' => $member->id,
            'game' => 'raupe',
            'score' => $score,
        ]);
    }
    // Fremde Familie taucht nicht auf.
    $stranger = familyMember();
    GameScore::create([
        'family_id' => $stranger->family_id,
        'user_id' => $stranger->id,
        'game' => 'raupe',
        'score' => 999,
    ]);

    Sanctum::actingAs($bob);
    $response = $this->getJson('/api/v1/games/raupe/scores')->assertOk();

    expect($response->json('data.top'))->toBe([
        ['user_id' => $alice->id, 'score' => 30],
        ['user_id' => $bob->id, 'score' => 20],
    ]);
    expect($response->json('data.my_best'))->toBe(20);
});

it('rejects unknown games and invalid scores', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/games/moorhuhn/scores', ['score' => 10])->assertNotFound();
    $this->getJson('/api/v1/games/moorhuhn/scores')->assertNotFound();
    $this->postJson('/api/v1/games/raupe/scores', ['score' => -1])
        ->assertStatus(422)->assertJsonValidationErrorFor('score');
    $this->postJson('/api/v1/games/raupe/scores', ['score' => 999999])
        ->assertStatus(422)->assertJsonValidationErrorFor('score');
});
