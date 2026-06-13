<?php

use App\Models\Subscription;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('reports the free plan by default', function () {
    Sanctum::actingAs(familyMember());

    $this->getJson('/api/v1/subscription')
        ->assertOk()
        ->assertJsonPath('data.is_premium', false)
        ->assertJsonPath('data.plan', 'free');
});

it('activates premium and then cancels it', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/subscription')
        ->assertCreated()
        ->assertJsonPath('data.is_premium', true)
        ->assertJsonPath('data.plan', 'premium');

    $this->deleteJson('/api/v1/subscription')->assertNoContent();

    $this->getJson('/api/v1/subscription')->assertJsonPath('data.is_premium', false);
});

it('blocks gallery upload on the free plan when the limit is reached', function () {
    config(['features.free_limits.gallery_images' => 0]);
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json'])->assertForbidden();
});

it('allows unlimited gallery uploads on premium', function () {
    config(['features.free_limits.gallery_images' => 0]);
    Storage::fake('public');
    $user = familyMember();
    Subscription::create([
        'family_id' => $user->family_id,
        'plan' => 'premium',
        'status' => 'active',
        'provider' => 'manual',
        'expires_at' => now()->addMonth(),
    ]);
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json'])->assertCreated();
});
