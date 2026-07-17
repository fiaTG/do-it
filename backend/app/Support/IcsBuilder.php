<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Family;
use DateTimeZone;
use Sabre\VObject\Component\VCalendar;

/**
 * Baut den iCal-Export des Familienkalenders (ADR-0024, Kalender-Freigabe).
 * Serien werden als echte RRULEs exportiert – Google/Apple/Outlook expandieren
 * selbst. Zeiten in UTC (Z-Suffix), die Kalender-App des Abonnenten zeigt lokal.
 */
class IcsBuilder
{
    /** Unser Recurrence-Modell (bewusst simpel) → RFC-5545-RRULE. */
    private const RRULE_FREQ = [
        'daily' => 'FREQ=DAILY',
        'weekly' => 'FREQ=WEEKLY',
        'biweekly' => 'FREQ=WEEKLY;INTERVAL=2',
        'monthly' => 'FREQ=MONTHLY',
        'yearly' => 'FREQ=YEARLY',
    ];

    public function build(Family $family): string
    {
        $calendar = new VCalendar;
        $calendar->PRODID = '-//Nidula//Familienkalender//DE';
        // Anzeigename + Aktualisierungs-Hinweis (nicht jede App wertet ihn aus).
        $calendar->add('X-WR-CALNAME', 'Nidula – '.$family->name);
        $calendar->add('X-PUBLISHED-TTL', 'PT6H');
        $calendar->add('REFRESH-INTERVAL', 'PT6H', ['VALUE' => 'DURATION']);

        $utc = new DateTimeZone('UTC');
        foreach ($family->events()->with('owner')->orderBy('starts_at')->get() as $event) {
            $calendar->add('VEVENT', $this->vevent($event, $utc));
        }

        return $calendar->serialize();
    }

    /**
     * @return array<string, mixed>
     */
    private function vevent(Event $event, DateTimeZone $utc): array
    {
        // Owner in den Titel: im fremden Kalender fehlt sonst das WER.
        $summary = $event->owner
            ? "{$event->title} ({$event->owner->first_name})"
            : $event->title;

        $props = [
            'UID' => "event-{$event->id}@nidula.app",
            'SUMMARY' => $summary,
            'DTSTART' => $event->starts_at->copy()->setTimezone($utc),
            'DTEND' => $event->ends_at->copy()->setTimezone($utc),
        ];

        if ($event->car_reserved) {
            $props['DESCRIPTION'] = 'Auto ist reserviert.';
        }

        $rrule = self::RRULE_FREQ[$event->recurrence] ?? null;
        if ($rrule !== null) {
            if ($event->recurrence_until !== null) {
                // UNTIL ist inklusiv: Ende des Tages, als UTC-Zeitstempel.
                $rrule .= ';UNTIL='.$event->recurrence_until
                    ->copy()->endOfDay()->setTimezone($utc)->format('Ymd\THis\Z');
            }
            $props['RRULE'] = $rrule;
        }

        return $props;
    }
}
