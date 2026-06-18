<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lets a guardian set a member to child', function () {
    $guardian = familyMember();
    $member = User::factory()->create(['family_id' => $guardian->family_id]);
    Sanctum::actingAs($guardian);

    $this->patchJson("/api/v1/family/members/{$member->id}/role", ['role' => 'child'])
        ->assertOk()->assertJsonPath('data.role', 'child');

    expect($member->fresh()->role)->toBe('child');
});

it('forbids a child from changing roles', function () {
    $guardian = familyMember();
    $child = User::factory()->create(['family_id' => $guardian->family_id, 'role' => 'child']);
    Sanctum::actingAs($child);

    $this->patchJson("/api/v1/family/members/{$guardian->id}/role", ['role' => 'child'])
        ->assertForbidden();
});

it('forbids changing your own role', function () {
    $guardian = familyMember();
    Sanctum::actingAs($guardian);

    $this->patchJson("/api/v1/family/members/{$guardian->id}/role", ['role' => 'child'])
        ->assertStatus(422);
});

it('forbids changing the role of another family member', function () {
    Sanctum::actingAs(familyMember());
    $stranger = familyMember(); // eigene Familie

    $this->patchJson("/api/v1/family/members/{$stranger->id}/role", ['role' => 'child'])
        ->assertForbidden();
});
