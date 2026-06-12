import { useEffect, useState, type FormEvent } from 'react'
import { apiError, eventsApi } from '../api'
import type { EventItem } from '../types'

const CATEGORIES = ['Sonstiges', 'Freizeit', 'Gesundheit', 'Schule', 'Arbeit']

function formatDateTime(iso: string): string {
  return new Date(iso).toLocaleString('de-DE', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

export default function CalendarPage() {
  const [events, setEvents] = useState<EventItem[]>([])
  const [title, setTitle] = useState('')
  const [startsAt, setStartsAt] = useState('')
  const [endsAt, setEndsAt] = useState('')
  const [category, setCategory] = useState('Sonstiges')
  const [carReserved, setCarReserved] = useState(false)
  const [error, setError] = useState('')

  async function load() {
    try {
      setEvents(await eventsApi.list())
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  async function add(e: FormEvent) {
    e.preventDefault()
    setError('')
    try {
      await eventsApi.create({
        title,
        starts_at: startsAt,
        ends_at: endsAt,
        category,
        car_reserved: carReserved,
      })
      setTitle('')
      setStartsAt('')
      setEndsAt('')
      setCategory('Sonstiges')
      setCarReserved(false)
      await load()
    } catch (err) {
      setError(apiError(err))
    }
  }

  async function remove(id: number) {
    await eventsApi.remove(id)
    await load()
  }

  const inputClass =
    'rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand'

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <h1 className="text-2xl font-bold text-brand">📅 Kalender</h1>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <form onSubmit={add} className="grid gap-3 rounded-2xl bg-white p-4 shadow sm:grid-cols-2">
        <input
          placeholder="Titel"
          required
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          className={`${inputClass} sm:col-span-2`}
        />
        <label className="text-sm text-slate-500">
          Von
          <input
            type="datetime-local"
            required
            value={startsAt}
            onChange={(e) => setStartsAt(e.target.value)}
            className={`${inputClass} mt-1 w-full`}
          />
        </label>
        <label className="text-sm text-slate-500">
          Bis
          <input
            type="datetime-local"
            required
            value={endsAt}
            onChange={(e) => setEndsAt(e.target.value)}
            className={`${inputClass} mt-1 w-full`}
          />
        </label>
        <select
          value={category}
          onChange={(e) => setCategory(e.target.value)}
          className={inputClass}
        >
          {CATEGORIES.map((c) => (
            <option key={c}>{c}</option>
          ))}
        </select>
        <label className="flex items-center gap-2 text-sm text-slate-600">
          <input
            type="checkbox"
            checked={carReserved}
            onChange={(e) => setCarReserved(e.target.checked)}
            className="h-5 w-5 accent-brand"
          />
          🚗 Auto reservieren
        </label>
        <button className="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-dark sm:col-span-2">
          + Termin anlegen
        </button>
      </form>

      <ul className="space-y-3">
        {events.length === 0 && <li className="text-slate-500">Keine Termine.</li>}
        {events.map((ev) => (
          <li key={ev.id} className="flex items-start justify-between rounded-2xl bg-white p-4 shadow">
            <div>
              <div className="font-semibold text-slate-700">
                {ev.title}
                {ev.car_reserved && <span className="ml-2" title="Auto reserviert">🚗</span>}
              </div>
              <div className="text-sm text-slate-500">
                {formatDateTime(ev.starts_at)} – {formatDateTime(ev.ends_at)}
              </div>
              <span className="mt-1 inline-block rounded-full bg-brand/10 px-2 py-0.5 text-xs text-brand">
                {ev.category}
              </span>
            </div>
            <button
              onClick={() => void remove(ev.id)}
              className="text-slate-300 hover:text-red-500"
              aria-label="Löschen"
            >
              🗑️
            </button>
          </li>
        ))}
      </ul>
    </div>
  )
}
