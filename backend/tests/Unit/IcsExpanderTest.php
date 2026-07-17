<?php

use App\Support\IcsExpander;
use App\Support\IcsParseException;

function expander(): IcsExpander
{
    return new IcsExpander;
}

it('throws a german parse error for invalid input', function () {
    expect(fn () => expander()->assertParses('das ist kein kalender'))
        ->toThrow(IcsParseException::class);
});

it('derives the end from a DURATION when DTEND is missing', function () {
    $ics = implode("\r\n", [
        'BEGIN:VCALENDAR', 'VERSION:2.0',
        'BEGIN:VEVENT',
        'UID:duration@test',
        'SUMMARY:Training',
        'DTSTART:20260910T170000Z',
        'DURATION:PT1H30M',
        'END:VEVENT',
        'END:VCALENDAR', '',
    ]);

    $events = expander()->expand($ics, new DateTimeImmutable('2026-09-01'), new DateTimeImmutable('2026-10-01'));

    expect($events)->toHaveCount(1);
    expect($events[0]['ends_at'])->toBe('2026-09-10T18:30:00+00:00');
});

it('falls back to a sensible title for nameless events', function () {
    $ics = implode("\r\n", [
        'BEGIN:VCALENDAR', 'VERSION:2.0',
        'BEGIN:VEVENT',
        'UID:leer@test',
        'DTSTART:20260910T170000Z',
        'END:VEVENT',
        'END:VCALENDAR', '',
    ]);

    $events = expander()->expand($ics, new DateTimeImmutable('2026-09-01'), new DateTimeImmutable('2026-10-01'));

    expect($events[0]['title'])->toBe('Ohne Titel');
});
