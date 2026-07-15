import { useEffect, useState, type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { apiError, eventsApi, familyApi, inviteApi } from '../api'
import MemberAvatar from '../components/MemberAvatar'
import { Baby, Cake, Calendar, Crown, Mail, MapPin, PartyPopper, Shield, Users, X } from '../lib/icons'
import { memberColor } from '../lib/memberColors'
import { expandEvents, type Occurrence } from '../lib/recurrence'
import { searchPlaces, type GeocodingResult } from '../lib/weather'
import { useAuth } from '../store/auth'
import type { EventItem, FamilyRole, Invite, User } from '../types'

const MS_PER_DAY = 86_400_000

function startOfDay(date: Date): Date {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate())
}

/** Alter in Jahren (Stichtag now). */
function age(birthdate: string, now: Date): number {
  const birth = new Date(birthdate)
  let years = now.getFullYear() - birth.getFullYear()
  const beforeBirthday =
    now.getMonth() < birth.getMonth() ||
    (now.getMonth() === birth.getMonth() && now.getDate() < birth.getDate())
  if (beforeBirthday) years--
  return years
}

/** Tage bis zum nächsten Geburtstag (0 = heute) und das Alter, das erreicht wird. */
function nextBirthday(birthdate: string, now: Date): { days: number; turns: number } {
  const birth = new Date(birthdate)
  const today = startOfDay(now)
  let next = new Date(today.getFullYear(), birth.getMonth(), birth.getDate())
  if (next < today) next = new Date(today.getFullYear() + 1, birth.getMonth(), birth.getDate())
  return {
    days: Math.round((+next - +today) / MS_PER_DAY),
    turns: next.getFullYear() - birth.getFullYear(),
  }
}

/** "Heute 16:00", "Morgen 09:30" oder "Mo., 21.07. 16:00". */
function eventLabel(event: EventItem, now: Date): string {
  const start = new Date(event.starts_at)
  const time = start.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })
  const diff = Math.round((+startOfDay(start) - +startOfDay(now)) / MS_PER_DAY)
  if (diff === 0) return `Heute ${time}`
  if (diff === 1) return `Morgen ${time}`
  return `${start.toLocaleDateString('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit' })} ${time}`
}

