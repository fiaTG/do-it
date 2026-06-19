import { type MouseEvent } from 'react'
import { Car } from '../lib/icons'
import type { EventItem, User } from '../types'

// Selbstgebaute Tagesansicht mit einer Spalte je Familienmitglied (kostenlos,
// kein FullCalendar-Premium). Spalten = Personen, Zeilen = Stunden; Termine
// absolut nach Uhrzeit positioniert. So sieht man auf einen Blick, wer wann was hat.

const DAY_START = 6
const DAY_END = 23
const HOUR_PX = 48
const MS_PER_HOUR = 3_600_000
const HOURS = Array.from({ length: DAY_END - DAY_START }, (_, i) => DAY_START + i)
const TOTAL_HEIGHT = (DAY_END - DAY_START) * HOUR_PX

function fmtTime(iso: string): string {
  return new Date(iso).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })
}

interface Props {
  date: Date
  members: User[]
  events: EventItem[]
  colorFor: (ownerId: number | null) => string
  onEventClick: (event: EventItem) => void
  onSlotClick: (member: User, start: Date) => void
}

export default function PersonDayView({ date, members, events, colorFor, onEventClick, onSlotClick }: Props) {
  const winStart = new Date(date)
  winStart.setHours(DAY_START, 0, 0, 0)
  const winEnd = new Date(date)
  winEnd.setHours(DAY_END, 0, 0, 0)

  // Termine eines Mitglieds an diesem Tag inkl. einfacher Spur-Aufteilung bei
  // Überschneidungen (innerhalb derselben Person nebeneinander statt übereinander).
  function layoutFor(memberId: number) {
    const dayEvents = events.filter((e) => {
      if (e.owner_id !== memberId) return false
      return new Date(e.ends_at) > winStart && new Date(e.starts_at) < winEnd
    })
    const sorted = [...dayEvents].sort(
      (a, b) => +new Date(a.starts_at) - +new Date(b.starts_at),
    )
    const laneEnds: number[] = []
    const laneOf = new Map<number, number>()
    for (const e of sorted) {
      const s = +new Date(e.starts_at)
      let lane = laneEnds.findIndex((end) => end <= s)
      if (lane === -1) {
        lane = laneEnds.length
        laneEnds.push(0)
      }
      laneEnds[lane] = +new Date(e.ends_at)
      laneOf.set(e.id, lane)
    }
    const lanes = laneEnds.length || 1

    return dayEvents.map((e) => {
      const vs = Math.max(+new Date(e.starts_at), +winStart)
      const ve = Math.min(+new Date(e.ends_at), +winEnd)
      const lane = laneOf.get(e.id) ?? 0
      return {
        e,
        top: ((vs - +winStart) / MS_PER_HOUR) * HOUR_PX,
        height: Math.max(((ve - vs) / MS_PER_HOUR) * HOUR_PX, 22),
        leftPct: (lane / lanes) * 100,
        widthPct: 100 / lanes,
      }
    })
  }

  function handleColumnClick(member: User, ev: MouseEvent<HTMLDivElement>) {
    const y = ev.clientY - ev.currentTarget.getBoundingClientRect().top
    const hour = Math.min(DAY_START + Math.floor(y / HOUR_PX), DAY_END - 1)
    const start = new Date(date)
    start.setHours(hour, 0, 0, 0)
    onSlotClick(member, start)
  }

  return (
    <div className="flex">
      {/* Zeit-Spalte */}
      <div className="w-12 shrink-0">
        <div className="h-8" />
        <div className="relative" style={{ height: TOTAL_HEIGHT }}>
          {HOURS.map((h, i) => (
            <div
              key={h}
              className="absolute right-1 -translate-y-1/2 text-[10px] text-muted"
              style={{ top: i * HOUR_PX }}
            >
              {String(h).padStart(2, '0')}:00
            </div>
          ))}
        </div>
      </div>

      {/* Personen-Spalten – bei vielen Mitgliedern horizontal scrollbar */}
      <div className="flex flex-1 overflow-x-auto">
        {members.map((m) => (
          <div key={m.id} className="min-w-[7rem] flex-1 border-l border-border">
            <div className="flex h-8 items-center justify-center gap-1.5 text-xs font-medium text-text">
              <span className="inline-block h-2.5 w-2.5 rounded-full" style={{ background: colorFor(m.id) }} />
              {m.first_name}
            </div>
            <div
              className="relative cursor-pointer"
              style={{ height: TOTAL_HEIGHT }}
              onClick={(ev) => handleColumnClick(m, ev)}
            >
              {HOURS.map((h, i) => (
                <div
                  key={h}
                  className="absolute inset-x-0 border-t border-border/60"
                  style={{ top: i * HOUR_PX }}
                />
              ))}
              {layoutFor(m.id).map(({ e, top, height, leftPct, widthPct }) => (
                <button
                  key={e.id}
                  type="button"
                  onClick={(ev) => {
                    ev.stopPropagation()
                    onEventClick(e)
                  }}
                  className="absolute overflow-hidden rounded-md px-1.5 py-0.5 text-left text-[11px] leading-tight text-white shadow-sm"
                  style={{
                    top,
                    height,
                    left: `${leftPct}%`,
                    width: `calc(${widthPct}% - 2px)`,
                    background: colorFor(e.owner_id),
                  }}
                >
                  <span className="flex items-center gap-1 truncate font-semibold">
                    <span className="truncate">{e.title}</span>
                    {e.car_reserved && <Car className="h-3 w-3 shrink-0" aria-label="Auto reserviert" />}
                  </span>
                  <span className="block opacity-80">{fmtTime(e.starts_at)}</span>
                </button>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
