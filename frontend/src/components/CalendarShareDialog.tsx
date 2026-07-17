import { useEffect, useState } from 'react'
import { apiError, calendarExportApi, type CalendarExportState } from '../api'
import { Check, Copy, RotateCcw, Share2 } from '../lib/icons'
import { useAuth } from '../store/auth'

/**
 * Kalender-Freigabe (ADR-0024, Premium): geheime .ics-Abo-URL fürs Abonnieren
 * in Google/Apple/Outlook. Alle Mitglieder sehen und kopieren die Adresse
 * (jedes Handy soll abonnieren), Verwalter aktivieren/rotieren/beenden.
 */
export default function CalendarShareDialog({ onClose }: { onClose: () => void }) {
  const isGuardian = useAuth((s) => s.user?.role === 'guardian')
  const [state, setState] = useState<CalendarExportState | null>(null)
  const [busy, setBusy] = useState(false)
  const [copied, setCopied] = useState(false)
  const [error, setError] = useState('')

  useEffect(() => {
    calendarExportApi
      .show()
      .then(setState)
      .catch((err) => setError(apiError(err)))
  }, [])

  async function rotate() {
    setBusy(true)
    setError('')
    try {
      setState(await calendarExportApi.rotate())
      setCopied(false)
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function disable() {
    setBusy(true)
    setError('')
    try {
      await calendarExportApi.disable()
      setState({ enabled: false, url: null })
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function copy() {
    if (!state?.url) return
    try {
      await navigator.clipboard.writeText(state.url)
      setCopied(true)
    } catch {
      setError('Kopieren nicht möglich – bitte die Adresse markieren und manuell kopieren.')
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="w-full max-w-md space-y-4 rounded-2xl bg-surface p-6 shadow-xl">
        <h2 className="flex items-center gap-2 text-lg font-semibold text-primary">
          <Share2 className="h-5 w-5" /> Kalender teilen
        </h2>
        <p className="text-xs text-muted">
          Mit dieser geheimen Adresse abonniert ihr euren Nidula-Kalender in Google, Apple oder
          Outlook – Familientermine (auch Serien) erscheinen direkt im Handy-Kalender.
        </p>
        {error && <p className="text-sm text-red-600">{error}</p>}

        {state === null ? (
          <p className="text-sm text-muted">Lade …</p>
        ) : state.enabled && state.url ? (
          <>
            <div className="flex items-center gap-2">
              <input
                readOnly
                value={state.url}
                onFocus={(e) => e.target.select()}
                className="w-full min-w-0 flex-1 rounded-lg border border-border bg-surface-2 px-3 py-2 text-xs text-text outline-none"
              />
              <button
                type="button"
                onClick={() => void copy()}
                className="flex shrink-0 items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-primary-hover"
              >
                {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                {copied ? 'Kopiert' : 'Kopieren'}
              </button>
            </div>
            <ul className="list-disc space-y-1 pl-4 text-xs text-muted">
              <li>
                <span className="font-semibold">Google Kalender:</span> Einstellungen → „Kalender
                hinzufügen" → „Per URL" → Adresse einfügen.
              </li>
              <li>
                <span className="font-semibold">iPhone/Apple:</span> Einstellungen → Kalender →
                Accounts → „Kalenderabo hinzufügen".
              </li>
              <li>
                Kalender-Apps aktualisieren Abos nur alle paar Stunden – neue Termine erscheinen
                also nicht sofort.
              </li>
              <li>
                Jeder, der diese Adresse kennt, kann eure Termine lesen. Falls sie in falsche
                Hände gerät: neue Adresse erzeugen.
              </li>
            </ul>
            {isGuardian && (
              <div className="flex flex-wrap gap-2">
                <button
                  type="button"
                  onClick={() => void rotate()}
                  disabled={busy}
                  className="flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm text-muted hover:bg-surface-2 disabled:opacity-60"
                >
                  <RotateCcw className="h-4 w-4" /> Neue Adresse erzeugen
                </button>
                <button
                  type="button"
                  onClick={() => void disable()}
                  disabled={busy}
                  className="rounded-lg border border-border px-3 py-2 text-sm text-red-500 hover:bg-surface-2 disabled:opacity-60"
                >
                  Freigabe beenden
                </button>
              </div>
            )}
          </>
        ) : isGuardian ? (
          <button
            type="button"
            onClick={() => void rotate()}
            disabled={busy}
            className="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-hover disabled:opacity-60"
          >
            {busy ? 'Wird aktiviert …' : 'Freigabe aktivieren'}
          </button>
        ) : (
          <p className="text-sm text-muted">
            Die Freigabe ist noch nicht aktiv – ein Verwalter kann sie hier einschalten.
          </p>
        )}

        <div className="flex justify-end">
          <button
            type="button"
            onClick={onClose}
            className="rounded-lg px-4 py-2 text-sm text-muted hover:bg-surface-2"
          >
            Schließen
          </button>
        </div>
      </div>
    </div>
  )
}
