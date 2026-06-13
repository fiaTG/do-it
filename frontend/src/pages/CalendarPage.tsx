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

interface ModalState {
  open: boolean
  mode: 'create' | 'edit'
  id: number | null
  title: string
  start: string
  end: string
  category: string
  carReserved: boolean
}

const CLOSED: ModalState = {
  open: false,
  mode: 'create',
  id: null,
  title: '',
  start: '',
  end: '',
  category: 'Familie',
  carReserved: false,
}

/** Date -> Wert für <input type="datetime-local"> (lokale Zeit, ohne Sekunden). */
function toLocalInput(date: Date): string {
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

export default function CalendarPage() {
  const [events, setEvents] = useState<EventItem[]>([])
  const [modal, setModal] = useState<ModalState>(CLOSED)
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

  function openCreate(arg: DateSelectArg) {
    setModal({
      ...CLOSED,
      open: true,
      mode: 'create',
      start: toLocalInput(arg.start),
      end: toLocalInput(arg.end),
    })
  }

  function openEdit(arg: EventClickArg) {
    const event = events.find((e) => e.id === Number(arg.event.id))
    if (!event) return
    setModal({
      open: true,
      mode: 'edit',
      id: event.id,
      title: event.title,
      start: toLocalInput(new Date(event.starts_at)),
      end: toLocalInput(new Date(event.ends_at)),
      category: event.category,
      carReserved: event.car_reserved,
    })
  }

  // Drag&Drop bzw. Resize -> nur die Zeiten persistieren.
  async function persistMove(arg: { event: { id: string; start: Date | null; end: Date | null } }) {
    const { id, start, end } = arg.event
    if (!start) return
    await eventsApi.update(Number(id), {
      starts_at: start.toISOString(),
      ends_at: (end ?? start).toISOString(),
    })
    await load()
  }

  async function submit(e: FormEvent) {
    e.preventDefault()
    setError('')
    const payload = {
      title: modal.title,
      starts_at: modal.start,
      ends_at: modal.end,
      category: modal.category,
      car_reserved: modal.carReserved,
    }
    try {
      if (modal.mode === 'create') {
        await eventsApi.create(payload)
      } else if (modal.id !== null) {
        await eventsApi.update(modal.id, payload)
      }
      setModal(CLOSED)
      await load()
    } catch (err) {
      setError(apiError(err))
    }
  }

  async function remove() {
    if (modal.id === null) return
    await eventsApi.remove(modal.id)
    setModal(CLOSED)
    await load()
  }

  const inputClass =
    'rounded-lg border border-border px-3 py-2 outline-none focus:border-primary'

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold text-primary">📅 Kalender</h1>
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
          editable
          select={openCreate}
          events={fcEvents}
          eventClick={openEdit}
          eventDrop={persistMove}
          eventResize={persistMove}
        />
      </div>

      <div className="flex flex-wrap gap-4 text-xs text-muted">
        {CATEGORIES.map((c) => (
          <span key={c} className="flex items-center gap-1">
            <span className="inline-block h-3 w-3 rounded" style={{ background: COLORS[c] }} />
            {c}
          </span>
        ))}
        <span>· Bereich ziehen = anlegen · Klick = bearbeiten · Ziehen = verschieben</span>
      </div>

      {modal.open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <form onSubmit={submit} className="w-full max-w-sm space-y-3 rounded-2xl bg-white p-6 shadow-xl">
            <h2 className="text-lg font-semibold text-primary">
              {modal.mode === 'create' ? 'Neuer Termin' : 'Termin bearbeiten'}
            </h2>
            <input
              autoFocus
              placeholder="Titel"
              required
              value={modal.title}
              onChange={(e) => setModal({ ...modal, title: e.target.value })}
              className={`${inputClass} w-full`}
            />
            <div className="flex gap-2">
              <label className="flex-1 text-xs text-muted">
                Von
                <input
                  type="datetime-local"
                  required
                  value={modal.start}
                  onChange={(e) => setModal({ ...modal, start: e.target.value })}
                  className={`${inputClass} mt-1 w-full`}
                />
              </label>
              <label className="flex-1 text-xs text-muted">
                Bis
                <input
                  type="datetime-local"
                  required
                  value={modal.end}
                  onChange={(e) => setModal({ ...modal, end: e.target.value })}
                  className={`${inputClass} mt-1 w-full`}
                />
              </label>
            </div>
            <select
              value={modal.category}
              onChange={(e) => setModal({ ...modal, category: e.target.value })}
              className={`${inputClass} w-full`}
            >
              {CATEGORIES.map((c) => (
                <option key={c}>{c}</option>
              ))}
            </select>
            <label className="flex items-center gap-2 text-sm text-muted">
              <input
                type="checkbox"
                checked={modal.carReserved}
                onChange={(e) => setModal({ ...modal, carReserved: e.target.checked })}
                className="h-5 w-5 accent-brand"
              />
              🚗 Auto reservieren
            </label>
            <div className="flex items-center justify-between pt-2">
              {modal.mode === 'edit' ? (
                <button
                  type="button"
                  onClick={() => void remove()}
                  className="text-sm text-red-500 hover:underline"
                >
                  Löschen
                </button>
              ) : (
                <span />
              )}
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => setModal(CLOSED)}
                  className="rounded-lg px-4 py-2 text-sm text-muted hover:bg-surface-2"
                >
                  Abbrechen
                </button>
                <button className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-hover">
                  {modal.mode === 'create' ? 'Anlegen' : 'Speichern'}
                </button>
              </div>
            </div>
          </form>
        </div>
      )}
    </div>
  )
}
