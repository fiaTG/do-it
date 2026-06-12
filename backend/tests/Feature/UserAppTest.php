<?php

use App\Models\App;
use Laravel\Sanctum\Sanctum;

it('lists the app catalog', function () {
    App::create(['slug' => 'gallery', 'name' => 'Galerie', 'icon' => 'fa-image']);
    Sanctum::actingAs(familyMember());

    $this->getJson('/api/v1/apps')->assertOk()->assertJsonCount(1, 'data');
});

it('adds and removes a dashboard app', function () {
    $app = App::create(['slug' => 'todo', 'name' => 'ToDo', 'icon' => 'fa-list']);
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/me/apps', ['app_id' => $app->id])->assertNoContent();
    expect($user->apps()->count())->toBe(1);

    $this->getJson('/api/v1/me/apps')->assertOk()->assertJsonCount(1, 'data');

    $this->deleteJson("/api/v1/me/apps/{$app->id}")->assertNoContent();
    expect($user->fresh()->apps()->count())->toBe(0);
});
