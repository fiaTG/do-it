<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\ShoppingItemResource;
use App\Models\ShoppingItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ShoppingItemController extends Controller
{
    use InteractsWithFamily;

    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return ShoppingItemResource::collection(
            ShoppingItem::where('family_id', $familyId)->with('shop')->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $familyId = $this->familyId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
        ]);

        $item = ShoppingItem::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'quantity' => $data['quantity'] ?? 1,
            'shop_id' => $data['shop_id'] ?? null,
            'is_purchased' => false,
        ]);

        return (new ShoppingItemResource($item->load('shop')))
            ->response()->setStatusCode(201);
    }

    public function update(Request $request, ShoppingItem $shoppingItem): ShoppingItemResource
    {
        $this->authorize('update', $shoppingItem);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'shop_id' => ['sometimes', 'nullable', 'integer', 'exists:shops,id'],
            'is_purchased' => ['sometimes', 'boolean'],
        ]);

        $shoppingItem->update($data);

        return new ShoppingItemResource($shoppingItem->load('shop'));
    }

    public function destroy(Request $request, ShoppingItem $shoppingItem): Response
    {
        $this->authorize('delete', $shoppingItem);

        $shoppingItem->delete();

        return response()->noContent();
    }
}
