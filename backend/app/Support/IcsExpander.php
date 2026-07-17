<?php

namespace App\Support;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

/**
 * Expandiert einen iCal-Rohtext in einzelne Termin-Vorkommen innerhalb eines
 * Zeitfensters. RRULE-Auflösung (inkl. EXDATE/RECURRENCE-ID/Zeitzonen)
 * übernimmt sabre/vobject – das selbst zu bauen wäre fahrlässig. Zeiten kommen
 * von expand() bereits in UTC, Ganztagstermine bleiben reine Datumswerte.
 */
class IcsExpander
{
    /** Obergrenze je Feed – schützt vor pathologischen Kalendern. */
    public const MAX_EVENTS = 2000;

    /** Wirft IcsParseException, wenn der Text kein gültiger Kalender ist. */
    public function assertParses(string $ics): void
    {
        $this->read($ics);
    }

    /**
     * @return list<array<string, mixed>> chronologisch sortierte Vorkommen
     */
    public function expand(string $ics, DateTimeInterface $from, DateTimeInterface $to): array
    {
        $calendar = $this->read($ics);

        try {
            $expanded = $calendar->expand(
                DateTimeImmutable::createFromInterface($from),
                DateTimeImmutable::createFromInterface($to),
            );
        } catch (\Throwable) {
            throw new IcsParseException('Der Kalender enthält eine fehlerhafte Wiederholungsregel.');
        }

        $events = [];
        foreach ($expanded->select('VEVENT') as $vevent) {
            if (count($events) >= self::MAX_EVENTS) {
                break;
            }
            if (! isset($vevent->DTSTART)) {
                continue;
            }

            $allDay = ! $vevent->DTSTART->hasTime();
            $start = $vevent->DTSTART->getDateTime();
            $end = $this->endOf($vevent, $start, $allDay);

            $events[] = [
                'uid' => (string) ($vevent->UID ?? md5($vevent->serialize())),
                'title' => trim((string) ($vevent->SUMMARY ?? '')) !== ''
                    ? trim((string) $vevent->SUMMARY)
                    : 'Ohne Titel',
                // Ganztägig als reines Datum (Ende exklusiv, wie RFC 5545 und
                // FullCalendar es gleichermaßen definieren).
                'starts_at' => $allDay ? $start->format('Y-m-d') : $start->format(DateTimeInterface::ATOM),
                'ends_at' => $allDay ? $end->format('Y-m-d') : $end->format(DateTimeInterface::ATOM),
                'all_day' => $allDay,
                'location' => trim((string) ($vevent->LOCATION ?? '')) !== ''
                    ? trim((string) $vevent->LOCATION)
                    : null,
            ];
        }

        usort($events, fn (array $a, array $b) => $a['starts_at'] <=> $b['starts_at']);

        return $events;
    }

    private function read(string $ics): VCalendar
    {
        try {
            $calendar = Reader::read($ics, Reader::OPTION_FORGIVING);
        } catch (\Throwable) {
            throw new IcsParseException('Das ist kein gültiger iCal-Kalender (.ics).');
        }

        throw_unless($calendar instanceof VCalendar, new IcsParseException(
            'Das ist kein gültiger iCal-Kalender (.ics).',
        ));

        return $calendar;
    }

    private function endOf(object $vevent, DateTimeInterface $start, bool $allDay): DateTimeInterface
    {
        if (isset($vevent->DTEND)) {
            return $vevent->DTEND->getDateTime();
        }
        if (isset($vevent->DURATION)) {
            return DateTimeImmutable::createFromInterface($start)->add($vevent->DURATION->getDateInterval());
        }

        // RFC 5545: ohne DTEND/DURATION dauert ein Ganztagstermin genau einen Tag,
        // ein Termin mit Uhrzeit hat keine Dauer.
        return $allDay
            ? DateTimeImmutable::createFromInterface($start)->add(new DateInterval('P1D'))
            : $start;
    }
}
