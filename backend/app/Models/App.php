<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Auswählbare Dashboard-App (Galerie, Einkaufsliste, ToDo, Kalender).
 */
class App extends Model
{
    protected $fillable = ['slug', 'name', 'icon'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
