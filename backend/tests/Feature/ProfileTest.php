<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('updates profile fields', function () {
    Sanctum::actingAs(familyMember());

    $this->putJson('/api/v1/profile', [
        'first_name' => 'Neu',
        'last_name' => 'Name',
        'gender' => 'w',
        'instagram' => 'https://instagram.com/x',
    ])->assertOk()
        ->assertJsonPath('data.first_name', 'Neu')
        ->assertJsonPath('data.socials.instagram', 'https://instagram.com/x');
});

it('rejects an invalid gender', function () {
    Sanctum::actingAs(familyMember());

    $this->putJson('/api/v1/profile', [
        'first_name' => 'A',
        'last_name' => 'B',
        'gender' => 'x',
    ])->assertStatus(422)->assertJsonValidationErrorFor('gender');
});

it('uploads an avatar', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $response = $this->post('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->image('me.jpg'),
    ], ['Accept' => 'application/json']);

    $response->assertOk();
    expect($response->json('data.avatar_url'))->not->toBeNull();
    expect($user->fresh()->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->fresh()->avatar_path);
});