export default function MembersPage() {
  const me = useAuth((s) => s.user)
  const setUser = useAuth((s) => s.setUser)
  const isGuardian = me?.role !== 'child'
  const [members, setMembers] = useState<User[]>([])
  const [events, setEvents] = useState<EventItem[]>([])
  const [invites, setInvites] = useState<Invite[]>([])
  const [inviteRole, setInviteRole] = useState<FamilyRole>('guardian')
  // Einmal beim Mount fixiert – Render bleibt pur (react-hooks/purity).
  const [now] = useState(() => new Date())
  const [email, setEmail] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [placeQuery, setPlaceQuery] = useState('')
  const [placeResults, setPlaceResults] = useState<GeocodingResult[]>([])
  const [placeSearching, setPlaceSearching] = useState(false)

  async function load() {
    try {
      const [mem, ev, inv] = await Promise.all([
        familyApi.members(),
        eventsApi.list(),
        inviteApi.list(),
      ])
      setMembers(mem)
      setEvents(ev)
      setInvites(inv)
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  /** Nächster (laufender oder kommender) Termin eines Mitglieds – inkl. Serien. */
  function nextEventFor(memberId: number): Occurrence | undefined {
    return expandEvents(events, now, new Date(+now + 90 * MS_PER_DAY))
      .filter((e) => e.owner_id === memberId && +new Date(e.ends_at) >= +now)
      .sort((a, b) => +new Date(a.starts_at) - +new Date(b.starts_at))[0]
  }

  // Verwalter können die Rolle anderer Mitglieder umstellen (Kind <-> Verwalter).
  async function changeRole(member: User, role: FamilyRole) {
    setError('')
    try {
      await familyApi.updateRole(member.id, role)
      await load()
    } catch (err) {
      setError(apiError(err))
    }
  }

  async function invite(e: FormEvent) {
    e.preventDefault()
    setError('')
    setMessage('')
    try {
      await inviteApi.create(email, inviteRole)
      setMessage(`Einladung an ${email} verschickt (im Dev-Setup landet sie in Mailpit).`)
      setEmail('')
      setInvites(await inviteApi.list())
    } catch (err) {
      setError(apiError(err))
    }
  }

  async function searchPlace(e: FormEvent) {
    e.preventDefault()
    if (placeQuery.trim().length < 2) return
    setPlaceSearching(true)
    setError('')
    try {
      const results = await searchPlaces(placeQuery.trim())
      setPlaceResults(results)
      if (results.length === 0) setError('Kein Ort gefunden – anders schreiben?')
    } catch {
      setError('Ortssuche gerade nicht erreichbar.')
    } finally {
      setPlaceSearching(false)
    }
  }

  async function chooseLocation(place: GeocodingResult) {
    setError('')
    try {
      const updated = await familyApi.updateLocation({
        location_name: place.name,
        latitude: place.latitude,
        longitude: place.longitude,
      })
      if (me) setUser({ ...me, family: updated })
      setPlaceResults([])
      setPlaceQuery('')
      setMessage(`Familienort auf ${updated.location_name} gesetzt – das Dashboard zeigt jetzt euer Wetter.`)
    } catch (err) {
      setError(apiError(err))
    }
  }

  async function revoke(invite: Invite) {
    if (!window.confirm(`Einladung an ${invite.email} zurückziehen?`)) return
    setError('')
    try {
      await inviteApi.remove(invite.id)
      setInvites((prev) => prev.filter((i) => i.id !== invite.id))
    } catch (err) {
      setError(apiError(err))
    }
  }

  if (error && members.length === 0) return <p className="text-red-600">{error}</p>

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <Users className="h-6 w-6" /> Familie
      </h1>
      {error && <p className="text-sm text-red-600">{error}</p>}

      {/* Familien-Kopfkarte: Name, Mitgliederzahl, Premium-Status + Einladen. */}
      <div className="rounded-2xl bg-surface p-5 shadow">
        <div className="flex flex-wrap items-center justify-between gap-2">
          <div>
            <p className="text-lg font-bold text-text">{me?.family?.name ?? 'Unsere Familie'}</p>
            <p className="text-sm text-muted">
              {members.length} {members.length === 1 ? 'Mitglied' : 'Mitglieder'}
            </p>
          </div>
          {me?.family?.is_premium && (
            <span className="inline-flex items-center gap-1.5 rounded-full bg-primary px-3 py-1 text-sm font-semibold text-white">
              <Crown className="h-4 w-4" /> Premium
            </span>
          )}
        </div>

        <div className="mt-3 flex flex-wrap items-center gap-2 text-sm text-muted">
          <MapPin className="h-4 w-4 shrink-0" />
          {me?.family?.location_name ? (
            <span>
              Familienort: <span className="font-semibold text-text">{me.family.location_name}</span>
            </span>
          ) : (
            <span>Noch kein Familienort – fürs Wetter auf dem Dashboard.</span>
          )}
        </div>
        {isGuardian && (
          <form onSubmit={searchPlace} className="mt-2 space-y-2">
            <div className="flex gap-2">
              <input
                placeholder="Ort suchen (z. B. Mannheim) …"
                value={placeQuery}
                onChange={(e) => setPlaceQuery(e.target.value)}
                className="flex-1 rounded-lg border border-border px-3 py-2 text-sm outline-none focus:border-primary"
              />
              <button
                disabled={placeSearching}
                className="rounded-lg bg-surface-2 px-4 py-2 text-sm font-semibold text-muted hover:text-primary disabled:opacity-60"
              >
                {placeSearching ? 'Sucht …' : 'Suchen'}
              </button>
            </div>
            {placeResults.length > 0 && (
              <ul className="space-y-1">
                {placeResults.map((r, i) => (
                  <li key={`${r.latitude}-${r.longitude}-${i}`}>
                    <button
                      type="button"
                      onClick={() => void chooseLocation(r)}
                      className="w-full rounded-lg bg-surface-2 px-3 py-1.5 text-left text-sm hover:text-primary"
                    >
                      {r.name}
                      {r.admin1 ? `, ${r.admin1}` : ''}
                      {r.country ? ` (${r.country})` : ''}
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </form>
        )}

        {isGuardian && (
          <form onSubmit={invite} className="mt-4 space-y-2">
            {message && <p className="text-sm text-green-700">{message}</p>}
            <div className="flex gap-2">
              <input
                type="email"
                placeholder="Per E-Mail einladen …"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="flex-1 rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
              />
              <button className="rounded-lg bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-hover">
                Einladen
              </button>
            </div>
            {/* Rolle des Eingeladenen (ADR-0021) – Kind kann sie später nicht selbst ändern. */}
            <div className="flex items-center gap-2 text-xs text-muted">
              Tritt bei als:
              {(['guardian', 'child'] as const).map((r) => (
                <button
                  key={r}
                  type="button"
                  onClick={() => setInviteRole(r)}
                  className={`flex items-center gap-1 rounded-full px-2.5 py-1 font-semibold transition ${
                    inviteRole === r ? 'bg-primary text-white' : 'bg-surface-2 hover:text-primary'
                  }`}
                >
                  {r === 'guardian' ? <Shield className="h-3 w-3" /> : <Baby className="h-3 w-3" />}
                  {r === 'guardian' ? 'Verwalter' : 'Kind'}
                </button>
              ))}
            </div>
          </form>
        )}

        {invites.length > 0 && (
          <div className="mt-4 border-t border-border pt-3">
            <p className="mb-2 text-xs font-semibold text-muted">Offene Einladungen</p>
            <ul className="space-y-1.5">
              {invites.map((inv) => (
                <li key={inv.id} className="flex items-center gap-2 text-sm">
                  <Mail className="h-4 w-4 shrink-0 text-muted" />
                  <span className="min-w-0 flex-1 truncate text-text">{inv.email}</span>
                  <span className="shrink-0 rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                    {inv.role === 'child' ? 'Kind' : 'Verwalter'}
                  </span>
                  {inv.expires_at && (
                    <span className="shrink-0 text-xs text-muted">
                      bis {new Date(inv.expires_at).toLocaleDateString('de-DE')}
                    </span>
                  )}
                  {isGuardian && (
                    <button
                      onClick={() => void revoke(inv)}
                      aria-label={`Einladung an ${inv.email} zurückziehen`}
                      className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-muted hover:bg-surface-2 hover:text-red-600"
                    >
                      <X className="h-3.5 w-3.5" />
                    </button>
                  )}
                </li>
              ))}
            </ul>
          </div>
        )}
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {members.map((m) => {
          const color = memberColor(m)
          const birthday = m.birthdate ? nextBirthday(m.birthdate, now) : null
          const next = nextEventFor(m.id)
          return (
            <div key={m.id} className="overflow-hidden rounded-2xl bg-surface shadow">
              {/* Farbakzent = Kalenderfarbe der Person (siehe Kalender-Legende). */}
              <div className="h-1.5" style={{ background: color }} />
              <div className="flex flex-col items-center p-5">
                <MemberAvatar member={m} size="lg" />
                <div className="mt-2 text-center font-medium text-text">
                  {m.first_name} {m.last_name}
                  {m.id === me?.id && <span className="text-muted"> (ich)</span>}
                </div>
                <span className="mt-1 inline-flex items-center gap-1 rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                  {m.role === 'child' ? (
                    <>
                      <Baby className="h-3 w-3" /> Kind
                    </>
                  ) : (
                    <>
                      <Shield className="h-3 w-3" /> Verwalter
                    </>
                  )}
                </span>

                <div className="mt-3 w-full space-y-1.5 text-xs text-muted">
                  {m.birthdate && birthday && (
                    <p className="flex items-center gap-1.5">
                      {birthday.days === 0 ? (
                        <>
                          <PartyPopper className="h-3.5 w-3.5 shrink-0 text-primary" />
                          <span className="font-semibold text-primary">
                            Hat heute Geburtstag – {age(m.birthdate, now)} Jahre!
                          </span>
                        </>
                      ) : (
                        <>
                          <Cake className="h-3.5 w-3.5 shrink-0" />
                          <span>
                            {age(m.birthdate, now)} Jahre · wird {birthday.turns} in{' '}
                            {birthday.days === 1 ? 'einem Tag' : `${birthday.days} Tagen`}
                          </span>
                        </>
                      )}
                    </p>
                  )}
                  {next && (
                    <Link to="/calendar" className="flex items-center gap-1.5 hover:text-primary">
                      <Calendar className="h-3.5 w-3.5 shrink-0" />
                      <span className="truncate">
                        {eventLabel(next, now)} · {next.title}
                      </span>
                    </Link>
                  )}
                  {!m.birthdate && !next && <p className="text-center">&nbsp;</p>}
                </div>

                {isGuardian && m.id !== me?.id && (
                  <button
                    onClick={() => void changeRole(m, m.role === 'child' ? 'guardian' : 'child')}
                    className="mt-2 text-xs text-primary hover:underline"
                  >
                    {m.role === 'child' ? 'zu Verwalter machen' : 'zu Kind machen'}
                  </button>
                )}
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}
