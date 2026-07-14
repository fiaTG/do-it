<?php

use App\Models\Image;
use App\Support\ImageVariants;
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
    expect($response->json('data.width'))->toBe(1200);
    expect($response->json('data.height'))->toBe(800);

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

it('captures the EXIF DateTimeOriginal as taken_at before stripping it', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $fixture = base_path('tests/fixtures/photo-with-exif.jpg');

    $this->post('/api/v1/images', [
        'image' => new UploadedFile($fixture, 'photo.jpg', 'image/jpeg', null, true),
    ], ['Accept' => 'application/json'])->assertCreated();

    // Aufnahmedatum laut EXIF-Fixture: 2025:03:25 20:11:56.
    expect(Image::first()->taken_at->format('Y-m-d H:i:s'))->toBe('2025-03-25 20:11:56');
});

it('falls back to no taken_at for images without EXIF', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('screenshot.png', 800, 600),
    ], ['Accept' => 'application/json'])->assertCreated();

    expect(Image::first()->taken_at)->toBeNull();
});

it('paginates the gallery and reports the free-tier quota', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    foreach (range(1, 65) as $i) {
        Image::create([
            'family_id' => $user->family_id,
            'user_id' => $user->id,
            'path' => "gallery/{$user->family_id}/fake-{$i}.jpg",
        ]);
    }

    $response = $this->getJson('/api/v1/images')->assertOk();

    expect($response->json('data'))->toHaveCount(60);
    expect($response->json('meta.total'))->toBe(65);
    expect($response->json('meta.last_page'))->toBe(2);
    expect($response->json('meta.limit'))->toBe(30); // Free-Familie, kein Abo

    $secondPage = $this->getJson('/api/v1/images?page=2')->assertOk();
    expect($secondPage->json('data'))->toHaveCount(5);
});

it('returns a single image with fresh signed URLs for family members', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);
    $image = Image::first();

    $this->getJson("/api/v1/images/{$image->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $image->id);
});

it('batch-deletes multiple own images including all files', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    foreach (['a.jpg', 'b.jpg'] as $name) {
        $this->post('/api/v1/images', [
            'image' => UploadedFile::fake()->image($name, 1200, 800),
        ], ['Accept' => 'application/json']);
    }
    $images = Image::all();

    $this->postJson('/api/v1/images/batch-delete', ['ids' => $images->pluck('id')->all()])
        ->assertNoContent();

    expect(Image::count())->toBe(0);
    foreach ($images as $image) {
        Storage::disk('public')->assertMissing($image->path);
        Storage::disk('public')->assertMissing($image->thumbnail_path);
        foreach (ImageVariants::WIDTHS as $width) {
            Storage::disk('public')->assertMissing(ImageVariants::path($image->path, $width));
        }
    }
});

it('refuses a batch-delete containing a foreign family image and deletes nothing', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);
    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('own.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);
    $own = Image::first();

    Sanctum::actingAs(familyMember());
    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('foreign.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);
    $foreign = Image::where('id', '!=', $own->id)->first();

    Sanctum::actingAs($user);
    $this->postJson('/api/v1/images/batch-delete', ['ids' => [$own->id, $foreign->id]])
        ->assertForbidden();

    // Kein partielles Löschen: beide Rows und alle Dateien bleiben erhalten.
    expect(Image::count())->toBe(2);
    Storage::disk('public')->assertExists($own->path);
    Storage::disk('public')->assertExists($own->thumbnail_path);
    Storage::disk('public')->assertExists($foreign->path);
    Storage::disk('public')->assertExists($foreign->thumbnail_path);
});

it('rejects a batch-delete with an empty ids array', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/images/batch-delete', ['ids' => []])
        ->assertStatus(422)->assertJsonValidationErrorFor('ids');
});

it('ignores non-existent ids in a batch-delete', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);
    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);
    $image = Image::first();

    $this->postJson('/api/v1/images/batch-delete', ['ids' => [$image->id, 999999]])
        ->assertNoContent();

    expect(Image::count())->toBe(0);
    Storage::disk('public')->assertMissing($image->path);
    Storage::disk('public')->assertMissing($image->thumbnail_path);
});

it('does not let a user fetch an image from another family', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());
    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);
    $image = Image::first();

    Sanctum::actingAs(familyMember());
    $this->getJson("/api/v1/images/{$image->id}")->assertForbidden();
});
