<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

it('changes the password with the correct current password', function () {
    $user = User::factory()->create(['password' => Hash::make('Old!pass1')]);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/auth/password', [
        'current_password' => 'Old!pass1',
        'password' => 'New!pass2',
        'password_confirmation' => 'New!pass2',
    ])->assertNoContent();

    expect(Hash::check('New!pass2', $user->fresh()->password))->toBeTrue();
});

it('rejects a wrong current password', function () {
    $user = User::factory()->create(['password' => Hash::make('Old!pass1')]);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/auth/password', [
        'current_password' => 'totally-wrong',
        'password' => 'New!pass2',
        'password_confirmation' => 'New!pass2',
    ])->assertStatus(422)->assertJsonValidationErrorFor('current_password');
});

it('rejects a weak new password', function () {
    $user = User::factory()->create(['password' => Hash::make('Old!pass1')]);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/auth/password', [
        'current_password' => 'Old!pass1',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ])->assertStatus(422)->assertJsonValidationErrorFor('password');
});

it('requires authentication', function () {
    $this->putJson('/api/v1/auth/password', [])->assertUnauthorized();
});

it('revokes other api tokens on password change but keeps the current one (Review M-06)', function () {
    $user = familyMember();
    $keep = $user->createToken('handy-aktuell');
    $user->createToken('altes-tablet');
    $user->createToken('geklautes-geraet');

    $this->withHeader('Authorization', 'Bearer '.$keep->plainTextToken)
        ->putJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'password' => 'N3ues!Passwort',
            'password_confirmation' => 'N3ues!Passwort',
        ])->assertNoContent();

    // Nur der aktuell benutzte Token überlebt.
    expect($user->tokens()->count())->toBe(1);
    expect($user->tokens()->first()->name)->toBe('handy-aktuell');
});
