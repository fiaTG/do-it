<?php

use App\Models\Todo;
use Laravel\Sanctum\Sanctum;

it('creates a todo for the family', function () {
    $user = familyMember();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/todos', ['title' => 'Müll rausbringen'])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Müll rausbringen')
        ->assertJsonPath('data.is_done', false);
});

it('marks a todo as done', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $todo = Todo::create(['family_id' => $user->family_id, 'title' => 'Test']);

    $this->patchJson("/api/v1/todos/{$todo->id}", ['is_done' => true])
        ->assertOk()
        ->assertJsonPath('data.is_done', true);
});

it('forbids deleting another family todo', function () {
    Sanctum::actingAs(familyMember());
    $todo = Todo::create(['family_id' => familyMember()->family_id, 'title' => 'Fremd']);

    $this->deleteJson("/api/v1/todos/{$todo->id}")->assertForbidden();
});
