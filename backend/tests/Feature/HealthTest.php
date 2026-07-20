<?php

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Health-Endpunkte (ADR-0027). Fehlerfälle deterministisch per Facade-Mock,
// nicht über einen unerreichbaren Redis-Port (stabil + schnell in CI).

it('reports liveness', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonPath('status', 'ok');
});

it('reports readiness when db and cache are up', function () {
    $this->getJson('/api/v1/health/ready')
        ->assertOk()
        ->assertJsonPath('status', 'ready');
});

it('reports degraded when the database is down', function () {
    DB::shouldReceive('select')->andThrow(new RuntimeException('db down'));

    $this->getJson('/api/v1/health/ready')
        ->assertStatus(503)
        ->assertJsonPath('status', 'degraded');
});

it('reports degraded when the cache is down', function () {
    // Ohne Throttle-Middleware: die nutzt selbst den Cache, sonst käme 500
    // statt des erwarteten 503 aus dem Readiness-Check.
    $this->withoutMiddleware(ThrottleRequests::class);
    Cache::shouldReceive('store')->andThrow(new RuntimeException('redis down'));

    $this->getJson('/api/v1/health/ready')
        ->assertStatus(503)
        ->assertJsonPath('status', 'degraded');
});
