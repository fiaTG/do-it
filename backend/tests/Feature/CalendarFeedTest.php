<?php

use App\Models\CalendarFeed;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

// Öffentliche IP-Literale statt Hostnamen: der SSRF-Guard im IcsFetcher prüft
// IPs ohne DNS-Auflösung, Http::fake fängt den eigentlichen Abruf ab.
const FEED_URL = 'https://93.184.216.34/schule.ics';

function schulkalenderIcs(): string
{
    return (string) file_get_contents(base_path('tests/Fixtures/schulkalender.ics'));
}

function fakeFeedServer(?string $body = null): void
{
    Http::fake(['93.184.216.34/*' => Http::response($body ?? schulkalenderIcs(), 200)]);
}

function calendarFeedFor(User $user, array $attributes = []): CalendarFeed
{
    return CalendarFeed::create([
        'family_id' => $user->family_id,
        'user_id' => $user->id,
        'name' => 'Schule',
        'color' => '#7BA05B',
        'url' => FEED_URL,
        'ics_data' => schulkalenderIcs(),
        'last_synced_at' => now(),
        ...$attributes,
    ]);
}

it('lets a guardian subscribe to an ics url', function () {
    fakeFeedServer();
    Sanctum::actingAs(premiumFamilyMember());

    $response = $this->postJson('/api/v1/calendar-feeds', [
        'name' => 'Schule',
        'color' => '#7BA05B',
        'url' => FEED_URL,
    ])->assertCreated();

    expect($response->json('data.is_subscription'))->toBeTrue();
    expect(CalendarFeed::first()->ics_data)->toContain('BEGIN:VCALENDAR');
});

it('lets a guardian import an ics file once', function () {
    Sanctum::actingAs(premiumFamilyMember());

    $file = UploadedFile::fake()->createWithContent('abfall.ics', schulkalenderIcs());
    $response = $this->post('/api/v1/calendar-feeds', [
        'name' => 'Abfallkalender',
        'color' => '#C46A4A',
        'file' => $file,
    ], ['Accept' => 'application/json'])->assertCreated();

    expect($response->json('data.is_subscription'))->toBeFalse();
    expect($response->json('data.url'))->toBeNull();
});

it('blocks free families via the premium middleware', function () {
    Sanctum::actingAs(familyMember());

    $this->getJson('/api/v1/calendar-feeds')->assertForbidden();
    $this->getJson('/api/v1/calendar-feeds/events?from=2026-09-01&to=2026-10-01')->assertForbidden();
});

it('blocks children from managing feeds', function () {
    $guardian = premiumFamilyMember();
    $child = User::factory()->create(['family_id' => $guardian->family_id, 'role' => 'child']);
    $feed = calendarFeedFor($guardian);
    Sanctum::actingAs($child);

    $this->postJson('/api/v1/calendar-feeds', [
        'name' => 'X', 'color' => '#7BA05B', 'url' => FEED_URL,
    ])->assertForbidden();
    $this->deleteJson("/api/v1/calendar-feeds/{$feed->id}")->assertForbidden();
});

it('rejects urls that point into private networks', function () {
    Http::fake();
    Sanctum::actingAs(premiumFamilyMember());

    $this->postJson('/api/v1/calendar-feeds', [
        'name' => 'Böse', 'color' => '#7BA05B', 'url' => 'http://127.0.0.1/cal.ics',
    ])->assertStatus(422)->assertJsonValidationErrorFor('url');
    Http::assertNothingSent();
});

it('rejects urls that do not serve an ics calendar', function () {
    fakeFeedServer('<html>Kein Kalender</html>');
    Sanctum::actingAs(premiumFamilyMember());

    $this->postJson('/api/v1/calendar-feeds', [
        'name' => 'Schule', 'color' => '#7BA05B', 'url' => FEED_URL,
    ])->assertStatus(422)->assertJsonValidationErrorFor('url');
});

it('limits a family to five feeds', function () {
    fakeFeedServer();
    $guardian = premiumFamilyMember();
    foreach (range(1, 5) as $i) {
        calendarFeedFor($guardian, ['name' => "Feed {$i}"]);
    }
    Sanctum::actingAs($guardian);

    $this->postJson('/api/v1/calendar-feeds', [
        'name' => 'Zu viel', 'color' => '#7BA05B', 'url' => FEED_URL,
    ])->assertStatus(422);
});

