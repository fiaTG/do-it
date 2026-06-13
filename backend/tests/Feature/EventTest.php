<?php

use App\Models\Event;
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

it('updates an event (title and time)', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $event = Event::create([
        'family_id' => $user->family_id,
        'user_id' => $user->id,
        'title' => 'Alt',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
        'category' => 'Familie',
    ]);

    $this->patchJson("/api/v1/events/{$event->id}", [
        'title' => 'Neu',
        'category' => 'Arbeit',
    ])->assertOk()
        ->assertJsonPath('data.title', 'Neu')
        ->assertJsonPath('data.category', 'Arbeit');
});

it('forbids updating another family event', function () {
    Sanctum::actingAs(familyMember());
    $other = familyMember();
    $event = Event::create([
        'family_id' => $other->family_id,
        'user_id' => $other->id,
        'title' => 'Fremd',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);

    $this->patchJson("/api/v1/events/{$event->id}", ['title' => 'Hack'])
        ->assertForbidden();
});
