<?php

use App\Models\Family;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates a family and joins the creator', function () {
    $user = User::factory()->create(['family_id' => null]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/family', ['name' => 'Schlumpf'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Schlumpf');

    expect($user->fresh()->family_id)->not->toBeNull();
});

it('forbids creating a second family', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/family', ['name' => 'Zweite'])->assertStatus(409);
});

it('lists family members', function () {
    $family = Family::factory()->create();
    User::factory()->count(2)->create(['family_id' => $family->id]);
    Sanctum::actingAs(familyMember($family));

    $this->getJson('/api/v1/family/members')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});
