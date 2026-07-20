import { useEffect, useState } from 'react'
import { apiError, authApi, subscriptionApi } from '../api'
import {
  Check,
  Crown,
  Fuel,
  Gamepad2,
  Globe,
  ImageIcon,
  type LucideIcon,
  PartyPopper,
  Share2,
  Shield,
  Users,
} from '../lib/icons'
import { MEMBER_PALETTE } from '../lib/memberColors'
import { useAuth } from '../store/auth'
import type { Subscription } from '../types'

// Ehrlich bleiben (ADR-0022): nur Verfügbares als verfügbar zeigen,
// Kommendes klar als "Bald" kennzeichnen.
const BENEFITS: { icon: LucideIcon; title: string; text: string; soon?: boolean }[] = [
  {
    icon: ImageIcon,
    // Ehrlich statt "unbegrenzt" (Timo 2026-07-18): Fair-Use-Grenze benennen.
    title: 'Riesiger Galerie-Speicher',
    text: '2.500 statt 100 Fotos (Fair Use) – inklusive 30 Tage Papierkorb.',
  },
  {
    icon: Globe,
    title: 'Kalender-Abos (iCal)',
    text: 'Schule, Verein & Abfallkalender erscheinen direkt im Familienkalender.',
  },
  {
    icon: Share2,
    title: 'Termine aufs Handy',
    text: 'Familienkalender in Google, Apple oder Outlook abonnieren.',
  },
  {
    icon: Fuel,
    title: 'Spritpreise in der Nähe',
    text: 'Aktuelle Preise rund um euren Familienort (Daten: Tankerkönig.de).',
  },
  {
    icon: Gamepad2,
    title: 'Premium-Spiele in der Fun Area',
    text: 'Ballon-Knallerei & Nidulas Blütenbeet – je mit eigener Familien-Bestenliste.',
  },
  {
    icon: Check,
    title: 'Belohnungs-Regal für ToDos',
    text: 'Eigene Familien-Belohnungen, einlösbar mit Nest-Blättern (🍃).',
    soon: true,
  },
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
    <div className="mx-auto max-w-2xl space-y-5">
      {error && <p className="text-sm text-red-600">{error}</p>}

      {/* Hero: eine Karte, die sagt, worum es geht – Status inklusive. */}
      <div className="relative overflow-hidden rounded-3xl bg-surface p-6 shadow sm:p-8">
        <div
          aria-hidden="true"
          className="pointer-events-none absolute -right-10 -top-10 h-44 w-44 rounded-full bg-primary/10"
        />
        <div
          aria-hidden="true"
          className="pointer-events-none absolute -bottom-14 -left-8 h-36 w-36 rounded-full bg-primary/5"
        />
        <div className="relative flex flex-col items-start gap-4 sm:flex-row sm:items-center">
          <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary/10">
            <Crown className="h-7 w-7 text-primary" />
          </span>
          <div className="min-w-0 flex-1">
            <h1 className="text-2xl font-bold text-primary">Nidula Premium</h1>
            <p className="mt-1 text-sm text-muted">
              Mehr Platz, mehr Sync, mehr Spaß – das Nest wächst mit euch.
            </p>
          </div>
          <span
            className={`shrink-0 rounded-full px-3 py-1 text-sm font-semibold ${
              isPremium ? 'bg-primary text-white' : 'bg-surface-2 text-muted'
            }`}
          >
            {isPremium
              ? sub?.plan === 'yearly'
                ? 'Premium · Jahresabo'
                : 'Premium · Monatsabo'
              : 'Free'}
          </span>
        </div>
        <p className="relative mt-4 flex items-center gap-2 rounded-xl bg-surface-2 px-3 py-2 text-xs text-muted">
          <Users className="h-4 w-4 shrink-0 text-primary" />
          Einmal aktivieren, alle profitieren: Premium gilt für die ganze Familie – auf jedem
          Gerät.
        </p>
      </div>

      {/* Vorteile als Karten: WAS bekommt die Familie konkret. */}
      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
        {BENEFITS.map((b) => {
          const active = isPremium && !b.soon
          return (
            <div key={b.title} className="flex gap-3 rounded-2xl bg-surface p-4 shadow">
              <span
                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${
                  active ? 'bg-primary/10' : 'bg-surface-2'
                }`}
              >
                <b.icon className={`h-5 w-5 ${active ? 'text-primary' : 'text-muted'}`} />
              </span>
              <div className="min-w-0">
                <p className="flex flex-wrap items-center gap-1.5 text-sm font-semibold text-text">
                  {b.title}
                  {b.soon ? (
                    <span className="rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                      Bald
                    </span>
                  ) : (
                    isPremium && <Check className="h-4 w-4 shrink-0 text-primary" />
                  )}
                </p>
                <p className="mt-0.5 text-xs text-muted">{b.text}</p>
              </div>
            </div>
          )
        })}
        <div className="flex gap-3 rounded-2xl bg-surface p-4 shadow">
          <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-2">
            <Shield className="h-5 w-5 text-muted" />
          </span>
          <div className="min-w-0">
            <p className="text-sm font-semibold text-text">Werbefrei & fair – für immer</p>
            <p className="mt-0.5 text-xs text-muted">
              Keine Werbung, kein Datenverkauf. Die Kernfunktionen bleiben für alle gratis –
              Premium finanziert die Weiterentwicklung.
            </p>
          </div>
        </div>
      </div>

      {/* Plan-Wahl bzw. Verwaltung */}
      <div className="rounded-2xl bg-surface p-6 shadow">
        {isPremium ? (
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p className="font-semibold text-text">Danke, dass ihr Nidula unterstützt! 💚</p>
              {sub?.expires_at && (
                <p className="mt-1 text-sm text-muted">
                  Läuft bis {new Date(sub.expires_at).toLocaleDateString('de-DE')}
                </p>
              )}
            </div>
            <button
              onClick={() => void cancel()}
              disabled={busy}
              className="rounded-lg border border-border px-5 py-2 text-sm text-muted hover:bg-surface-2 disabled:opacity-60"
            >
              Premium kündigen
            </button>
          </div>
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
                Danke, dass ihr Nidula unterstützt. Euer Nest hat jetzt richtig viel Platz.
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
