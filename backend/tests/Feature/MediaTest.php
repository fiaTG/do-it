<?php

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

it('serves an image through a valid signed url', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 800, 600),
    ], ['Accept' => 'application/json'])->assertCreated();

    $image = Image::first();
    $signed = URL::temporarySignedRoute('media.image', now()->addHour(), ['image' => $image->id]);

    $this->get($signed)->assertOk();
});

it('rejects media access without a valid signature', function () {
    $user = familyMember();
    $image = Image::create([
        'family_id' => $user->family_id,
        'user_id' => $user->id,
        'path' => 'gallery/1/whatever.jpg',
    ]);

    // Ohne ?signature=... -> 403 durch die signed-Middleware.
    $this->get("/api/v1/media/images/{$image->id}")->assertForbidden();
});

it('rejects media with a tampered signature', function () {
    $user = familyMember();
    $image = Image::create([
        'family_id' => $user->family_id,
        'user_id' => $user->id,
        'path' => 'gallery/1/whatever.jpg',
    ]);
    $signed = URL::temporarySignedRoute('media.image', now()->addHour(), ['image' => $image->id]);

    $this->get($signed.'tampered')->assertForbidden();
});
