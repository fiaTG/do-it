import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { eventsApi, imagesApi, shoppingApi, todosApi } from '../api'

type Metric = {
  slug: string
  icon: string
  label: string
  to: string
  load: () => Promise<number>
}

// Kennzahl je Feature-App. Reihenfolge hier = Anzeigereihenfolge.
const METRICS: Metric[] = [
  {
    slug: 'todo',
    icon: '✅',
    label: 'offene ToDos',
    to: '/todos',
    load: async () => (await todosApi.list()).filter((t) => !t.is_done).length,
  },
  {
    slug: 'shopping-list',
    icon: '🛒',
    label: 'offene Artikel',
    to: '/shopping',
    load: async () => (await shoppingApi.list()).filter((i) => !i.is_purchased).length,
  },
  {
    slug: 'calendar',
    icon: '📅',
    label: 'anstehende Termine',
    to: '/calendar',
    load: async () => {
      const now = Date.now()
      return (await eventsApi.list()).filter((e) => new Date(e.ends_at).getTime() >= now).length
    },
  },
  {
    slug: 'gallery',
    icon: '🖼️',
    label: 'Bilder',
    to: '/gallery',
    load: async () => (await imagesApi.list()).length,
  },
]

type Stat = { icon: string; label: string; to: string; value: number }

/** Übersichts-Kacheln mit Kennzahlen der aktivierten Apps. */
export default function DashboardStats({ slugs }: { slugs: string[] }) {
  const navigate = useNavigate()
  const [stats, setStats] = useState<Stat[]>([])
  const key = slugs.join(',')

  useEffect(() => {
    let cancelled = false
    const active = METRICS.filter((m) => key.split(',').includes(m.slug))

    void Promise.all(active.map((m) => m.load().catch(() => null))).then((values) => {
      if (cancelled) return
      setStats(
        active
          .map((m, i) => ({ icon: m.icon, label: m.label, to: m.to, value: values[i] }))
          .filter((s): s is Stat => s.value !== null),
      )
    })

    return () => {
      cancelled = true
    }
  }, [key])

  if (stats.length === 0) return null

  return (
    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      {stats.map((s) => (
        <button
          key={s.to}
          onClick={() => navigate(s.to)}
          className="flex items-center gap-3 rounded-2xl bg-surface p-4 text-left shadow-card transition hover:shadow-pop"
        >
          <span className="text-2xl" aria-hidden>
            {s.icon}
          </span>
          <span>
            <span className="block text-2xl font-bold leading-none text-text">{s.value}</span>
            <span className="mt-1 block text-xs text-muted">{s.label}</span>
          </span>
        </button>
      ))}
    </div>
  )
}
