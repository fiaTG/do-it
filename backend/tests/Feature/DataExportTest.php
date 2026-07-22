<?php

use App\Models\Contact;
use App\Models\Family;
use App\Models\Todo;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('exports the user own account, family and authored content', function () {
    $family = Family::factory()->create(['name' => 'Musterfamilie', 'location_name' => 'Heidelberg']);
    $user = User::factory()->create(['family_id' => $family->id, 'email' => 'ich@example.com']);
    Todo::create(['family_id' => $family->id, 'user_id' => $user->id, 'title' => 'Mein ToDo']);
    Contact::create(['family_id' => $family->id, 'user_id' => $user->id, 'name' => 'Kinderarzt']);

    Sanctum::actingAs($user);
    $res = $this->getJson('/api/v1/me/export')->assertOk();

    $res->assertJsonPath('konto.email', 'ich@example.com');
    $res->assertJsonPath('familie.name', 'Musterfamilie');
    expect($res->json('meine_inhalte.todos'))->toHaveCount(1);
    expect($res->json('meine_inhalte.todos.0.title'))->toBe('Mein ToDo');
    expect($res->json('meine_inhalte.kontakte.0.name'))->toBe('Kinderarzt');
});

it('never exposes the password hash or tokens', function () {
    Sanctum::actingAs(familyMember());

    $body = $this->getJson('/api/v1/me/export')->assertOk()->content();
    expect($body)->not->toContain('password');
});

it('only exports the requesting user content, not other members', function () {
    $family = Family::factory()->create();
    $me = User::factory()->create(['family_id' => $family->id]);
    $other = User::factory()->create(['family_id' => $family->id]);
    Todo::create(['family_id' => $family->id, 'user_id' => $me->id, 'title' => 'Meins']);
    Todo::create(['family_id' => $family->id, 'user_id' => $other->id, 'title' => 'Fremdes']);

    Sanctum::actingAs($me);
    $todos = $this->getJson('/api/v1/me/export')->assertOk()->json('meine_inhalte.todos');

    expect($todos)->toHaveCount(1);
    expect($todos[0]['title'])->toBe('Meins');
});

it('requires authentication', function () {
    $this->getJson('/api/v1/me/export')->assertUnauthorized();
});
