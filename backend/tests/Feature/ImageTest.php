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

it('strips embedded metadata (EXIF/GPS) from uploaded images', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $fixture = base_path('tests/fixtures/photo-with-exif.jpg');
    // Vorbedingung: das Fixture hat echte eingebettete Metadaten.
    $before = @exif_read_data($fixture, null, true);
    expect(isset($before['IFD0']) || isset($before['EXIF']))->toBeTrue();

    $this->post('/api/v1/images', [
        'image' => new UploadedFile($fixture, 'photo.jpg', 'image/jpeg', null, true),
    ], ['Accept' => 'application/json'])->assertCreated();

    $tmp = tempnam(sys_get_temp_dir(), 'exif').'.jpg';
    file_put_contents($tmp, Storage::disk('public')->get(Image::first()->path));
    $after = @exif_read_data($tmp, null, true);
    @unlink($tmp);

    // Nach dem Upload keine EXIF/IFD0/GPS-Sektionen mehr.
    expect(isset($after['IFD0']))->toBeFalse();
    expect(isset($after['EXIF']))->toBeFalse();
    expect(isset($after['GPS']))->toBeFalse();
});
