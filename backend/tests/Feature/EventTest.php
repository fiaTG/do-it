<?php

use Laravel\Sanctum\Sanctum;

it('creates a calendar event with car reservation', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/events', [
        'title' => 'Arzttermin',
        'starts_at' => now()->addDay()->toIso8601String(),
        'ends_at' => now()->addDay()->addHour()->toIso8601String(),
        'category' => 'Gesundheit',
        'car_reserved' => true,
    ])->assertCreated()
        ->assertJsonPath('data.title', 'Arzttermin')
        ->assertJsonPath('data.car_reserved', true);
});

it('rejects an event ending before it starts', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/events', [
        'title' => 'Kaputt',
        'starts_at' => now()->addDay()->toIso8601String(),
        'ends_at' => now()->subDay()->toIso8601String(),
    ])->assertStatus(422)->assertJsonValidationErrorFor('ends_at');
});
