<?php

use App\Models\Family;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

/**
 * Erzeugt einen Nutzer, der einer Familie angehört (eigene oder übergebene).
 */
function familyMember(?Family $family = null): User
{
    $family ??= Family::factory()->create();

    return User::factory()->create(['family_id' => $family->id]);
}

/**
 * Erzeugt einen Nutzer in einer Familie mit aktivem Premium-Abo
 * (für Tests der premium-Middleware-Endpunkte).
 */
function premiumFamilyMember(bool $withLocation = true): User
{
    $family = Family::factory()->create($withLocation ? [
        'location_name' => 'Heidelberg',
        'latitude' => 49.40768,
        'longitude' => 8.69079,
    ] : []);
    Subscription::create([
        'family_id' => $family->id,
        'plan' => 'monthly',
        'status' => 'active',
        'provider' => 'manual',
        'expires_at' => now()->addMonth(),
    ]);

    return User::factory()->create(['family_id' => $family->id]);
}
