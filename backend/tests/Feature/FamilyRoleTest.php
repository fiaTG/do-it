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

it('keeps at least one guardian per family (Review M-03)', function () {
    $a = familyMember(); // Guardian A
    $b = User::factory()->create(['family_id' => $a->family_id]); // Guardian B

    // Mit zwei Verwaltern ist Herabstufen erlaubt – einer bleibt immer übrig.
    Sanctum::actingAs($b);
    $this->patchJson("/api/v1/family/members/{$a->id}/role", ['role' => 'child'])->assertOk();
    expect(User::where('family_id', $a->family_id)->where('role', 'guardian')->count())->toBe(1);

    // Der verbleibende Verwalter kann sich selbst nicht ändern (422) und
    // Kinder dürfen gar nicht (403) – zusammen mit dem Letzter-Guardian-Guard
    // im Controller ist "Familie ohne Verwalter" damit unerreichbar.
    $this->patchJson("/api/v1/family/members/{$b->id}/role", ['role' => 'child'])->assertStatus(422);
    Sanctum::actingAs($a->fresh());
    $this->patchJson("/api/v1/family/members/{$b->id}/role", ['role' => 'child'])->assertForbidden();
});
