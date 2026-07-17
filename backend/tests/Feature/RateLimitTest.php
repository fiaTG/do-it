<?php

use Laravel\Sanctum\Sanctum;

// ADR-0025: Massen-Registrierung, Mail-Spam über Einladungen und teure
// Uploads sind gedrosselt. Auch fehlgeschlagene Versuche zählen mit.

it('throttles mass registration per ip', function () {
    foreach (range(1, 5) as $i) {
        $this->postJson('/api/v1/auth/register', [])->assertStatus(422);
    }

    $this->postJson('/api/v1/auth/register', [])->assertStatus(429);
});

it('throttles invite creation per user', function () {
    Sanctum::actingAs(familyMember());

    foreach (range(1, 5) as $i) {
        $this->postJson('/api/v1/invites', ['email' => "person{$i}@example.com", 'role' => 'child']);
    }

    $this->postJson('/api/v1/invites', ['email' => 'person6@example.com', 'role' => 'child'])
        ->assertStatus(429);
});
