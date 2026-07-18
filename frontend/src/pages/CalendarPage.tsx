import { useCallback, useEffect, useMemo, useState, type FormEvent } from 'react'
import FullCalendar from '@fullcalendar/react'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import type { EventClickArg, EventContentArg, EventInput } from '@fullcalendar/core'
import deLocale from '@fullcalendar/core/locales/de'
import { Link } from 'react-router-dom'
import { apiError, calendarFeedsApi, eventsApi, familyApi } from '../api'
import CalendarFeedManager from '../components/CalendarFeedManager'
import CalendarShareDialog from '../components/CalendarShareDialog'
import MemberAvatar from '../components/MemberAvatar'
import PersonDayView from '../components/PersonDayView'
import { Calendar, Car, Check, Crown, Globe, MapPin, RotateCcw, Share2 } from '../lib/icons'
import { FALLBACK_COLOR, memberColor } from '../lib/memberColors'
import { expandEvents } from '../lib/recurrence'
import { useAuth } from '../store/auth'
import type { CalendarFeed, EventItem, FeedEvent, User } from '../types'

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
  recurrence: string
  recurrenceUntil: string
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
  recurrence: '',
  recurrenceUntil: '',
}

/** Date -> Wert für <input type="datetime-local"> (lokale Zeit, ohne Sekunden). */
function toLocalInput(date: Date): string {
  const pad = (n: number) => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

/** Zeitangabe eines Abo-Termins fürs Info-Fenster (ganztägig: Ende exklusiv). */
function formatFeedWhen(e: FeedEvent): string {
  const dayFmt: Intl.DateTimeFormatOptions = { weekday: 'short', day: 'numeric', month: 'long' }
  if (e.all_day) {
    const start = new Date(`${e.starts_at}T00:00:00`)
    const lastDay = new Date(+new Date(`${e.ends_at}T00:00:00`) - 86_400_000)
    const range =
      +lastDay <= +start
        ? start.toLocaleDateString('de-DE', dayFmt)
        : `${start.toLocaleDateString('de-DE', dayFmt)} – ${lastDay.toLocaleDateString('de-DE', dayFmt)}`
    return `${range} · ganztägig`
  }
  const start = new Date(e.starts_at)
  const end = new Date(e.ends_at)
  const time: Intl.DateTimeFormatOptions = { hour: '2-digit', minute: '2-digit' }
  return `${start.toLocaleDateString('de-DE', dayFmt)}, ${start.toLocaleTimeString('de-DE', time)} – ${end.toLocaleTimeString('de-DE', time)} Uhr`
}

export default function CalendarPage() {
  const me = useAuth((s) => s.user)
  const userId = me?.id ?? 0
  const isGuardian = me?.role === 'guardian' // Verwalter dürfen alle Termine verwalten
  const isPremium = me?.family?.is_premium ?? false

  const [events, setEvents] = useState<EventItem[]>([])
  const [members, setMembers] = useState<User[]>([])
  const [hidden, setHidden] = useState<number[]>([]) // ausgeblendete Personen
  const [feeds, setFeeds] = useState<CalendarFeed[]>([]) // Kalender-Abos (Premium)
  const [feedEvents, setFeedEvents] = useState<FeedEvent[]>([])
  const [hiddenFeeds, setHiddenFeeds] = useState<number[]>([])
  const [manageFeeds, setManageFeeds] = useState(false)
  const [shareOpen, setShareOpen] = useState(false)
  const [feedInfo, setFeedInfo] = useState<FeedEvent | null>(null)
  const [modal, setModal] = useState<ModalState>(CLOSED)
  const [error, setError] = useState('')
  const [view, setView] = useState<'overview' | 'person'>('overview')
  const [personDate, setPersonDate] = useState(() => new Date())
  // Einmal beim Mount fixiert (react-hooks/purity); Expansionsfenster ±.
  const [mountedAt] = useState(() => new Date())
  // Handy-Erkennung einmalig: kleine Screens starten in der Listen-Ansicht
  // (Beta-Feedback: Wochen-Raster ist auf dem Handy hakelig und eng).
  const [isMobile] = useState(() => window.matchMedia('(max-width: 639px)').matches)

  // Gleiches Fenster für eigene Serien UND Abo-Termine: 1 Jahr zurück, 2 voraus.
  const [windowFrom, windowTo] = useMemo(
    () => [
      new Date(mountedAt.getFullYear() - 1, 0, 1),
      new Date(mountedAt.getFullYear() + 2, 0, 1),
    ],
    [mountedAt],
  )

  async function load() {
    try {
      const [ev, mem] = await Promise.all([eventsApi.list(), familyApi.members()])
      setEvents(ev)
      setMembers(mem)
    } catch (err) {
      setError(apiError(err))
    }
  }

  // Abos sind Zusatz (Premium): Fehler hier lassen den Kalender selbst in Ruhe.
  const loadFeeds = useCallback(async () => {
    try {
      const [fs, fe] = await Promise.all([
        calendarFeedsApi.list(),
        calendarFeedsApi.events(windowFrom, windowTo),
      ])
      setFeeds(fs)
      setFeedEvents(fe)
    } catch {
      /* Kalender bleibt ohne Abo-Ebene nutzbar */
    }
  }, [windowFrom, windowTo])

  useEffect(() => {
    void load()
  }, [])

  useEffect(() => {
    if (isPremium) void loadFeeds()
  }, [isPremium, loadFeeds])

  // Farbe je Mitglied: selbst gewählt (users.color) oder stabiler ID-Fallback.
  const memberById = (id: number | null): User | undefined =>
    members.find((m) => m.id === id)
  const colorFor = (ownerId: number | null): string => {
    const member = memberById(ownerId)
    return member ? memberColor(member) : FALLBACK_COLOR
  }

  // Kinder dürfen nur SELBST angelegte eigene Termine bearbeiten (Owner-Schutz,
  // ADR-0021) – vom Verwalter für sie angelegte Termine sind nur lesbar.
  const canEdit = (e: EventItem): boolean =>
    isGuardian || (e.owner_id === userId && e.created_by === userId)

  // Serien in Vorkommen auflösen: 1 Jahr zurück, 2 Jahre voraus.
  const occurrences = useMemo(
    () => expandEvents(events, windowFrom, windowTo),
    [events, windowFrom, windowTo],
  )

  const feedById = (id: number): CalendarFeed | undefined => feeds.find((f) => f.id === id)

  const fcEvents: EventInput[] = occurrences
    .filter((e) => !hidden.includes(e.owner_id ?? -1))
    .map((e) => ({
      id: e.occurrence_key,
      title: e.title,
      start: e.starts_at,
      end: e.ends_at,
      backgroundColor: colorFor(e.owner_id),
      borderColor: colorFor(e.owner_id),
      // Serien-Vorkommen nicht ziehbar (sonst würde die ganze Serie umziehen).
      editable: canEdit(e) && !e.recurrence,
      extendedProps: {
        eventId: e.id,
        ownerId: e.owner_id,
        carReserved: e.car_reserved,
        recurrence: e.recurrence,
      },
    }))

  // Abo-Termine (ADR-0023): eigene Lese-Ebene in der Feed-Farbe, nie ziehbar.
  const fcFeedEvents: EventInput[] = feedEvents
    .filter((e) => !hiddenFeeds.includes(e.feed_id))
    .map((e) => ({
      id: e.id,
      title: e.title,
      start: e.starts_at,
      end: e.ends_at,
      allDay: e.all_day,
      backgroundColor: feedById(e.feed_id)?.color ?? FALLBACK_COLOR,
      borderColor: feedById(e.feed_id)?.color ?? FALLBACK_COLOR,
      editable: false,
      extendedProps: { feedEvent: e },
    }))

  // Eigener Event-Inhalt: Mini-Avatar des Owners + Zeit + Titel, damit in der
  // Übersicht sofort erkennbar ist, WESSEN Termin es ist (nicht nur die Farbe).
  function renderEventContent(arg: EventContentArg) {
    // Abo-Termine: Globus statt Personen-Avatar (gehören keinem Mitglied).
    const feedEvent = arg.event.extendedProps.feedEvent as FeedEvent | undefined
    if (feedEvent) {
      return (
        <span className="flex min-w-0 items-center gap-1 px-0.5">
          <Globe className="h-3 w-3 shrink-0 opacity-80" />
          {arg.timeText && <span className="shrink-0 text-[10px] opacity-80">{arg.timeText}</span>}
          <span className="truncate text-[11px] font-semibold">{arg.event.title}</span>
        </span>
      )
    }
    const owner = memberById(arg.event.extendedProps.ownerId as number | null)
    const carReserved = arg.event.extendedProps.carReserved as boolean
    return (
      <span className="flex min-w-0 items-center gap-1 px-0.5">
        {owner && <MemberAvatar member={owner} size="xs" />}
        {arg.timeText && <span className="shrink-0 text-[10px] opacity-80">{arg.timeText}</span>}
        <span className="truncate text-[11px] font-semibold">{arg.event.title}</span>
        {carReserved && <Car className="h-3 w-3 shrink-0" aria-label="Auto reserviert" />}
      </span>
    )
  }

  function toggleHidden(id: number) {
    setHidden((h) => (h.includes(id) ? h.filter((x) => x !== id) : [...h, id]))
  }

  function openCreateAt(start: Date, end: Date, ownerId: number) {
    setModal({
      ...CLOSED,
      open: true,
      mode: 'create',
      start: toLocalInput(start),
      end: toLocalInput(end),
      // Kinder dürfen nur für sich selbst eintragen.
      ownerId: isGuardian ? ownerId : userId,
    })
  }

  function openEditEvent(event: EventItem) {
    // Bei Serien-Vorkommen immer die SERIE bearbeiten (Original-Zeiten laden).
    const series = events.find((e) => e.id === event.id) ?? event
    setModal({
      open: true,
      mode: 'edit',
      readOnly: !canEdit(series), // fremder Termin (Kind) -> nur ansehen
      id: series.id,
      title: series.title,
      start: toLocalInput(new Date(series.starts_at)),
      end: toLocalInput(new Date(series.ends_at)),
      ownerId: series.owner_id ?? userId,
      carReserved: series.car_reserved,
      recurrence: series.recurrence ?? '',
      recurrenceUntil: series.recurrence_until ?? '',
    })
  }

  function openEditFromCalendar(arg: EventClickArg) {
    const feedEvent = arg.event.extendedProps.feedEvent as FeedEvent | undefined
    if (feedEvent) {
      setFeedInfo(feedEvent) // Abo-Termine sind nur lesbar
      return
    }
    const event = events.find((e) => e.id === Number(arg.event.extendedProps.eventId))
    if (event) openEditEvent(event)
  }

  function toggleHiddenFeed(id: number) {
    setHiddenFeeds((h) => (h.includes(id) ? h.filter((x) => x !== id) : [...h, id]))
  }

  // Drag&Drop bzw. Resize -> nur die Zeiten persistieren (nur Einzeltermine,
  // Serien-Vorkommen sind nicht ziehbar; occurrence_key einmaliger Events = id).
  async function persistMove(arg: { event: { id: string; start: Date | null; end: Date | null } }) {
    const { id, start, end } = arg.event
    if (!start) return
    await eventsApi.update(Number(id.split(':')[0]), {
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
      recurrence: modal.recurrence || null,
      recurrence_until: modal.recurrence && modal.recurrenceUntil ? modal.recurrenceUntil : null,
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
  const visibleMembers = members.filter((m) => !hidden.includes(m.id))

  return (
    <div className="space-y-4">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <Calendar className="h-6 w-6" /> Kalender
      </h1>
      {error && <p className="text-sm text-red-600">{error}</p>}

      {/* Umschalter: Übersicht (Woche/Monat/Liste) vs. Tag nach Person */}
      <div className="inline-flex rounded-lg border border-border bg-surface p-0.5 text-sm">
        <button
          type="button"
          onClick={() => setView('overview')}
          className={`rounded-md px-3 py-1.5 ${view === 'overview' ? 'bg-primary text-white' : 'text-muted'}`}
        >
          Übersicht
        </button>
        <button
          type="button"
          onClick={() => setView('person')}
          className={`rounded-md px-3 py-1.5 ${view === 'person' ? 'bg-primary text-white' : 'text-muted'}`}
        >
          Tag nach Person
        </button>
      </div>

      {/* Legende = Personen (Avatar + Farbe); antippen blendet ein Mitglied ein/aus */}
      <div className="flex flex-wrap items-center gap-2 text-xs">
        {members.map((m) => {
          const off = hidden.includes(m.id)
          return (
            <button
              key={m.id}
              type="button"
              onClick={() => toggleHidden(m.id)}
              className={`flex items-center gap-1.5 rounded-full py-1 pl-1 pr-2.5 transition ${
                off ? 'opacity-40 grayscale' : 'hover:bg-surface-2'
              }`}
            >
              <MemberAvatar member={m} size="sm" />
              <span className={off ? 'text-muted line-through' : 'text-text'}>
                {m.first_name}
                {m.id === userId && ' (ich)'}
              </span>
            </button>
          )
        })}
        {/* Kalender-Abos (Premium): Feed-Chips zum Ein-/Ausblenden + Verwaltung */}
        {feeds.map((f) => {
          const off = hiddenFeeds.includes(f.id)
          return (
            <button
              key={`feed-${f.id}`}
              type="button"
              onClick={() => toggleHiddenFeed(f.id)}
              className={`flex items-center gap-1.5 rounded-full py-1 pl-2 pr-2.5 transition ${
                off ? 'opacity-40 grayscale' : 'hover:bg-surface-2'
              }`}
            >
              <span className="h-2.5 w-2.5 rounded-full" style={{ background: f.color }} />
              <Globe className="h-3 w-3 text-muted" />
              <span className={off ? 'text-muted line-through' : 'text-text'}>{f.name}</span>
            </button>
          )
        })}
        <span className="text-muted">· antippen zum Ein-/Ausblenden</span>
        {isPremium ? (
          <span className="ml-auto flex items-center gap-1.5">
            {/* Kalender teilen: alle Mitglieder (jedes Handy soll abonnieren) */}
            <button
              type="button"
              onClick={() => setShareOpen(true)}
              className="flex items-center gap-1.5 rounded-full border border-border px-2.5 py-1 text-muted hover:bg-surface-2"
            >
              <Share2 className="h-3.5 w-3.5" /> Teilen
            </button>
            {isGuardian && (
              <button
                type="button"
                onClick={() => setManageFeeds(true)}
                className="flex items-center gap-1.5 rounded-full border border-border px-2.5 py-1 text-muted hover:bg-surface-2"
              >
                <Globe className="h-3.5 w-3.5" /> Kalender-Abos
              </button>
            )}
          </span>
        ) : (
          isGuardian && (
            <Link
              to="/premium"
              className="ml-auto flex items-center gap-1.5 rounded-full border border-border px-2.5 py-1 text-muted hover:bg-surface-2"
            >
              <Crown className="h-3.5 w-3.5 text-primary" /> Kalender-Abos & Teilen (Premium)
            </Link>
          )
        )}
      </div>

      <div className="rounded-2xl bg-surface p-4 shadow">
        {view === 'overview' ? (
          <FullCalendar
            plugins={[dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin]}
            initialView={isMobile ? 'listWeek' : 'timeGridWeek'}
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
            // Touch: schnelleres Antippen/Ziehen (Standard 1000 ms fühlt sich tot an)
            longPressDelay={150}
            eventLongPressDelay={150}
            selectLongPressDelay={300}
            // Monatsansicht: max. 3 Termine je Tag, Rest als "+ x weitere"
            dayMaxEvents={3}
            moreLinkText={(n) => `+ ${n} weitere`}
            selectable
            editable
            select={(arg) => openCreateAt(arg.start, arg.end, userId)}
            events={[...fcEvents, ...fcFeedEvents]}
            eventContent={renderEventContent}
            eventClick={openEditFromCalendar}
            eventDrop={persistMove}
            eventResize={persistMove}
          />
        ) : (
          <>
            <div className="mb-3 flex items-center justify-between">
              <div className="flex gap-1">
                <button
                  type="button"
                  onClick={() => setPersonDate((d) => new Date(+d - 86_400_000))}
                  className="rounded-lg px-3 py-1.5 text-sm hover:bg-surface-2"
                  aria-label="Vorheriger Tag"
                >
                  ‹
                </button>
                <button
                  type="button"
                  onClick={() => setPersonDate(new Date())}
                  className="rounded-lg px-3 py-1.5 text-sm hover:bg-surface-2"
                >
                  Heute
                </button>
                <button
                  type="button"
                  onClick={() => setPersonDate((d) => new Date(+d + 86_400_000))}
                  className="rounded-lg px-3 py-1.5 text-sm hover:bg-surface-2"
                  aria-label="Nächster Tag"
                >
                  ›
                </button>
              </div>
              <span className="text-sm font-semibold capitalize text-text">
                {personDate.toLocaleDateString('de-DE', { weekday: 'long', day: 'numeric', month: 'long' })}
              </span>
            </div>
            <PersonDayView
              date={personDate}
              members={visibleMembers}
              events={occurrences}
              colorFor={colorFor}
              onEventClick={openEditEvent}
              onSlotClick={(m, start) => openCreateAt(start, new Date(+start + 3_600_000), m.id)}
            />
          </>
        )}
      </div>

      {manageFeeds && (
        <CalendarFeedManager
          feeds={feeds}
          onClose={() => setManageFeeds(false)}
          onChanged={loadFeeds}
        />
      )}

      {shareOpen && <CalendarShareDialog onClose={() => setShareOpen(false)} />}

      {/* Info-Fenster für Abo-Termine (nur lesbar) */}
      {feedInfo && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <div className="w-full max-w-sm space-y-3 rounded-2xl bg-surface p-6 shadow-xl">
            <div className="flex items-center gap-2 text-xs text-muted">
              <span
                className="h-2.5 w-2.5 rounded-full"
                style={{ background: feedById(feedInfo.feed_id)?.color ?? FALLBACK_COLOR }}
              />
              <Globe className="h-3.5 w-3.5" />
              {feedById(feedInfo.feed_id)?.name ?? 'Kalender-Abo'}
            </div>
            <h2 className="text-lg font-semibold text-text">{feedInfo.title}</h2>
            <p className="text-sm text-muted">{formatFeedWhen(feedInfo)}</p>
            {feedInfo.location && (
              <p className="flex items-center gap-1.5 text-sm text-muted">
                <MapPin className="h-4 w-4 shrink-0" /> {feedInfo.location}
              </p>
            )}
            <p className="text-xs text-muted">
              Aus einem abonnierten Kalender – hier nur lesbar. Änderungen macht die Quelle.
            </p>
            <div className="flex justify-end">
              <button
                type="button"
                onClick={() => setFeedInfo(null)}
                className="rounded-lg px-4 py-2 text-sm text-muted hover:bg-surface-2"
              >
                Schließen
              </button>
            </div>
          </div>
        </div>
      )}

      {modal.open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <form onSubmit={submit} className="w-full max-w-sm space-y-3 rounded-2xl bg-surface p-6 shadow-xl">
            <h2 className="text-lg font-semibold text-primary">
              {modal.mode === 'create' ? 'Neuer Termin' : modal.readOnly ? 'Termin' : 'Termin bearbeiten'}
            </h2>
            {modal.readOnly && (
              <p className="text-xs text-muted">
                Nur ansehen – diesen Termin kannst du nicht bearbeiten (von einem Verwalter angelegt
                oder er gehört einem anderen Mitglied).
              </p>
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
              <div className="flex items-center gap-2 text-xs text-muted">
                Für:
                {memberById(modal.ownerId) && (
                  <MemberAvatar member={memberById(modal.ownerId)!} size="sm" />
                )}
                {memberById(modal.ownerId)?.first_name ?? '—'}
              </div>
            ) : (
              <div className="text-xs text-muted">
                Für
                {/* Avatar-Chips statt Dropdown: WER ist auf einen Blick wählbar. */}
                <div className="mt-1 flex flex-wrap gap-2">
                  {ownerOptions.map((m) => {
                    const active = modal.ownerId === m.id
                    return (
                      <button
                        key={m.id}
                        type="button"
                        onClick={() => setModal({ ...modal, ownerId: m.id })}
                        // Deutliches Selected (Beta-Feedback): Ring + Häkchen,
                        // nicht nur Farbwechsel – auf dem Handy sonst zu subtil.
                        className={`flex items-center gap-1.5 rounded-full border py-1 pl-1 pr-2.5 text-sm font-semibold transition ${
                          active
                            ? 'border-transparent text-white ring-2 ring-primary ring-offset-2 ring-offset-surface'
                            : 'border-border text-text opacity-70 hover:bg-surface-2 hover:opacity-100'
                        }`}
                        style={active ? { background: memberColor(m) } : undefined}
                      >
                        <MemberAvatar member={m} size="sm" />
                        {m.first_name}
                        {m.id === userId ? ' (ich)' : ''}
                        {active && <Check className="h-4 w-4 shrink-0" />}
                      </button>
                    )
                  })}
                </div>
              </div>
            )}
            <label className="flex items-center gap-2 text-sm text-muted">
              <input
                type="checkbox"
                disabled={modal.readOnly}
                checked={modal.carReserved}
                onChange={(e) => setModal({ ...modal, carReserved: e.target.checked })}
                className="h-5 w-5 accent-primary"
              />
              <Car className="h-4 w-4 text-muted" /> Auto reservieren
            </label>

            {/* Serie: Mülltonnen wöchentlich, TÜV jährlich (Produkt-Backlog). */}
            <div className="flex gap-2">
              <label className="flex-1 text-xs text-muted">
                <span className="flex items-center gap-1">
                  <RotateCcw className="h-3 w-3" /> Wiederholung
                </span>
                <select
                  disabled={modal.readOnly}
                  value={modal.recurrence}
                  onChange={(e) => setModal({ ...modal, recurrence: e.target.value })}
                  className={`${inputClass} mt-1 w-full`}
                >
                  <option value="">Nie</option>
                  <option value="daily">Täglich</option>
                  <option value="weekly">Wöchentlich</option>
                  <option value="biweekly">Alle 2 Wochen</option>
                  <option value="monthly">Monatlich</option>
                  <option value="yearly">Jährlich</option>
                </select>
              </label>
              {modal.recurrence && (
                <label className="flex-1 text-xs text-muted">
                  Endet am (optional)
                  <input
                    type="date"
                    disabled={modal.readOnly}
                    value={modal.recurrenceUntil}
                    onChange={(e) => setModal({ ...modal, recurrenceUntil: e.target.value })}
                    className={`${inputClass} mt-1 w-full`}
                  />
                </label>
              )}
            </div>
            {modal.mode === 'edit' && modal.recurrence && !modal.readOnly && (
              <p className="text-xs text-muted">
                Serientermin – Änderungen gelten für die gesamte Serie.
              </p>
            )}
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
