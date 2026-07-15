import { useEffect, useState } from 'react'
import { eventsApi } from '../../api'
import { APP_ICONS, Car, RotateCcw } from '../../lib/icons'
import { expandEvents } from '../../lib/recurrence'
import type { EventItem } from '../../types'
import WidgetCard from './WidgetCard'

function formatWhen(iso: string): string {
  return new Date(iso).toLocaleString('de-DE', {
    weekday: 'short',
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

export default function CalendarWidget({ onRemove }: { onRemove?: () => void }) {
  const [events, setEvents] = useState<EventItem[]>([])
  // „jetzt" einmalig beim Mount festhalten (stabil über Re-Renders).
  const [now] = useState(() => Date.now())

  useEffect(() => {
    eventsApi.list().then(setEvents).catch(() => {})
  }, [])

  // Serien in konkrete Vorkommen auflösen (90-Tage-Fenster reicht fürs Widget).
  const upcoming = expandEvents(events, new Date(now), new Date(now + 90 * 86_400_000))
    .filter((e) => new Date(e.ends_at).getTime() >= now)
    .sort((a, b) => new Date(a.starts_at).getTime() - new Date(b.starts_at).getTime())
    .slice(0, 4)

  return (
    <WidgetCard title="Kalender" icon={APP_ICONS.calendar} to="/calendar" onRemove={onRemove}>
      {upcoming.length === 0 ? (
        <p className="text-sm text-muted">Keine anstehenden Termine.</p>
      ) : (
        <ul className="space-y-2">
          {upcoming.map((e) => (
            <li key={e.occurrence_key} className="text-sm leading-tight">
              <span className="text-muted">{formatWhen(e.starts_at)}</span>
              <br />
              <span className="inline-flex items-center gap-1 text-text">
                {e.title}
                {e.car_reserved && <Car className="h-3.5 w-3.5 text-muted" aria-label="Auto reserviert" />}
                {e.recurrence && <RotateCcw className="h-3 w-3 text-muted" aria-label="Serientermin" />}
              </span>
            </li>
          ))}
        </ul>
      )}
    </WidgetCard>
  )
}
