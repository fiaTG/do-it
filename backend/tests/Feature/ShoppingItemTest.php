<?php

use App\Models\Family;
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

it('merges quantity for the same item and shop', function () {
    Sanctum::actingAs(familyMember());
    $shop = Shop::create(['name' => 'Aldi']);

    $this->postJson('/api/v1/shopping-items', ['name' => 'Milch', 'quantity' => 2, 'shop_id' => $shop->id])
        ->assertCreated();
    $this->postJson('/api/v1/shopping-items', ['name' => 'milch', 'quantity' => 3, 'shop_id' => $shop->id])
        ->assertOk()
        ->assertJsonPath('data.quantity', 5);

    expect(ShoppingItem::count())->toBe(1);
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

it('lets the creator delete their item', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    $item = ShoppingItem::create(['family_id' => $user->family_id, 'user_id' => $user->id, 'name' => 'Z', 'quantity' => 1]);

    $this->deleteJson("/api/v1/shopping-items/{$item->id}")->assertNoContent();
    expect(ShoppingItem::find($item->id))->toBeNull();
});

it('forbids a non-creator family member from deleting', function () {
    $family = Family::factory()->create();
    $creator = familyMember($family);
    $item = ShoppingItem::create(['family_id' => $family->id, 'user_id' => $creator->id, 'name' => 'X', 'quantity' => 1]);

    Sanctum::actingAs(familyMember($family));
    $this->deleteJson("/api/v1/shopping-items/{$item->id}")->assertForbidden();
});

it('returns 409 when the user has no family', function () {
    Sanctum::actingAs(User::factory()->create(['family_id' => null]));

    $this->getJson('/api/v1/shopping-items')->assertStatus(409);
});

it('exports the shopping list as pdf', function () {
    $user = familyMember();
    Sanctum::actingAs($user);
    ShoppingItem::create(['family_id' => $user->family_id, 'user_id' => $user->id, 'name' => 'Apfel', 'quantity' => 2]);

    $response = $this->get('/api/v1/shopping-items/pdf');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
