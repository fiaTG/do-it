<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Nest-Blätter (ADR-0026): ein Eintrag je erledigtem ToDo. Getrennt vom Todo,
 * damit Punkte das Löschen erledigter Aufgaben überleben (todo_id -> null).
 */
class TodoPoint extends Model
{
    protected $fillable = ['family_id', 'user_id', 'todo_id', 'points'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
