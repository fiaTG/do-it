<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'category' => $this->category,
            'car_reserved' => $this->car_reserved,
            'created_by' => $this->user_id,
            // Mitglied, für das der Termin ist (Farbe/Zuordnung im Familienkalender).
            'recurrence' => $this->recurrence,
            'recurrence_until' => $this->recurrence_until?->toDateString(),
            'owner_id' => $this->owner_id,
            'owner_name' => $this->owner?->first_name,
        ];
    }
}
