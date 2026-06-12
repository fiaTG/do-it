import { useEffect, useState, type FormEvent } from 'react'
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import type { DateSelectArg, EventClickArg, EventInput } from '@fullcalendar/core'
import deLocale from '@fullcalendar/core/locales/de'
import { apiError, eventsApi } from '../api'
import type { EventItem } from '../types'

const CATEGORIES = ['Familie', 'Freizeit', 'Arbeit', 'Sonstiges'] as const

const COLORS: Record<string, string> = {
  Familie: '#e07a5f',
  Freizeit: '#3b82f6',
  Arbeit: '#406f8f',
  Sonstiges: '#968d86',
}

interface NewEvent {
  open: boolean
  start: string
  end: string
  title: string
  category: string
  carReserved: boolean
}

const EMPTY: NewEvent = {
  open: false,
  start: '',
  end: '',
  title: '',
  category: 'Familie',
  carReserved: false,
}

export default function CalendarPage() {
  const [events, setEvents] = useState<EventItem[]>([])
  const [form, setForm] = useState<NewEvent>(EMPTY)
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

  const fcEvents: EventInput[] = events.map((e) => ({
    id: String(e.id),
    title: e.car_reserved ? `${e.title} 🚗` : e.title,
    start: e.starts_at,
    end: e.ends_at,
    backgroundColor: COLORS[e.category] ?? COLORS.Sonstiges,
    borderColor: COLORS[e.category] ?? COLORS.Sonstiges,
  }))

  function handleSelect(arg: DateSelectArg) {
    setForm({ ...EMPTY, open: true, start: arg.startStr, end: arg.endStr })
  }

  async function handleEventClick(arg: EventClickArg) {
    if (window.confirm(`Termin „${arg.event.title}" löschen?`)) {
      await eventsApi.remove(Number(arg.event.id))
      await load()
    }
  }

  async function submit(e: FormEvent) {
    e.preventDefault()
    setError('')
    try {
      await eventsApi.create({
        title: form.title,
        starts_at: form.start,
        ends_at: form.end,
        category: form.category,
        car_reserved: form.carReserved,
      })
      setForm(EMPTY)
      await load()
    } catch (err) {
      setError(apiError(err))
    }
  }

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold text-brand">📅 Kalender</h1>
      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="rounded-2xl bg-white p-4 shadow">
        <FullCalendar
          plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
          initialView="dayGridMonth"
          headerToolbar={{
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
          }}
          locale={deLocale}
          height="auto"
          selectable
          select={handleSelect}
          events={fcEvents}
          eventClick={handleEventClick}
        />
      </div>

      <div className="flex flex-wrap gap-4 text-xs text-slate-500">
        {CATEGORIES.map((c) => (
          <span key={c} className="flex items-center gap-1">
            <span className="inline-block h-3 w-3 rounded" style={{ background: COLORS[c] }} />
            {c}
          </span>
        ))}
        <span>· Bereich im Kalender ziehen, um einen Termin anzulegen.</span>
      </div>

      {form.open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <form onSubmit={submit} className="w-full max-w-sm space-y-3 rounded-2xl bg-white p-6 shadow-xl">
            <h2 className="text-lg font-semibold text-brand">Neuer Termin</h2>
            <input
              autoFocus
              placeholder="Titel"
              required
              value={form.title}
              onChange={(e) => setForm({ ...form, title: e.target.value })}
              className="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
            />
            <select
              value={form.category}
              onChange={(e) => setForm({ ...form, category: e.target.value })}
              className="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
            >
              {CATEGORIES.map((c) => (
                <option key={c}>{c}</option>
              ))}
            </select>
            <label className="flex items-center gap-2 text-sm text-slate-600">
              <input
                type="checkbox"
                checked={form.carReserved}
                onChange={(e) => setForm({ ...form, carReserved: e.target.checked })}
                className="h-5 w-5 accent-brand"
              />
              🚗 Auto reservieren
            </label>
            <div className="flex justify-end gap-2 pt-2">
              <button
                type="button"
                onClick={() => setForm(EMPTY)}
                className="rounded-lg px-4 py-2 text-sm text-slate-500 hover:bg-slate-100"
              >
                Abbrechen
              </button>
              <button className="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                Anlegen
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  )
}
