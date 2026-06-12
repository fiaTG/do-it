<?php

use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

/**
 * Erzeugt einen Nutzer, der einer Familie angehört (eigene oder übergebene).
 */
function familyMember(?Family $family = null): User
{
    $family ??= Family::factory()->create();

    return User::factory()->create(['family_id' => $family->id]);
}
