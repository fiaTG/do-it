<?php

use App\Models\Family;
use App\Models\Todo;
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
