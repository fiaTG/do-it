<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('Sup3r!pass'),
    ]);
});

it('logs in with valid credentials', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'Sup3r!pass',
    ])->assertOk()->assertJsonPath('data.email', 'login@example.com');
});

it('rejects invalid credentials with a generic error', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(422)->assertJsonValidationErrorFor('email');

    $this->assertGuest();
});

it('issues an API token for native clients', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'Sup3r!pass',
        'device_name' => 'iphone',
    ])->assertOk()->assertJsonStructure(['token']);

    expect($this->user->fresh()->tokens()->count())->toBe(1);
});

it('rate limits repeated failed logins', function () {
    foreach (range(1, 6) as $ignored) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ]);
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});
