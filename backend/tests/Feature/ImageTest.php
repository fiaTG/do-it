<?php

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('uploads an image to storage', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'title' => 'Urlaub',
        'image' => UploadedFile::fake()->image('foto.jpg'),
    ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Urlaub');

    expect(Image::count())->toBe(1);
    Storage::disk('public')->assertExists(Image::first()->path);
});

it('rejects a non-image upload', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->create('schadcode.pdf', 100, 'application/pdf'),
    ], ['Accept' => 'application/json'])
        ->assertStatus(422)->assertJsonValidationErrorFor('image');
});

it('deletes an image and its file', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg'),
    ], ['Accept' => 'application/json']);

    $image = Image::first();
    $this->deleteJson("/api/v1/images/{$image->id}")->assertNoContent();

    expect(Image::count())->toBe(0);
    Storage::disk('public')->assertMissing($image->path);
});
