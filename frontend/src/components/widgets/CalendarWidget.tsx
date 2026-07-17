import { useEffect, useState } from 'react'
import { calendarFeedsApi, eventsApi } from '../../api'
import { APP_ICONS, Car, Globe, RotateCcw } from '../../lib/icons'
import { expandEvents } from '../../lib/recurrence'
import { useAuth } from '../../store/auth'
import type { EventItem, FeedEvent } from '../../types'
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

/** Nächste Termine – gemischt aus Familien-Terminen und Kalender-Abos (Premium). */
interface Entry {
  key: string
  when: number
  label: string
  title: string
  car?: boolean
  series?: boolean
  feed?: boolean
}

export default function CalendarWidget({ onRemove }: { onRemove?: () => void }) {
  const isPremium = useAuth((s) => s.user?.family?.is_premium ?? false)
  const [events, setEvents] = useState<EventItem[]>([])
  const [feedEvents, setFeedEvents] = useState<FeedEvent[]>([])
  // „jetzt" einmalig beim Mount festhalten (stabil über Re-Renders).
  const [now] = useState(() => Date.now())

  useEffect(() => {
    eventsApi.list().then(setEvents).catch(() => {})
  }, [])

  useEffect(() => {
    if (!isPremium) return
    calendarFeedsApi
      .events(new Date(now), new Date(now + 90 * 86_400_000))
      .then(setFeedEvents)
      .catch(() => {})
  }, [isPremium, now])

  // Ganztägige Abo-Termine sind reine Datumswerte (Ende exklusiv, RFC 5545).
  const feedStart = (e: FeedEvent): number =>
    new Date(e.all_day ? `${e.starts_at}T00:00:00` : e.starts_at).getTime()
  const feedEnd = (e: FeedEvent): number =>
    new Date(e.all_day ? `${e.ends_at}T00:00:00` : e.ends_at).getTime()

  // Serien in konkrete Vorkommen auflösen (90-Tage-Fenster reicht fürs Widget).
  const upcoming: Entry[] = [
    ...expandEvents(events, new Date(now), new Date(now + 90 * 86_400_000))
      .filter((e) => new Date(e.ends_at).getTime() >= now)
      .map((e) => ({
        key: e.occurrence_key,
        when: new Date(e.starts_at).getTime(),
        label: formatWhen(e.starts_at),
        title: e.title,
        car: e.car_reserved,
        series: !!e.recurrence,
      })),
    ...feedEvents
      .filter((e) => feedEnd(e) >= now)
      .map((e) => ({
        key: e.id,
        when: feedStart(e),
        label: e.all_day
          ? `${new Date(`${e.starts_at}T00:00:00`).toLocaleDateString('de-DE', {
              weekday: 'short',
              day: '2-digit',
              month: '2-digit',
            })}, ganztägig`
          : formatWhen(e.starts_at),
        title: e.title,
        feed: true,
      })),
  ]
    .sort((a, b) => a.when - b.when)
    .slice(0, 4)

  return (
    <WidgetCard title="Kalender" icon={APP_ICONS.calendar} to="/calendar" onRemove={onRemove}>
      {upcoming.length === 0 ? (
        <p className="text-sm text-muted">Keine anstehenden Termine.</p>
      ) : (
        <ul className="space-y-2">
          {upcoming.map((e) => (
            <li key={e.key} className="text-sm leading-tight">
              <span className="text-muted">{e.label}</span>
              <br />
              <span className="inline-flex items-center gap-1 text-text">
                {e.title}
                {e.car && <Car className="h-3.5 w-3.5 text-muted" aria-label="Auto reserviert" />}
                {e.series && <RotateCcw className="h-3 w-3 text-muted" aria-label="Serientermin" />}
                {e.feed && <Globe className="h-3 w-3 text-muted" aria-label="Aus Kalender-Abo" />}
              </span>
            </li>
          ))}
        </ul>
      )}
    </WidgetCard>
  )
}
