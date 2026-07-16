<?php

use App\Models\Family;
use App\Models\Invite;
use App\Models\User;

it('registers a new user', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Erika',
        'last_name' => 'Muster',
        'email' => 'erika@example.com',
        'password' => 'Sup3r!pass',
        'password_confirmation' => 'Sup3r!pass',
    ]);

    $response->assertCreated()->assertJsonPath('data.email', 'erika@example.com');
    expect(User::where('email', 'erika@example.com')->exists())->toBeTrue();
});

it('issues an API token for native clients on register', function () {
    $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Nat',
        'last_name' => 'Ive',
        'email' => 'native@example.com',
        'password' => 'Sup3r!pass',
        'password_confirmation' => 'Sup3r!pass',
        'device_name' => 'pixel',
    ])->assertCreated()->assertJsonStructure(['token']);

    expect(User::where('email', 'native@example.com')->first()->tokens()->count())->toBe(1);
});

it('rejects weak passwords', function () {
    $this->postJson('/api/v1/auth/register', [
        'first_name' => 'A',
        'last_name' => 'B',
        'email' => 'weak@example.com',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ])->assertStatus(422)->assertJsonValidationErrorFor('password');
});

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'first_name' => 'A',
        'last_name' => 'B',
        'email' => 'taken@example.com',
        'password' => 'Sup3r!pass',
        'password_confirmation' => 'Sup3r!pass',
    ])->assertStatus(422)->assertJsonValidationErrorFor('email');
});

it('joins a family via a valid invite token', function () {
    $family = Family::factory()->create();
    $invite = Invite::create([
        'family_id' => $family->id,
        'email' => 'join@example.com',
        'token' => 'tok123',
        'expires_at' => now()->addDay(),
    ]);

    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Joi',
        'last_name' => 'Ner',
        'email' => 'join@example.com',
        'password' => 'Sup3r!pass',
        'password_confirmation' => 'Sup3r!pass',
        'token' => 'tok123',
    ]);

    $response->assertCreated()->assertJsonPath('data.family_id', $family->id);
    expect($invite->fresh()->accepted_at)->not->toBeNull();
});

it('applies the role chosen in the invite on registration', function () {
    $family = Family::factory()->create();
    Invite::create([
        'family_id' => $family->id,
        'email' => 'kid@example.com',
        'role' => 'child',
        'token' => 'kid-token',
        'expires_at' => now()->addDay(),
    ]);

    $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Kiki',
        'last_name' => 'Kind',
        'email' => 'kid@example.com',
        'password' => 'Sup3r!pass',
        'password_confirmation' => 'Sup3r!pass',
        'token' => 'kid-token',
    ])->assertCreated()->assertJsonPath('data.role', 'child');
});

it('rejects registration with an invalid or used invite token (Review H-01)', function () {
    $family = Family::factory()->create();
    Invite::create([
        'family_id' => $family->id,
        'email' => 'used@example.com',
        'token' => 'used-token',
        'expires_at' => now()->addDay(),
        'accepted_at' => now(),
    ]);

    foreach (['unknown-token', 'used-token'] as $token) {
        $this->postJson('/api/v1/auth/register', [
            'first_name' => 'X',
            'last_name' => 'Y',
            'email' => "x-{$token}@example.com",
            'password' => 'Sup3r!pass',
            'password_confirmation' => 'Sup3r!pass',
            'token' => $token,
        ])->assertStatus(422)->assertJsonValidationErrorFor('token');
    }

    // Kein stiller familienloser Account entstanden.
    expect(User::where('email', 'like', 'x-%')->count())->toBe(0);
});

it('binds the invite to the invited email address (Review H-01)', function () {
    $family = Family::factory()->create();
    Invite::create([
        'family_id' => $family->id,
        'email' => 'eingeladen@example.com',
        'token' => 'bound-token',
        'expires_at' => now()->addDay(),
    ]);

    // Fremde Adresse mit gültigem Token -> 422, kein Beitritt.
    $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Fremd',
        'last_name' => 'Ling',
        'email' => 'angreifer@example.com',
        'password' => 'Sup3r!pass',
        'password_confirmation' => 'Sup3r!pass',
        'token' => 'bound-token',
    ])->assertStatus(422)->assertJsonValidationErrorFor('email');

    // Eingeladene Adresse (case-insensitiv) -> Beitritt klappt.
    $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Ein',
        'last_name' => 'Geladen',
        'email' => 'Eingeladen@Example.com',
        'password' => 'Sup3r!pass',
        'password_confirmation' => 'Sup3r!pass',
        'token' => 'bound-token',
    ])->assertCreated()->assertJsonPath('data.family_id', $family->id);
});
