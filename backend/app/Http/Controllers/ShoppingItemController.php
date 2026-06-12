<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\ShoppingItemResource;
use App\Models\ShoppingItem;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /**
     * Offene Einkaufsliste als PDF (nach Shop gruppiert) – ersetzt den
     * früheren FPDF-Export.
     */
    public function pdf(Request $request): Response
    {
        $familyId = $this->familyId($request);

        $items = ShoppingItem::where('family_id', $familyId)
            ->where('is_purchased', false)
            ->with('shop')
            ->orderBy('shop_id')
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('pdf.shopping-list', [
            'items' => $items,
            'family' => $request->user()->family,
        ]);

        return $pdf->download('einkaufsliste.pdf');
    }

    public function store(Request $request): JsonResponse
    {
        $familyId = $this->familyId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
        ]);

        $quantity = $data['quantity'] ?? 1;
        $shopId = $data['shop_id'] ?? null;

        // Mengen-Merge (wie im Original): existiert derselbe, noch nicht gekaufte
        // Artikel im selben Shop bereits, wird nur die Menge erhöht.
        $existing = ShoppingItem::where('family_id', $familyId)
            ->where('is_purchased', false)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])
            ->when($shopId === null, fn ($q) => $q->whereNull('shop_id'))
            ->when($shopId !== null, fn ($q) => $q->where('shop_id', $shopId))
            ->first();

        if ($existing) {
            $existing->increment('quantity', $quantity);

            return (new ShoppingItemResource($existing->fresh()->load('shop')))
                ->response()->setStatusCode(200);
        }

        $item = ShoppingItem::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'quantity' => $quantity,
            'shop_id' => $shopId,
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
