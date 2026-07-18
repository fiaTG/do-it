<?php

namespace App\Http\Resources;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Todo
 */
class TodoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_done' => $this->is_done,
            'created_by' => $this->user_id,
            // Nest-Blätter (ADR-0026): wer hat's erledigt (Avatar in der Liste).
            'completed_by' => $this->completed_by,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
