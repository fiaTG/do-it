<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarFeed extends Model
{
    protected $fillable = [
        'family_id',
        'user_id',
        'name',
        'color',
        'url',
        'ics_data',
        'last_synced_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Abo mit URL wird periodisch neu geholt; Datei-Importe nie. */
    public function isSubscription(): bool
    {
        return $this->url !== null;
    }
}
