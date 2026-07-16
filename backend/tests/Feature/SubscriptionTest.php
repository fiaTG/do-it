<?php

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
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
        ->assertJsonPath('data.plan', 'monthly'); // Default ohne Angabe (ADR-0022)

    $this->deleteJson('/api/v1/subscription')->assertNoContent();

    $this->getJson('/api/v1/subscription')->assertJsonPath('data.is_premium', false);
});

it('activates the yearly plan with a one-year term', function () {
    Sanctum::actingAs(familyMember());

    $response = $this->postJson('/api/v1/subscription', ['plan' => 'yearly'])
        ->assertCreated()
        ->assertJsonPath('data.is_premium', true)
        ->assertJsonPath('data.plan', 'yearly');

    $expires = new DateTimeImmutable($response->json('data.expires_at'));
    expect($expires->getTimestamp())->toBeGreaterThan(now()->addMonths(11)->timestamp);
});

it('rejects an unknown plan', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/subscription', ['plan' => 'diamond'])
        ->assertStatus(422)->assertJsonValidationErrorFor('plan');
});

it('gates premium-only routes via the premium middleware', function () {
    Route::middleware(['auth:sanctum', 'premium'])->get('/api/v1/premium-only-test', fn () => response()->json(['ok' => true]));

    $user = familyMember();
    Sanctum::actingAs($user);

    // Free-Familie -> 403 mit deutscher Meldung.
    $this->getJson('/api/v1/premium-only-test')->assertForbidden();

    // Nach Aktivierung -> 200.
    $this->postJson('/api/v1/subscription')->assertCreated();
    $user->family->unsetRelation('subscription');
    $this->getJson('/api/v1/premium-only-test')->assertOk()->assertJson(['ok' => true]);
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

it('forbids children from managing the family subscription (Review C-01)', function () {
    $guardian = familyMember();
    $child = User::factory()->create(['family_id' => $guardian->family_id, 'role' => 'child']);
    Sanctum::actingAs($child);

    $this->postJson('/api/v1/subscription', ['plan' => 'monthly'])->assertForbidden();
    $this->deleteJson('/api/v1/subscription')->assertForbidden();
});
