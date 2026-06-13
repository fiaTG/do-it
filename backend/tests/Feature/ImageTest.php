<?php

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('uploads an image and creates a thumbnail', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $response = $this->post('/api/v1/images', [
        'title' => 'Urlaub',
        'image' => UploadedFile::fake()->image('foto.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);

    $response->assertCreated()->assertJsonPath('data.title', 'Urlaub');
    expect($response->json('data.thumbnail_url'))->not->toBeNull();

    $image = Image::first();
    expect($image->thumbnail_path)->not->toBeNull();
    Storage::disk('public')->assertExists($image->path);
    Storage::disk('public')->assertExists($image->thumbnail_path);
});

it('rejects a non-image upload', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->create('schadcode.pdf', 100, 'application/pdf'),
    ], ['Accept' => 'application/json'])
        ->assertStatus(422)->assertJsonValidationErrorFor('image');
});

it('deletes an image and both files', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);

    $image = Image::first();
    $this->deleteJson("/api/v1/images/{$image->id}")->assertNoContent();

    expect(Image::count())->toBe(0);
    Storage::disk('public')->assertMissing($image->path);
    Storage::disk('public')->assertMissing($image->thumbnail_path);
});
