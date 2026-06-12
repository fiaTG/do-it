<?php

use App\Models\Shop;
use App\Models\ShoppingItem;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists only the own family items', function () {
    $user = familyMember();
    Sanctum::actingAs($user);

    ShoppingItem::create(['family_id' => $user->family_id, 'name' => 'Apfel', 'quantity' => 2]);
    ShoppingItem::create(['family_id' => familyMember()->family_id, 'name' => 'Fremd', 'quantity' => 1]);

    $this->getJson('/api/v1/shopping-items')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Apfel');
});

it('creates a shopping item', function () {
    Sanctum::actingAs(familyMember());
    $shop = Shop::create(['name' => 'Aldi']);

    $this->postJson('/api/v1/shopping-items', [
        'name' => 'Milch',
        'quantity' => 2,
        'shop_id' => $shop->id,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Milch')
        ->assertJsonPath('data.shop.name', 'Aldi');
});

it('toggles is_purchased', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $item = ShoppingItem::create(['family_id' => $user->family_id, 'name' => 'Brot', 'quantity' => 1]);

    $this->patchJson("/api/v1/shopping-items/{$item->id}", ['is_purchased' => true])
        ->assertOk()
        ->assertJsonPath('data.is_purchased', true);
});

it('forbids updating another family item', function () {
    Sanctum::actingAs(familyMember());
    $item = ShoppingItem::create(['family_id' => familyMember()->family_id, 'name' => 'X', 'quantity' => 1]);

    $this->patchJson("/api/v1/shopping-items/{$item->id}", ['is_purchased' => true])
        ->assertForbidden();
});

it('deletes a shopping item', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $item = ShoppingItem::create(['family_id' => $user->family_id, 'name' => 'Zucker', 'quantity' => 1]);

    $this->deleteJson("/api/v1/shopping-items/{$item->id}")->assertNoContent();
    expect(ShoppingItem::find($item->id))->toBeNull();
});

it('returns 409 when the user has no family', function () {
    Sanctum::actingAs(User::factory()->create(['family_id' => null]));

    $this->getJson('/api/v1/shopping-items')->assertStatus(409);
});
