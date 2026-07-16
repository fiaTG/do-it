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
    Mail::assertQueued(InvitationMail::class);
});

it('renders the invitation mail (markdown components resolve)', function () {
    $family = Family::factory()->create(['name' => 'Mustermann']);
    $invite = Invite::create([
        'family_id' => $family->id,
        'email' => 'a@example.com',
        'token' => 'render-token',
        'expires_at' => now()->addDay(),
    ]);

    // render() wirft "No hint path defined for [mail]", falls die Mailable das
    // Markdown-Template fälschlich per view: statt markdown: einbindet.
    $html = (new InvitationMail($invite))->render();

    expect($html)->toContain('Mustermann');
    expect($html)->toContain('/register?token=render-token');
});

it('forbids users without a family from inviting', function () {
    Sanctum::actingAs(User::factory()->create(['family_id' => null]));

    $this->postJson('/api/v1/invites', ['email' => 'x@example.com'])
        ->assertForbidden();
});

it('forbids children from inviting', function () {
    $family = Family::factory()->create();
    Sanctum::actingAs(User::factory()->create(['family_id' => $family->id, 'role' => 'child']));

    $this->postJson('/api/v1/invites', ['email' => 'x@example.com'])->assertForbidden();
});

it('stores the chosen role on the invite', function () {
    Mail::fake();
    $family = Family::factory()->create();
    Sanctum::actingAs(User::factory()->create(['family_id' => $family->id]));

    $this->postJson('/api/v1/invites', ['email' => 'kid@example.com', 'role' => 'child'])
        ->assertCreated()
        ->assertJsonPath('data.role', 'child');
});

it('lists open invites for every family member, hiding accepted and expired ones', function () {
    $family = Family::factory()->create();
    $child = User::factory()->create(['family_id' => $family->id, 'role' => 'child']);
    Invite::create(['family_id' => $family->id, 'email' => 'open@example.com', 'token' => 't1', 'expires_at' => now()->addDay()]);
    Invite::create(['family_id' => $family->id, 'email' => 'expired@example.com', 'token' => 't2', 'expires_at' => now()->subDay()]);
    Invite::create(['family_id' => $family->id, 'email' => 'done@example.com', 'token' => 't3', 'expires_at' => now()->addDay(), 'accepted_at' => now()]);
    Invite::create(['family_id' => Family::factory()->create()->id, 'email' => 'foreign@example.com', 'token' => 't4', 'expires_at' => now()->addDay()]);

    Sanctum::actingAs($child);
    $response = $this->getJson('/api/v1/invites')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.email'))->toBe('open@example.com');
});

it('lets a guardian revoke an open invite', function () {
    $family = Family::factory()->create();
    $guardian = User::factory()->create(['family_id' => $family->id]);
    $invite = Invite::create(['family_id' => $family->id, 'email' => 'a@example.com', 'token' => 't1', 'expires_at' => now()->addDay()]);

    Sanctum::actingAs($guardian);
    $this->deleteJson("/api/v1/invites/{$invite->id}")->assertNoContent();
    expect(Invite::count())->toBe(0);
});

it('forbids children and other families from revoking invites', function () {
    $family = Family::factory()->create();
    $invite = Invite::create(['family_id' => $family->id, 'email' => 'a@example.com', 'token' => 't1', 'expires_at' => now()->addDay()]);

    Sanctum::actingAs(User::factory()->create(['family_id' => $family->id, 'role' => 'child']));
    $this->deleteJson("/api/v1/invites/{$invite->id}")->assertForbidden();

    Sanctum::actingAs(User::factory()->create(['family_id' => Family::factory()->create()->id]));
    $this->deleteJson("/api/v1/invites/{$invite->id}")->assertForbidden();

    expect(Invite::count())->toBe(1);
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
