<?php

use App\Models\Contact;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('creates a contact with photo and lists it family-scoped', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/contacts', [
        'name' => 'Dr. Zahnlücke',
        'category' => 'Arzt',
        'phone' => '0621 123456',
        'website' => 'https://praxis.example.com',
        'photo' => UploadedFile::fake()->image('praxis.jpg', 400, 400),
    ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Dr. Zahnlücke')
        ->assertJsonPath('data.category', 'Arzt');

    $contact = Contact::first();
    expect($contact->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($contact->photo_path);
    expect($this->getJson('/api/v1/contacts')->json('data.0.photo_url'))->not->toBeNull();

    // Fremde Familie sieht nichts.
    Sanctum::actingAs(familyMember());
    expect($this->getJson('/api/v1/contacts')->json('data'))->toHaveCount(0);
});

it('lets the creator and guardians manage, but not other children', function () {
    $guardian = familyMember();
    $childCreator = User::factory()->create(['family_id' => $guardian->family_id, 'role' => 'child']);
    $otherChild = User::factory()->create(['family_id' => $guardian->family_id, 'role' => 'child']);
    $contact = Contact::create([
        'family_id' => $guardian->family_id,
        'user_id' => $childCreator->id,
        'name' => 'Oma Erna',
    ]);

    // Anderes Kind: nur ansehen.
    Sanctum::actingAs($otherChild);
    $this->patchJson("/api/v1/contacts/{$contact->id}", ['name' => 'X'])->assertForbidden();
    $this->deleteJson("/api/v1/contacts/{$contact->id}")->assertForbidden();

    // Ersteller-Kind darf bearbeiten.
    Sanctum::actingAs($childCreator);
    $this->patchJson("/api/v1/contacts/{$contact->id}", ['name' => 'Oma Erna (USA)'])
        ->assertOk()->assertJsonPath('data.name', 'Oma Erna (USA)');

    // Verwalter darf löschen.
    Sanctum::actingAs($guardian);
    $this->deleteJson("/api/v1/contacts/{$contact->id}")->assertNoContent();
    expect(Contact::count())->toBe(0);
});

it('deletes the photo file when the contact is removed', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/contacts', [
        'name' => 'Verein',
        'photo' => UploadedFile::fake()->image('logo.png', 200, 200),
    ], ['Accept' => 'application/json'])->assertCreated();
    $contact = Contact::first();

    $this->deleteJson("/api/v1/contacts/{$contact->id}")->assertNoContent();
    Storage::disk('public')->assertMissing($contact->photo_path);
});

it('forbids managing contacts of another family', function () {
    $owner = familyMember();
    $contact = Contact::create([
        'family_id' => $owner->family_id,
        'user_id' => $owner->id,
        'name' => 'Intern',
    ]);

    Sanctum::actingAs(familyMember());
    $this->patchJson("/api/v1/contacts/{$contact->id}", ['name' => 'Hack'])->assertForbidden();
    $this->deleteJson("/api/v1/contacts/{$contact->id}")->assertForbidden();
});

it('rejects an invalid website url', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/contacts', [
        'name' => 'Test',
        'website' => 'keine-url',
    ])->assertStatus(422)->assertJsonValidationErrorFor('website');
});

it('lets a guardian set the family location, children not', function () {
    $family = Family::factory()->create();
    $guardian = User::factory()->create(['family_id' => $family->id]);
    $child = User::factory()->create(['family_id' => $family->id, 'role' => 'child']);

    Sanctum::actingAs($child);
    $this->patchJson('/api/v1/family/location', [
        'location_name' => 'Mannheim',
        'latitude' => 49.4875,
        'longitude' => 8.466,
    ])->assertForbidden();

    Sanctum::actingAs($guardian);
    $this->patchJson('/api/v1/family/location', [
        'location_name' => 'Mannheim',
        'latitude' => 49.4875,
        'longitude' => 8.466,
    ])->assertOk()->assertJsonPath('data.location_name', 'Mannheim');

    // Koordinaten landen in der Familie und kommen über /auth/me an.
    expect((float) $family->fresh()->latitude)->toBe(49.4875);
    $this->getJson('/api/v1/auth/me')->assertJsonPath('data.family.location_name', 'Mannheim');
});

it('rejects out-of-range coordinates', function () {
    Sanctum::actingAs(familyMember());

    $this->patchJson('/api/v1/family/location', [
        'location_name' => 'Nirgendwo',
        'latitude' => 123,
        'longitude' => 8.4,
    ])->assertStatus(422)->assertJsonValidationErrorFor('latitude');
});
