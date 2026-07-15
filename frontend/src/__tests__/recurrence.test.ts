import { describe, expect, it } from 'vitest'
import { expandEvents } from '../lib/recurrence'
import type { EventItem } from '../types'

function event(overrides: Partial<EventItem>): EventItem {
  return {
    id: 1,
    title: 'Test',
    starts_at: '2026-07-20T07:00:00',
    ends_at: '2026-07-20T07:30:00',
    category: 'Sonstiges',
    car_reserved: false,
    created_by: 1,
    owner_id: 1,
    owner_name: 'Doris',
    recurrence: null,
    recurrence_until: null,
    ...overrides,
  }
}

const from = new Date('2026-07-01T00:00:00')
const to = new Date('2026-09-01T00:00:00')

describe('expandEvents', () => {
  it('lässt einmalige Termine unverändert durch', () => {
    const result = expandEvents([event({})], from, to)
    expect(result).toHaveLength(1)
    expect(result[0].occurrence_key).toBe('1')
    expect(result[0].starts_at).toBe('2026-07-20T07:00:00')
  })

  it('expandiert wöchentliche Serien im Fenster und respektiert das Enddatum', () => {
    const result = expandEvents(
      [event({ recurrence: 'weekly', recurrence_until: '2026-08-04' })],
      from,
      to,
    )
    // 20.07., 27.07., 03.08. – der 10.08. liegt hinter dem Enddatum.
    expect(result).toHaveLength(3)
    expect(result.map((o) => new Date(o.starts_at).getDate())).toEqual([20, 27, 3])
    // Uhrzeit bleibt erhalten, Dauer auch.
    const first = result[0]
    expect(new Date(first.starts_at).getHours()).toBe(7)
    expect(+new Date(first.ends_at) - +new Date(first.starts_at)).toBe(30 * 60 * 1000)
  })

  it('klemmt Monats-Serien am Monatsende (31. → kürzere Monate)', () => {
    const result = expandEvents(
      [
        event({
          starts_at: '2026-01-31T10:00:00',
          ends_at: '2026-01-31T11:00:00',
          recurrence: 'monthly',
        }),
      ],
      new Date('2026-01-01T00:00:00'),
      new Date('2026-05-01T00:00:00'),
    )
    // Jan 31, Feb 28, Mär 31, Apr 30
    expect(result.map((o) => new Date(o.starts_at).getDate())).toEqual([31, 28, 31, 30])
  })

  it('liefert nur Vorkommen, die das Fenster berühren', () => {
    const result = expandEvents(
      [event({ recurrence: 'daily' })],
      new Date('2026-07-25T00:00:00'),
      new Date('2026-07-28T00:00:00'),
    )
    expect(result.map((o) => new Date(o.starts_at).getDate())).toEqual([25, 26, 27])
  })

  it('expandiert jährliche Serien (TÜV-Fall)', () => {
    const result = expandEvents(
      [event({ recurrence: 'yearly' })],
      new Date('2026-01-01T00:00:00'),
      new Date('2029-01-01T00:00:00'),
    )
    expect(result.map((o) => new Date(o.starts_at).getFullYear())).toEqual([2026, 2027, 2028])
  })
})
