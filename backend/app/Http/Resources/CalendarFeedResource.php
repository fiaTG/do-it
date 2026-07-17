<?php

namespace App\Http\Resources;

use App\Models\CalendarFeed;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CalendarFeed
 */
class CalendarFeedResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'url' => $this->url,
            'is_subscription' => $this->isSubscription(),
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'last_error' => $this->last_error,
            'created_by' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
