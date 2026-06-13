import { useEffect, useState } from 'react'
import { apiError, authApi, subscriptionApi } from '../api'
import { useAuth } from '../store/auth'
import type { Subscription } from '../types'

const BENEFITS = [
  'Unbegrenzter Galerie-Speicher',
  'Synchronisation mit externen Kalendern',
  'Zusätzliche Premium-Widgets',
  '100 % werbefrei – heute und immer',
]

export default function PremiumPage() {
  const setUser = useAuth((s) => s.setUser)
  const [sub, setSub] = useState<Subscription | null>(null)
  const [busy, setBusy] = useState(false)
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
      setSub(await subscriptionApi.activate())
      await refreshUser()
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
      <h1 className="text-2xl font-bold text-brand">⭐ Premium</h1>
      {error && <p className="text-sm text-red-600">{error}</p>}

      <div className="rounded-2xl bg-white p-6 shadow">
        <div className="mb-4 flex items-center justify-between">
          <span className="font-semibold text-slate-700">Aktueller Plan</span>
          <span
            className={`rounded-full px-3 py-1 text-sm font-semibold ${
              isPremium ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500'
            }`}
          >
            {isPremium ? 'Premium' : 'Free'}
          </span>
        </div>

        <ul className="space-y-2">
          {BENEFITS.map((b) => (
            <li key={b} className="flex items-center gap-2 text-sm text-slate-600">
              <span className={isPremium ? 'text-brand' : 'text-slate-300'}>✓</span>
              {b}
            </li>
          ))}
        </ul>

        <div className="mt-6">
          {isPremium ? (
            <>
              {sub?.expires_at && (
                <p className="mb-3 text-sm text-slate-500">
                  Läuft bis {new Date(sub.expires_at).toLocaleDateString('de-DE')}
                </p>
              )}
              <button
                onClick={() => void cancel()}
                disabled={busy}
                className="rounded-lg border border-slate-300 px-5 py-2 text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-60"
              >
                Premium kündigen
              </button>
            </>
          ) : (
            <button
              onClick={() => void activate()}
              disabled={busy}
              className="rounded-lg bg-brand px-6 py-3 font-semibold text-white hover:bg-brand-dark disabled:opacity-60"
            >
              {busy ? 'Wird aktiviert …' : 'Premium aktivieren – 2,99 €/Monat'}
            </button>
          )}
        </div>
      </div>

      <p className="text-center text-xs text-slate-400">
        Hinweis: In dieser Entwicklungsversion wird der Kauf simuliert – es erfolgt keine
        echte Zahlung. Später über App Store / Google Play.
      </p>
    </div>
  )
}
