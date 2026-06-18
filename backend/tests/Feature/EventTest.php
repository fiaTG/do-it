<?php

use App\Models\Event;
use App\Models\User;
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

it('defaults the owner to the creator', function () {
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/events', [
        'title' => 'Mein Termin',
        'starts_at' => now()->addDay()->toIso8601String(),
        'ends_at' => now()->addDay()->addHour()->toIso8601String(),
    ])->assertCreated()->assertJsonPath('data.owner_id', $user->id);
});

it('lets a member assign an event to another family member', function () {
    $parent = familyMember();
    $child = User::factory()->create(['family_id' => $parent->family_id]);
    Sanctum::actingAs($parent);

    $this->postJson('/api/v1/events', [
        'title' => 'Zahnarzt Kind',
        'starts_at' => now()->addDay()->toIso8601String(),
        'ends_at' => now()->addDay()->addHour()->toIso8601String(),
        'owner_id' => $child->id,
    ])->assertCreated()
        ->assertJsonPath('data.owner_id', $child->id)
        ->assertJsonPath('data.owner_name', $child->first_name);
});

it('forces a child to be the owner of events they create', function () {
    $parent = familyMember();
    $child = User::factory()->create(['family_id' => $parent->family_id, 'role' => 'child']);
    Sanctum::actingAs($child);

    // Kind versucht, den Termin dem Elternteil zuzuweisen -> wird auf sich selbst gezwungen.
    $this->postJson('/api/v1/events', [
        'title' => 'Schummel',
        'starts_at' => now()->addDay()->toIso8601String(),
        'ends_at' => now()->addDay()->addHour()->toIso8601String(),
        'owner_id' => $parent->id,
    ])->assertCreated()->assertJsonPath('data.owner_id', $child->id);
});

it('forbids a child from editing an event they do not own', function () {
    $parent = familyMember();
    $child = User::factory()->create(['family_id' => $parent->family_id, 'role' => 'child']);
    $event = Event::create([
        'family_id' => $parent->family_id,
        'user_id' => $parent->id,
        'owner_id' => $parent->id,
        'title' => 'Eltern-Termin',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);
    Sanctum::actingAs($child);

    $this->patchJson("/api/v1/events/{$event->id}", ['title' => 'Hack'])->assertForbidden();
});

it('lets a child edit their own event', function () {
    $parent = familyMember();
    $child = User::factory()->create(['family_id' => $parent->family_id, 'role' => 'child']);
    $event = Event::create([
        'family_id' => $parent->family_id,
        'user_id' => $child->id,
        'owner_id' => $child->id,
        'title' => 'Kind-Termin',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);
    Sanctum::actingAs($child);

    $this->patchJson("/api/v1/events/{$event->id}", ['title' => 'Neu'])
        ->assertOk()->assertJsonPath('data.title', 'Neu');
});

it('rejects an owner from another family', function () {
    $user = familyMember();
    $stranger = familyMember(); // eigene Familie
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/events', [
        'title' => 'Fremd-Owner',
        'starts_at' => now()->addDay()->toIso8601String(),
        'ends_at' => now()->addDay()->addHour()->toIso8601String(),
        'owner_id' => $stranger->id,
    ])->assertStatus(422)->assertJsonValidationErrorFor('owner_id');
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
