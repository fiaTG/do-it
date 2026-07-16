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

it('moves a deleted image to the trash and keeps its files', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);

    $image = Image::first();
    $this->deleteJson("/api/v1/images/{$image->id}")->assertNoContent();

    // Papierkorb (ADR-0020): Soft-Delete, Dateien bleiben für Restore erhalten.
    expect(Image::count())->toBe(0);
    expect(Image::onlyTrashed()->count())->toBe(1);
    Storage::disk('public')->assertExists($image->path);
    Storage::disk('public')->assertExists($image->thumbnail_path);

    $this->getJson('/api/v1/images')->assertOk()->assertJsonPath('meta.total', 0);
    $trash = $this->getJson('/api/v1/images/trash')->assertOk();
    expect($trash->json('data'))->toHaveCount(1);
    expect($trash->json('data.0.deleted_at'))->not->toBeNull();
    expect($trash->json('data.0.expires_at'))->not->toBeNull();
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

it('batch-deletes multiple own images into the trash', function () {
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

    // Papierkorb statt endgültig: Rows soft-deleted, Dateien bleiben (ADR-0020).
    expect(Image::count())->toBe(0);
    expect(Image::onlyTrashed()->count())->toBe(2);
    foreach ($images as $image) {
        Storage::disk('public')->assertExists($image->path);
        Storage::disk('public')->assertExists($image->thumbnail_path);
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
    expect(Image::onlyTrashed()->count())->toBe(1);
});

it('restores images from the trash', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'title' => 'Zurückgeholt',
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);
    $image = Image::first();

    $this->deleteJson("/api/v1/images/{$image->id}")->assertNoContent();
    $this->postJson('/api/v1/images/restore', ['ids' => [$image->id]])->assertNoContent();

    expect(Image::count())->toBe(1);
    expect(Image::onlyTrashed()->count())->toBe(0);
    $this->getJson('/api/v1/images')->assertOk()->assertJsonPath('data.0.title', 'Zurückgeholt');
});

it('blocks a restore that would exceed the free gallery limit', function () {
    config(['features.free_limits.gallery_images' => 1]);
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    // Bild A hochladen, in den Papierkorb, dann Bild B hochladen (Limit 1 ist
    // wieder belegt – Papierkorb zählt nicht mit). Restore von A wäre Bild 2.
    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 800, 600),
    ], ['Accept' => 'application/json'])->assertCreated();
    $a = Image::first();
    $this->deleteJson("/api/v1/images/{$a->id}")->assertNoContent();

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('b.jpg', 800, 600),
    ], ['Accept' => 'application/json'])->assertCreated();

    $this->postJson('/api/v1/images/restore', ['ids' => [$a->id]])->assertForbidden();
    expect(Image::onlyTrashed()->count())->toBe(1);
});

it('purges trashed images permanently including all files', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json']);
    $image = Image::first();

    $this->deleteJson("/api/v1/images/{$image->id}")->assertNoContent();
    $this->postJson('/api/v1/images/purge', ['ids' => [$image->id]])->assertNoContent();

    expect(Image::withTrashed()->count())->toBe(0);
    Storage::disk('public')->assertMissing($image->path);
    Storage::disk('public')->assertMissing($image->thumbnail_path);
    foreach (ImageVariants::WIDTHS as $width) {
        Storage::disk('public')->assertMissing(ImageVariants::path($image->path, $width));
    }
});

it('purge only touches trashed images, active ones stay', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('active.jpg', 800, 600),
    ], ['Accept' => 'application/json']);
    $active = Image::first();

    // Purge auf ein AKTIVES Bild ist ein No-op (Endpoint arbeitet onlyTrashed).
    $this->postJson('/api/v1/images/purge', ['ids' => [$active->id]])->assertNoContent();
    expect(Image::count())->toBe(1);
    Storage::disk('public')->assertExists($active->path);
});

it('forbids restoring or purging another family\'s trashed image', function () {
    Storage::fake('public');
    $owner = familyMember();
    Sanctum::actingAs($owner);
    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 800, 600),
    ], ['Accept' => 'application/json']);
    $image = Image::first();
    $this->deleteJson("/api/v1/images/{$image->id}")->assertNoContent();

    Sanctum::actingAs(familyMember());
    $this->postJson('/api/v1/images/restore', ['ids' => [$image->id]])->assertForbidden();
    $this->postJson('/api/v1/images/purge', ['ids' => [$image->id]])->assertForbidden();
    expect(Image::onlyTrashed()->count())->toBe(1);
});

it('does not count trashed images against the upload limit', function () {
    config(['features.free_limits.gallery_images' => 1]);
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 800, 600),
    ], ['Accept' => 'application/json'])->assertCreated();
    $this->deleteJson('/api/v1/images/'.Image::first()->id)->assertNoContent();

    // Limit 1, aber das gelöschte Bild liegt im Papierkorb -> Upload erlaubt.
    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('b.jpg', 800, 600),
    ], ['Accept' => 'application/json'])->assertCreated();
});

it('prunes expired trash including files but keeps recent trash', function () {
    Storage::fake('public');
    $user = familyMember();
    Sanctum::actingAs($user);

    foreach (['old.jpg', 'recent.jpg'] as $name) {
        $this->post('/api/v1/images', [
            'image' => UploadedFile::fake()->image($name, 800, 600),
        ], ['Accept' => 'application/json']);
    }
    [$old, $recent] = Image::all();
    $this->postJson('/api/v1/images/batch-delete', ['ids' => [$old->id, $recent->id]])
        ->assertNoContent();

    // Frist des einen Bildes künstlich ablaufen lassen (31 Tage her).
    Image::withTrashed()->whereKey($old->id)->update(['deleted_at' => now()->subDays(31)]);

    $this->artisan('model:prune', ['--model' => Image::class])->assertSuccessful();

    expect(Image::withTrashed()->count())->toBe(1);
    expect(Image::onlyTrashed()->first()->id)->toBe($recent->id);
    Storage::disk('public')->assertMissing($old->path);
    Storage::disk('public')->assertMissing($old->thumbnail_path);
    Storage::disk('public')->assertExists($recent->path);
});

it('generates a blur-up placeholder for uploaded images', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $response = $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('a.jpg', 1200, 800),
    ], ['Accept' => 'application/json'])->assertCreated();

    expect($response->json('data.placeholder'))->toStartWith('data:image/jpeg;base64,');
    expect($response->json('data.processing'))->toBeFalse();
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

it('fails closed: a broken image is rejected and nothing is stored (Review H-02)', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    // Gültiger GIF-Header + Müll: passiert die image-Validierung (MIME),
    // scheitert aber beim Dekodieren – früher wurde dann das Original samt
    // potenzieller Metadaten gespeichert, jetzt wird abgelehnt.
    $broken = UploadedFile::fake()->createWithContent('kaputt.gif', 'GIF89a'.random_bytes(256));

    $this->post('/api/v1/images', [
        'image' => $broken,
    ], ['Accept' => 'application/json'])->assertStatus(422);

    expect(Image::count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('rejects absurdly large image dimensions (decompression bombs)', function () {
    Storage::fake('public');
    Sanctum::actingAs(familyMember());

    $this->post('/api/v1/images', [
        'image' => UploadedFile::fake()->image('riesig.jpg', 9000, 9000),
    ], ['Accept' => 'application/json'])
        ->assertStatus(422)->assertJsonValidationErrorFor('image');
});
