import { useEffect, useState, type FormEvent } from 'react'
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import type { DateSelectArg, EventClickArg, EventInput } from '@fullcalendar/core'
import deLocale from '@fullcalendar/core/locales/de'
import { apiError, eventsApi, familyApi } from '../api'
import { useAuth } from '../store/auth'
import type { EventItem, User } from '../types'

// Feste Farbpalette – je Familienmitglied eine Farbe (stabil über die Reihenfolge
// der Mitgliederliste). Familienkalender: Farbe = WER, nicht welcher Lebensbereich.
const MEMBER_COLORS = ['#3E7C9B', '#E58A72', '#8FCBB8', '#F4C95D', '#A9825A', '#9B6FB0', '#5BA88A', '#D08770']
const FALLBACK_COLOR = '#5b7689'

interface ModalState {
  open: boolean
  mode: 'create' | 'edit'
  readOnly: boolean
  id: number | null
  title: string
  start: string
  end: string
  ownerId: number
  carReserved: boolean
}

const CLOSED: ModalState = {
  open: false,
  mode: 'create',
  readOnly: false,
  id: null,
  title: '',
  start: '',
  end: '',
  ownerId: 0,
  carReserved: false,
}

/** Date -> Wert für <input type="datetime-local"> (lokale Zeit, ohne Sekunden). */
function toLocalInput(date: Date): string {
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

export default function CalendarPage() {
  const me = useAuth((s) => s.user)
  const userId = me?.id ?? 0
  const isGuardian = me?.role !== 'child' // Verwalter dürfen alle Termine verwalten

  const [events, setEvents] = useState<EventItem[]>([])
  const [members, setMembers] = useState<User[]>([])
  const [hidden, setHidden] = useState<number[]>([]) // ausgeblendete Personen
  const [modal, setModal] = useState<ModalState>(CLOSED)
  const [error, setError] = useState('')

  async function load() {
    try {
      const [ev, mem] = await Promise.all([eventsApi.list(), familyApi.members()])
      setEvents(ev)
      setMembers(mem)
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  // Stabile Farbe je Mitglied (nach Position in der Mitgliederliste).
  const colorFor = (ownerId: number | null): string => {
    const idx = members.findIndex((m) => m.id === ownerId)
    return idx >= 0 ? MEMBER_COLORS[idx % MEMBER_COLORS.length] : FALLBACK_COLOR
  }

  // Kinder dürfen nur eigene Termine bearbeiten.
  const canEdit = (e: EventItem): boolean => isGuardian || e.owner_id === userId

  const fcEvents: EventInput[] = events
    .filter((e) => !hidden.includes(e.owner_id ?? -1))
    .map((e) => ({
      id: String(e.id),
      title: e.car_reserved ? `${e.title} 🚗` : e.title,
      start: e.starts_at,
      end: e.ends_at,
      backgroundColor: colorFor(e.owner_id),
      borderColor: colorFor(e.owner_id),
      editable: canEdit(e), // nur eigene/als Verwalter ziehbar
    }))

  function toggleHidden(id: number) {
    setHidden((h) => (h.includes(id) ? h.filter((x) => x !== id) : [...h, id]))
  }

  function openCreate(arg: DateSelectArg) {
    setModal({
      ...CLOSED,
      open: true,
      mode: 'create',
      start: toLocalInput(arg.start),
      end: toLocalInput(arg.end),
      ownerId: userId, // Standard: für mich selbst
    })
  }

  function openEdit(arg: EventClickArg) {
    const event = events.find((e) => e.id === Number(arg.event.id))
    if (!event) return
    setModal({
      open: true,
      mode: 'edit',
      readOnly: !canEdit(event), // fremder Termin (Kind) -> nur ansehen
      id: event.id,
      title: event.title,
      start: toLocalInput(new Date(event.starts_at)),
      end: toLocalInput(new Date(event.ends_at)),
      ownerId: event.owner_id ?? userId,
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
      car_reserved: modal.carReserved,
      owner_id: modal.ownerId,
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
    'rounded-lg border border-border px-3 py-2 outline-none focus:border-primary disabled:opacity-60'

  // Kinder können Termine nur sich selbst zuordnen.
  const ownerOptions = isGuardian ? members : members.filter((m) => m.id === userId)

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold text-primary">📅 Kalender</h1>
      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="rounded-2xl bg-surface p-4 shadow">
        <FullCalendar
          plugins={[dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin]}
          initialView="timeGridWeek"
          headerToolbar={{
            left: 'prev,next today',
            center: 'title',
            right: 'listWeek,timeGridDay,timeGridWeek,dayGridMonth',
          }}
          buttonText={{ today: 'Heute', month: 'Monat', week: 'Woche', day: 'Tag', list: 'Liste' }}
          locale={deLocale}
          firstDay={1}
          height="auto"
          nowIndicator
          slotMinTime="06:00:00"
          slotMaxTime="23:00:00"
          slotEventOverlap={false}
          selectable
          editable
          select={openCreate}
          events={fcEvents}
          eventClick={openEdit}
          eventDrop={persistMove}
          eventResize={persistMove}
        />
      </div>

      {/* Legende = Personen; antippen blendet ein Mitglied ein/aus */}
      <div className="flex flex-wrap items-center gap-2 text-xs">
        {members.map((m) => {
          const off = hidden.includes(m.id)
          return (
            <button
              key={m.id}
              type="button"
              onClick={() => toggleHidden(m.id)}
              className={`flex items-center gap-1.5 rounded-full px-2 py-1 transition ${
                off ? 'opacity-40' : 'hover:bg-surface-2'
              }`}
            >
              <span className="inline-block h-3 w-3 rounded-full" style={{ background: colorFor(m.id) }} />
              <span className={off ? 'text-muted line-through' : 'text-text'}>
                {m.first_name}
                {m.id === userId && ' (ich)'}
              </span>
            </button>
          )
        })}
        <span className="text-muted">· antippen zum Ein-/Ausblenden</span>
      </div>

      {modal.open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <form onSubmit={submit} className="w-full max-w-sm space-y-3 rounded-2xl bg-surface p-6 shadow-xl">
            <h2 className="text-lg font-semibold text-primary">
              {modal.mode === 'create' ? 'Neuer Termin' : modal.readOnly ? 'Termin' : 'Termin bearbeiten'}
            </h2>
            {modal.readOnly && (
              <p className="text-xs text-muted">Nur ansehen – dieser Termin gehört einem anderen Mitglied.</p>
            )}
            <input
              autoFocus
              placeholder="Titel"
              required
              disabled={modal.readOnly}
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
                  disabled={modal.readOnly}
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
                  disabled={modal.readOnly}
                  value={modal.end}
                  onChange={(e) => setModal({ ...modal, end: e.target.value })}
                  className={`${inputClass} mt-1 w-full`}
                />
              </label>
            </div>
            {modal.readOnly ? (
              <p className="text-xs text-muted">
                Für: {members.find((m) => m.id === modal.ownerId)?.first_name ?? '—'}
              </p>
            ) : (
              <label className="block text-xs text-muted">
                Für
                <select
                  value={modal.ownerId}
                  onChange={(e) => setModal({ ...modal, ownerId: Number(e.target.value) })}
                  className={`${inputClass} mt-1 w-full`}
                >
                  {ownerOptions.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.first_name}
                      {m.id === userId ? ' (ich)' : ''}
                    </option>
                  ))}
                </select>
              </label>
            )}
            <label className="flex items-center gap-2 text-sm text-muted">
              <input
                type="checkbox"
                disabled={modal.readOnly}
                checked={modal.carReserved}
                onChange={(e) => setModal({ ...modal, carReserved: e.target.checked })}
                className="h-5 w-5 accent-primary"
              />
              🚗 Auto reservieren
            </label>
            <div className="flex items-center justify-between pt-2">
              {modal.mode === 'edit' && !modal.readOnly ? (
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
                  {modal.readOnly ? 'Schließen' : 'Abbrechen'}
                </button>
                {!modal.readOnly && (
                  <button className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-hover">
                    {modal.mode === 'create' ? 'Anlegen' : 'Speichern'}
                  </button>
                )}
              </div>
            </div>
          </form>
        </div>
      )}
    </div>
  )
}
