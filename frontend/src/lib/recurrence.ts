import type { EventItem } from '../types'

/**
 * Expandiert Serientermine (recurrence auf dem Event) in konkrete Vorkommen
 * für ein Zeitfenster. Der Server speichert nur die Serie; Kalender,
 * Personen-Tagesansicht und "nächster Termin" arbeiten mit den Vorkommen.
 *
 * Bewusst einfache Regeln (täglich/wöchentlich/monatlich/jährlich, optionales
 * Enddatum) statt vollem RRULE – deckt Mülltonnen & TÜV ab (Produkt-Backlog).
 */

export interface Occurrence extends EventItem {
  /** Eindeutig je Vorkommen (Event-ID + Index) – z. B. als React/FC-Key. */
  occurrence_key: string
}

// Sicherheitsdeckel gegen Endlos-Serien (täglich über ~3 Jahre passt hinein).
const MAX_OCCURRENCES = 1200

function daysInMonth(year: number, month: number): number {
  return new Date(year, month + 1, 0).getDate()
}

/** n-tes Vorkommen ab Serienstart; Monatsende wird geklemmt (31. → 30./28.). */
function nthOccurrenceStart(start: Date, recurrence: string, n: number): Date {
  const result = new Date(start)
  if (recurrence === 'daily') {
    result.setDate(result.getDate() + n)
  } else if (recurrence === 'weekly') {
    result.setDate(result.getDate() + n * 7)
  } else if (recurrence === 'biweekly') {
    result.setDate(result.getDate() + n * 14)
  } else if (recurrence === 'monthly') {
    const months = start.getMonth() + n
    const year = start.getFullYear() + Math.floor(months / 12)
    const month = ((months % 12) + 12) % 12
    result.setFullYear(year, month, Math.min(start.getDate(), daysInMonth(year, month)))
  } else {
    const year = start.getFullYear() + n
    result.setFullYear(
      year,
      start.getMonth(),
      Math.min(start.getDate(), daysInMonth(year, start.getMonth())),
    )
  }
  return result
}

export function expandEvents(events: EventItem[], from: Date, to: Date): Occurrence[] {
  const out: Occurrence[] = []

  for (const event of events) {
    if (!event.recurrence) {
      out.push({ ...event, occurrence_key: String(event.id) })
      continue
    }

    const seriesStart = new Date(event.starts_at)
    const duration = +new Date(event.ends_at) - +seriesStart
    // recurrence_until ist ein Datum – das Vorkommen an diesem Tag zählt noch mit.
    const untilMs = event.recurrence_until
      ? +new Date(`${event.recurrence_until}T23:59:59`)
      : Number.POSITIVE_INFINITY
    const limitMs = Math.min(untilMs, +to)

    for (let n = 0; n < MAX_OCCURRENCES; n++) {
      const occStart = nthOccurrenceStart(seriesStart, event.recurrence, n)
      if (+occStart > limitMs) break
      if (+occStart + duration < +from) continue
      out.push({
        ...event,
        starts_at: occStart.toISOString(),
        ends_at: new Date(+occStart + duration).toISOString(),
        occurrence_key: `${event.id}:${n}`,
      })
    }
  }

  return out
}