it('expands recurring feed events inside the requested window', function () {
    $guardian = premiumFamilyMember();
    calendarFeedFor($guardian);
    Sanctum::actingAs($guardian);

    $response = $this->getJson('/api/v1/calendar-feeds/events?from=2026-09-01&to=2026-10-01')
        ->assertOk();

    // Montage im September 2026: 7., 14., 21., 28. – der 14. ist EXDATE.
    $events = $response->json('data.events');
    expect($events)->toHaveCount(3);
    expect($events[0]['title'])->toBe('Schule');
    // 08:00 Europe/Berlin (Sommerzeit) = 06:00 UTC – sabre liefert UTC.
    expect($events[0]['starts_at'])->toBe('2026-09-07T06:00:00+00:00');
    expect($events[0]['all_day'])->toBeFalse();
    expect($events[0]['location'])->toBe('Grundschule Musterstadt');
});

it('returns all-day events as plain dates with exclusive end', function () {
    $guardian = premiumFamilyMember();
    calendarFeedFor($guardian);
    Sanctum::actingAs($guardian);

    // 23.–26.12. enthält keinen Schul-Montag – nur den ganztägigen Basar.
    $events = $this->getJson('/api/v1/calendar-feeds/events?from=2026-12-23&to=2026-12-26')
        ->assertOk()->json('data.events');

    expect($events)->toHaveCount(1);
    expect($events[0]['title'])->toBe('Weihnachtsbasar');
    expect($events[0]['all_day'])->toBeTrue();
    expect($events[0]['starts_at'])->toBe('2026-12-24');
    expect($events[0]['ends_at'])->toBe('2026-12-25');
});

it('refreshes stale subscriptions on read but keeps fresh ones cached', function () {
    fakeFeedServer();
    $guardian = premiumFamilyMember();
    $stale = calendarFeedFor($guardian, ['last_synced_at' => now()->subHours(7)]);
    Sanctum::actingAs($guardian);

    $this->getJson('/api/v1/calendar-feeds/events?from=2026-09-01&to=2026-10-01')->assertOk();
    Http::assertSentCount(1);
    expect($stale->fresh()->last_synced_at->diffInMinutes(now()))->toBeLessThan(2);

    // Zweiter Abruf: Feed ist jetzt frisch -> kein weiterer Upstream-Call.
    $this->getJson('/api/v1/calendar-feeds/events?from=2026-09-01&to=2026-10-01')->assertOk();
    Http::assertSentCount(1);
});

it('keeps old events and records the error when a refresh fails', function () {
    Http::fake(['93.184.216.34/*' => Http::response('Server kaputt', 500)]);
    $guardian = premiumFamilyMember();
    $feed = calendarFeedFor($guardian, ['last_synced_at' => now()->subHours(7)]);
    Sanctum::actingAs($guardian);

    $events = $this->getJson('/api/v1/calendar-feeds/events?from=2026-09-01&to=2026-10-01')
        ->assertOk()->json('data.events');

    expect($events)->toHaveCount(3); // alte Daten bleiben nutzbar
    expect($feed->fresh()->last_error)->not->toBeNull();
});

it('supports a manual refresh for subscriptions only', function () {
    fakeFeedServer();
    $guardian = premiumFamilyMember();
    $subscription = calendarFeedFor($guardian);
    $import = calendarFeedFor($guardian, ['name' => 'Abfall', 'url' => null]);
    Sanctum::actingAs($guardian);

    $this->postJson("/api/v1/calendar-feeds/{$subscription->id}/refresh")->assertOk();
    $this->postJson("/api/v1/calendar-feeds/{$import->id}/refresh")->assertStatus(422);
});

it('prevents guardians from touching feeds of other families', function () {
    $guardian = premiumFamilyMember();
    $feed = calendarFeedFor($guardian);
    Sanctum::actingAs(premiumFamilyMember());

    $this->deleteJson("/api/v1/calendar-feeds/{$feed->id}")->assertForbidden();
    $this->postJson("/api/v1/calendar-feeds/{$feed->id}/refresh")->assertForbidden();
});

it('deletes a feed together with its events layer', function () {
    $guardian = premiumFamilyMember();
    $feed = calendarFeedFor($guardian);
    Sanctum::actingAs($guardian);

    $this->deleteJson("/api/v1/calendar-feeds/{$feed->id}")->assertNoContent();
    expect(CalendarFeed::count())->toBe(0);
});

it('rejects oversized expansion windows', function () {
    $guardian = premiumFamilyMember();
    calendarFeedFor($guardian);
    Sanctum::actingAs($guardian);

    $this->getJson('/api/v1/calendar-feeds/events?from=2020-01-01&to=2027-01-01')
        ->assertStatus(422);
});
