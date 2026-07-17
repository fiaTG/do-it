<?php

namespace App\Models;

use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Family extends Model
{
    /** @use HasFactory<FamilyFactory> */
    use HasFactory;

    protected $fillable = ['name', 'location_name', 'latitude', 'longitude', 'calendar_token'];

    /** Das Kalender-Token ist ein Geheimnis – nie in Resources/JSON serialisieren. */
    protected $hidden = ['calendar_token'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function shoppingItems(): HasMany
    {
        return $this->hasMany(ShoppingItem::class);
    }

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Ist diese Familie aktuell Premium? (ein aktives, nicht abgelaufenes Abo)
     */
    public function isPremium(): bool
    {
        return $this->subscription?->isActive() ?? false;
    }
}
