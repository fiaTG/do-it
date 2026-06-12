<?php

use App\Mail\InvitationMail;
use App\Models\Family;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

it('lets a family member invite someone', function () {
    Mail::fake();
    $family = Family::factory()->create();
    Sanctum::actingAs(User::factory()->create(['family_id' => $family->id]));

    $this->postJson('/api/v1/invites', ['email' => 'new@example.com'])
        ->assertCreated()
        ->assertJsonPath('data.email', 'new@example.com');

    expect(Invite::where('email', 'new@example.com')->exists())->toBeTrue();
    Mail::assertSent(InvitationMail::class);
});

it('forbids users without a family from inviting', function () {
    Sanctum::actingAs(User::factory()->create(['family_id' => null]));

    $this->postJson('/api/v1/invites', ['email' => 'x@example.com'])
        ->assertForbidden();
});

it('shows a valid invite by token', function () {
    $family = Family::factory()->create();
    Invite::create([
        'family_id' => $family->id,
        'email' => 'a@example.com',
        'token' => 'valid-token',
        'expires_at' => now()->addDay(),
    ]);

    $this->getJson('/api/v1/invites/valid-token')
        ->assertOk()
        ->assertJsonPath('data.family.name', $family->name);
});

it('returns 404 for an expired invite', function () {
    $family = Family::factory()->create();
    Invite::create([
        'family_id' => $family->id,
        'email' => 'a@example.com',
        'token' => 'old-token',
        'expires_at' => now()->subDay(),
    ]);

    $this->getJson('/api/v1/invites/old-token')->assertNotFound();
});
