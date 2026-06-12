<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ShoppingItem
 */
class ShoppingItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'is_purchased' => $this->is_purchased,
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'created_by' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
