import { useEffect, useState } from 'react'
import { apiError, authApi, subscriptionApi } from '../api'
import { Check, Crown, PartyPopper } from '../lib/icons'
import { MEMBER_PALETTE } from '../lib/memberColors'
import { useAuth } from '../store/auth'
import type { Subscription } from '../types'

// Ehrlich bleiben (ADR-0022): nur Verfügbares als verfügbar zeigen,
// Kommendes klar als "Bald" kennzeichnen.
const BENEFITS: { label: string; soon?: boolean }[] = [
  { label: 'Unbegrenzter Galerie-Speicher' },
  { label: 'Kalender-Abos: Schule, Verein & Abfallkalender im Familienkalender (iCal)' },
  { label: 'Spritpreise rund um euren Familienort' },
  { label: 'Du unterstützt eine werbefreie Familien-App' },
  { label: 'Weitere Familienspiele in der Fun Area', soon: true },
]

type Plan = 'monthly' | 'yearly'

const PLANS: { id: Plan; name: string; price: string; note: string }[] = [
  { id: 'monthly', name: 'Monatlich', price: '2,99 €/Monat', note: 'jederzeit kündbar' },
  {
    id: 'yearly',
    name: 'Jährlich',
    price: '24,99 €/Jahr',
    note: 'entspricht 2,08 €/Monat · spart rund 30 %',
  },
]

interface ConfettiPiece {
  left: number
  delay: number
  duration: number
  size: number
  color: string
}

/** Konfetti-Regen in den Nidula-Farben – einmalig nach der Aktivierung. */
function Confetti() {
  // Im State-Initializer erzeugt (einmalig, Render bleibt pur).
  const [pieces] = useState<ConfettiPiece[]>(() =>
    Array.from({ length: 90 }, () => ({
      left: Math.random() * 100,
      delay: Math.random() * 0.8,
      duration: 2.2 + Math.random() * 1.6,
      size: 6 + Math.random() * 6,
      color: MEMBER_PALETTE[Math.floor(Math.random() * MEMBER_PALETTE.length)],
    })),
  )

  return (
    <div aria-hidden="true" className="pointer-events-none fixed inset-0 z-50 overflow-hidden">
      {pieces.map((p, i) => (
        <span
          key={i}
          className="absolute top-0 rounded-[2px]"
          style={{
            left: `${p.left}%`,
            width: p.size,
            height: p.size * 0.45,
            background: p.color,
            animation: `confetti-fall ${p.duration}s ease-in ${p.delay}s forwards`,
          }}
        />
      ))}
    </div>
  )
}

export default function PremiumPage() {
  const setUser = useAuth((s) => s.setUser)
  const [sub, setSub] = useState<Subscription | null>(null)
  const [plan, setPlan] = useState<Plan>('yearly')
  const [busy, setBusy] = useState(false)
  const [celebrate, setCelebrate] = useState(false)
  const [error, setError] = useState('')

  async function load() {
    try {
      setSub(await subscriptionApi.show())
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  // Auth-User aktualisieren, damit family.is_premium (Badge etc.) app-weit stimmt.
  async function refreshUser() {
    setUser(await authApi.me())
  }

  async function activate() {
    setBusy(true)
    setError('')
    try {
      setSub(await subscriptionApi.activate(plan))
      await refreshUser()
      setCelebrate(true)
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function cancel() {
    setBusy(true)
    setError('')
    try {
      await subscriptionApi.cancel()
      await load()
      await refreshUser()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  const isPremium = sub?.is_premium ?? false

  return (
    <div className="mx-auto max-w-lg space-y-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <Crown className="h-6 w-6" /> Premium
      </h1>
      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="rounded-2xl bg-surface p-6 shadow">
        <div className="mb-4 flex items-center justify-between">
          <span className="font-semibold text-text">Aktueller Plan</span>
          <span
            className={`rounded-full px-3 py-1 text-sm font-semibold ${
              isPremium ? 'bg-primary text-white' : 'bg-surface-2 text-muted'
            }`}
          >
            {isPremium ? (sub?.plan === 'yearly' ? 'Premium (Jahresabo)' : 'Premium (Monatsabo)') : 'Free'}
          </span>
        </div>

        <ul className="space-y-2">
          {BENEFITS.map((b) => (
            <li key={b.label} className="flex items-center gap-2 text-sm text-muted">
              <Check className={`h-4 w-4 shrink-0 ${isPremium && !b.soon ? 'text-primary' : 'text-muted'}`} />
              <span>{b.label}</span>
              {b.soon && (
                <span className="shrink-0 rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                  Bald
                </span>
              )}
            </li>
          ))}
        </ul>

        <div className="mt-6">
          {isPremium ? (
            <>
              {sub?.expires_at && (
                <p className="mb-3 text-sm text-muted">
                  Läuft bis {new Date(sub.expires_at).toLocaleDateString('de-DE')}
                </p>
              )}
              <button
                onClick={() => void cancel()}
                disabled={busy}
                className="rounded-lg border border-border px-5 py-2 text-sm text-muted hover:bg-surface-2 disabled:opacity-60"
              >
                Premium kündigen
              </button>
            </>
          ) : (
            <div className="space-y-3">
              {/* Ehrliche Plan-Wahl (ADR-0022): transparente Preise, keine Fake-Badges. */}
              <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                {PLANS.map((p) => {
                  const active = plan === p.id
                  return (
                    <button
                      key={p.id}
                      type="button"
                      onClick={() => setPlan(p.id)}
                      className={`rounded-xl border p-3 text-left transition ${
                        active
                          ? 'border-primary bg-primary/5 ring-1 ring-primary'
                          : 'border-border hover:bg-surface-2'
                      }`}
                    >
                      <span className="block font-semibold text-text">{p.name}</span>
                      <span className="block text-sm text-text">{p.price}</span>
                      <span className="block text-xs text-muted">{p.note}</span>
                    </button>
                  )
                })}
              </div>
              <button
                onClick={() => void activate()}
                disabled={busy}
                className="w-full rounded-lg bg-primary px-6 py-3 font-semibold text-white hover:bg-primary-hover disabled:opacity-60"
              >
                {busy ? 'Wird aktiviert …' : 'Premium aktivieren'}
              </button>
            </div>
          )}
        </div>
      </div>

      <p className="text-center text-xs text-muted">
        Hinweis: In dieser Entwicklungsversion wird der Kauf simuliert – es erfolgt keine
        echte Zahlung. Später über App Store / Google Play (ADR-0022).
      </p>

      {celebrate && (
        <>
          <Confetti />
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div className="w-full max-w-sm space-y-3 rounded-2xl bg-surface p-6 text-center shadow-xl">
              <PartyPopper className="mx-auto h-10 w-10 text-primary" />
              <h2 className="text-lg font-bold text-text">Willkommen bei Nidula Premium!</h2>
              <p className="text-sm text-muted">
                Danke, dass ihr Nidula unterstützt. Euer Nest hat jetzt unbegrenzten Platz.
              </p>
              <button
                onClick={() => setCelebrate(false)}
                className="w-full rounded-lg bg-primary py-2 font-semibold text-white hover:bg-primary-hover"
              >
                Los geht&apos;s
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  )
}
