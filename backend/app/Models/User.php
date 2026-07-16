<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /**
     * In-Memory-Default analog zum DB-Default: frisch instanziierte Models
     * (Factory/create ohne refresh) haben sonst role=null – die frühere
     * fail-open-Prüfung hatte genau das verdeckt (Review N-01).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'guardian',
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'family_id',
        'role',
        'color',
        'avatar_path',
        'birthdate',
        'gender',
        'facebook',
        'instagram',
        'linkedin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birthdate' => 'date',
            'password' => 'hashed',
        ];
    }

    /** Verwalter (Eltern) dürfen alle Familientermine verwalten, Kinder nur eigene. */
    public function isGuardian(): bool
    {
        // Fail closed (Review N-01): nur exakt 'guardian' hat Verwalterrechte –
        // unbekannte/beschädigte Werte führen zu weniger, nicht mehr Rechten.
        return $this->role === 'guardian';
    }

    public function isChild(): bool
    {
        return $this->role === 'child';
    }

    // --- Beziehungen --------------------------------------------------------

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class)->withTimestamps();
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
}
