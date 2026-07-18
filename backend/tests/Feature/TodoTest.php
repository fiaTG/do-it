<?php

use App\Models\Family;
use App\Models\Todo;
use App\Models\TodoPoint;
use Laravel\Sanctum\Sanctum;

it('creates a todo for the family', function () {
    Sanctum::actingAs(familyMember());

    $this->postJson('/api/v1/todos', ['title' => 'Müll rausbringen'])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Müll rausbringen')
        ->assertJsonPath('data.is_done', false);
});

it('lets any family member mark a todo as done', function () {
    $family = Family::factory()->create();
    $todo = Todo::create(['family_id' => $family->id, 'user_id' => familyMember($family)->id, 'title' => 'Test']);

    Sanctum::actingAs(familyMember($family));
    $this->patchJson("/api/v1/todos/{$todo->id}", ['is_done' => true])
        ->assertOk()
        ->assertJsonPath('data.is_done', true);
});

it('lets the creator delete their todo', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $todo = Todo::create(['family_id' => $user->family_id, 'user_id' => $user->id, 'title' => 'X']);

    $this->deleteJson("/api/v1/todos/{$todo->id}")->assertNoContent();
});

it('forbids a non-creator family member from deleting', function () {
    $family = Family::factory()->create();
    $todo = Todo::create(['family_id' => $family->id, 'user_id' => familyMember($family)->id, 'title' => 'X']);

    Sanctum::actingAs(familyMember($family));
    $this->deleteJson("/api/v1/todos/{$todo->id}")->assertForbidden();
});

it('forbids deleting another family todo', function () {
    Sanctum::actingAs(familyMember());
    $todo = Todo::create(['family_id' => familyMember()->family_id, 'user_id' => familyMember()->id, 'title' => 'Fremd']);

    $this->deleteJson("/api/v1/todos/{$todo->id}")->assertForbidden();
});

// --- Nest-Blätter (ADR-0026) -------------------------------------------------

it('awards a leaf to whoever completes a todo and removes it on uncheck', function () {
    $family = Family::factory()->create();
    $creator = familyMember($family);
    $doer = familyMember($family);
    $todo = Todo::create(['family_id' => $family->id, 'user_id' => $creator->id, 'title' => 'Spülen']);

    Sanctum::actingAs($doer);
    $this->patchJson("/api/v1/todos/{$todo->id}", ['is_done' => true])
        ->assertOk()->assertJsonPath('data.completed_by', $doer->id);
    expect((int) TodoPoint::where('user_id', $doer->id)->sum('points'))->toBe(1);

    $this->patchJson("/api/v1/todos/{$todo->id}", ['is_done' => false])->assertOk();
    expect(TodoPoint::count())->toBe(0);
});

it('keeps leaves when a completed todo is deleted', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $todo = Todo::create(['family_id' => $user->family_id, 'user_id' => $user->id, 'title' => 'Weg damit']);

    $this->patchJson("/api/v1/todos/{$todo->id}", ['is_done' => true])->assertOk();
    $this->deleteJson("/api/v1/todos/{$todo->id}")->assertNoContent();

    expect((int) TodoPoint::where('user_id', $user->id)->sum('points'))->toBe(1);
});

it('aggregates weekly and total leaves per family member', function () {
    $family = Family::factory()->create();
    $user = familyMember($family);
    // Alter Punkt (letzte Woche) + frischer Punkt (diese Woche)
    TodoPoint::create(['family_id' => $family->id, 'user_id' => $user->id]);
    TodoPoint::query()->update(['created_at' => now()->subWeeks(2)]);
    TodoPoint::create(['family_id' => $family->id, 'user_id' => $user->id]);
    // Punkt einer FREMDEN Familie darf nicht auftauchen
    $stranger = familyMember();
    TodoPoint::create(['family_id' => $stranger->family_id, 'user_id' => $stranger->id]);

    Sanctum::actingAs($user);
    $data = $this->getJson('/api/v1/todos/points')->assertOk()->json('data');

    expect($data['totals'][(string) $user->id])->toBe(2);
    expect($data['week'][(string) $user->id])->toBe(1);
    expect($data['totals'])->not->toHaveKey((string) $stranger->id);
});

it('checking off twice does not double-award', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $todo = Todo::create(['family_id' => $user->family_id, 'user_id' => $user->id, 'title' => 'X']);

    $this->patchJson("/api/v1/todos/{$todo->id}", ['is_done' => true])->assertOk();
    $this->patchJson("/api/v1/todos/{$todo->id}", ['is_done' => true])->assertOk();

    expect(TodoPoint::count())->toBe(1);
});
