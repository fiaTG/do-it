<?php

use App\Support\IcsFetcher;
use App\Support\IcsFetchException;
use Illuminate\Support\Facades\Http;

const ICS_BODY = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nEND:VCALENDAR\r\n";

function fetcher(): IcsFetcher
{
    return new IcsFetcher;
}

it('blocks private and reserved target ips (ssrf)', function (string $url) {
    Http::fake();

    expect(fn () => fetcher()->fetch($url))->toThrow(IcsFetchException::class);
    Http::assertNothingSent();
})->with([
    'http://127.0.0.1/cal.ics',
    'http://10.1.2.3/cal.ics',
    'http://172.16.0.9/cal.ics',
    'http://192.168.1.1/cal.ics',
    'http://169.254.169.254/latest/meta-data', // Cloud-Metadata-Endpoint
    'http://[::1]/cal.ics',
]);

it('rejects unsupported schemes and ports', function (string $url) {
    Http::fake();

    expect(fn () => fetcher()->fetch($url))->toThrow(IcsFetchException::class);
    Http::assertNothingSent();
})->with([
    'ftp://93.184.216.34/cal.ics',
    'file:///etc/passwd',
    'http://93.184.216.34:8080/cal.ics',
    'kein url',
]);

it('normalizes webcal:// to https://', function () {
    Http::fake(['93.184.216.34/*' => Http::response(ICS_BODY)]);

    expect(fetcher()->fetch('webcal://93.184.216.34/cal.ics'))->toBe(ICS_BODY);
    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://'));
});

it('follows redirects but re-checks every hop', function () {
    Http::fake([
        'https://93.184.216.34/alt.ics' => Http::response('', 302, ['Location' => 'https://93.184.216.35/neu.ics']),
        'https://93.184.216.35/neu.ics' => Http::response(ICS_BODY),
    ]);

    expect(fetcher()->fetch('https://93.184.216.34/alt.ics'))->toBe(ICS_BODY);
});

it('blocks redirects into private networks', function () {
    Http::fake([
        'https://93.184.216.34/*' => Http::response('', 302, ['Location' => 'http://127.0.0.1/intern.ics']),
    ]);

    expect(fn () => fetcher()->fetch('https://93.184.216.34/alt.ics'))
        ->toThrow(IcsFetchException::class);
});

it('rejects bodies that are not an ics calendar', function () {
    Http::fake(['93.184.216.34/*' => Http::response('<html>nope</html>')]);

    expect(fn () => fetcher()->fetch('https://93.184.216.34/cal.ics'))
        ->toThrow(IcsFetchException::class);
});

it('rejects oversized calendars', function () {
    Http::fake([
        '93.184.216.34/*' => Http::response('BEGIN:VCALENDAR'.str_repeat('X', IcsFetcher::MAX_BYTES)),
    ]);

    expect(fn () => fetcher()->fetch('https://93.184.216.34/cal.ics'))
        ->toThrow(IcsFetchException::class);
});
