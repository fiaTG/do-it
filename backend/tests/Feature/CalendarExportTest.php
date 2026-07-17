<?php

use App\Models\Event;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function exportEventFor(User $user, array $attributes = []): Event
{
    return Event::create([
        'family_id' => $user->family_id,
        'user_id' => $user->id,
        'owner_id' => $user->id,
        'title' => 'Zahnarzt',
        'starts_at' => now()->addDay()->setTime(10, 0),
        'ends_at' => now()->addDay()->setTime(11, 0),
        ...$attributes,
    ]);
}

function activateExport(User $guardian): string
{
    Sanctum::actingAs($guardian);
    $url = test()->postJson('/api/v1/calendar-export/rotate')->assertOk()->json('data.url');

    return (string) parse_url($url, PHP_URL_PATH);
}

it('lets a guardian activate the share and see the url', function () {
    $guardian = premiumFamilyMember();
    Sanctum::actingAs($guardian);

    $this->getJson('/api/v1/calendar-export')
        ->assertOk()->assertJsonPath('data.enabled', false);

    $response = $this->postJson('/api/v1/calendar-export/rotate')->assertOk();
    expect($response->json('data.enabled'))->toBeTrue();
    expect($response->json('data.url'))->toContain('/api/v1/calendar-export/');
});

it('never leaks the token through user or family endpoints', function () {
    $guardian = premiumFamilyMember();
    $path = activateExport($guardian);
    $token = basename($path);

    expect($this->getJson('/api/v1/auth/me')->content())->not->toContain($token);
    expect($this->getJson('/api/v1/family/members')->content())->not->toContain($token);
});

it('blocks children and free families from managing the share', function () {
    $guardian = premiumFamilyMember();
    $child = User::factory()->create(['family_id' => $guardian->family_id, 'role' => 'child']);

    Sanctum::actingAs($child);
    $this->postJson('/api/v1/calendar-export/rotate')->assertForbidden();
    $this->deleteJson('/api/v1/calendar-export')->assertForbidden();
    // Sehen dürfen Kinder die URL (jedes Handy soll abonnieren können).
    $this->getJson('/api/v1/calendar-export')->assertOk();

    Sanctum::actingAs(familyMember());
    $this->getJson('/api/v1/calendar-export')->assertForbidden();
});

it('serves the family calendar as ics with owner names', function () {
    $guardian = premiumFamilyMember();
    exportEventFor($guardian, ['car_reserved' => true]);
    $path = activateExport($guardian);

    $response = $this->get($path)->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('text/calendar');
    $body = $response->content();
    expect($body)->toContain('BEGIN:VCALENDAR');
    expect($body)->toContain("Zahnarzt ({$guardian->first_name})");
    expect($body)->toContain('Auto ist reserviert.');
    expect($body)->toContain('X-WR-CALNAME:Nidula');
});

it('exports recurrences as rrules including biweekly and until', function () {
    $guardian = premiumFamilyMember();
    exportEventFor($guardian, [
        'title' => 'Gelber Sack',
        'recurrence' => 'biweekly',
        'recurrence_until' => '2027-06-30',
    ]);
    $path = activateExport($guardian);

    $body = $this->get($path)->assertOk()->content();

    expect($body)->toContain('RRULE:FREQ=WEEKLY;INTERVAL=2;UNTIL=20270630T');
});

it('rejects unknown tokens', function () {
    $this->get('/api/v1/calendar-export/'.str_repeat('ab', 32))->assertNotFound();
});

it('invalidates the old url when the token is rotated', function () {
    $guardian = premiumFamilyMember();
    $oldPath = activateExport($guardian);

    $this->postJson('/api/v1/calendar-export/rotate')->assertOk();

    $this->get($oldPath)->assertNotFound();
});

it('stops the feed when the share is disabled', function () {
    $guardian = premiumFamilyMember();
    $path = activateExport($guardian);

    $this->deleteJson('/api/v1/calendar-export')->assertNoContent();

    $this->get($path)->assertNotFound();
});

it('pauses the feed when premium lapses', function () {
    $guardian = premiumFamilyMember();
    $path = activateExport($guardian);

    $guardian->family->subscription->update(['expires_at' => now()->subDay()]);

    $this->get($path)->assertNotFound();
});
