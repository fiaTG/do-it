import { useState, type FormEvent } from 'react'
import { apiError, calendarFeedsApi } from '../api'
import { Globe, RotateCcw, Trash2, Upload } from '../lib/icons'
import { MEMBER_PALETTE } from '../lib/memberColors'
import type { CalendarFeed } from '../types'

interface Props {
  feeds: CalendarFeed[]
  onClose: () => void
  /** Nach jeder Änderung: Feeds + Termine neu laden (macht die CalendarPage). */
  onChanged: () => Promise<void>
}

function formatSynced(iso: string | null): string {
  if (!iso) return 'noch nie'
  return new Date(iso).toLocaleString('de-DE', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

/**
 * Verwaltung der Kalender-Abos (ADR-0023, Premium, nur Verwalter): externe
 * iCal-Kalender per URL abonnieren oder als .ics-Datei einmalig importieren.
 */
export default function CalendarFeedManager({ feeds, onClose, onChanged }: Props) {
  const [mode, setMode] = useState<'url' | 'file'>('url')
  const [name, setName] = useState('')
  const [color, setColor] = useState(MEMBER_PALETTE[4])
  const [url, setUrl] = useState('')
  const [file, setFile] = useState<File | null>(null)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState('')

  const inputClass =
    'rounded-lg border border-border px-3 py-2 text-sm outline-none focus:border-primary disabled:opacity-60'

  async function add(e: FormEvent) {
    e.preventDefault()
    setBusy(true)
    setError('')
    try {
      await calendarFeedsApi.create({
        name,
        color,
        url: mode === 'url' ? url : undefined,
        file: mode === 'file' ? file : undefined,
      })
      setName('')
      setUrl('')
      setFile(null)
      await onChanged()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function refresh(feed: CalendarFeed) {
    setBusy(true)
    setError('')
    try {
      await calendarFeedsApi.refresh(feed.id)
      await onChanged()
    } catch (err) {
      setError(apiError(err))
      // Der Fehler steht jetzt auch am Feed (last_error) – Liste aktualisieren.
      await onChanged().catch(() => {})
    } finally {
      setBusy(false)
    }
  }

  async function remove(feed: CalendarFeed) {
    setBusy(true)
    setError('')
    try {
      await calendarFeedsApi.remove(feed.id)
      await onChanged()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="max-h-[90vh] w-full max-w-md space-y-4 overflow-y-auto rounded-2xl bg-surface p-6 shadow-xl">
        <h2 className="flex items-center gap-2 text-lg font-semibold text-primary">
          <Globe className="h-5 w-5" /> Kalender-Abos
        </h2>
        <p className="text-xs text-muted">
          Externe Kalender (Schule, Verein, Abfallkalender …) erscheinen als eigene Ebene im
          Familienkalender – nur lesbar, Abo löschen entfernt alle Termine wieder.
        </p>
        {error && <p className="text-sm text-red-600">{error}</p>}

        {feeds.length > 0 && (
          <ul className="space-y-2">
            {feeds.map((f) => (
              <li key={f.id} className="rounded-xl border border-border p-3">
                <div className="flex items-center gap-2">
                  <span
                    className="h-3 w-3 shrink-0 rounded-full"
                    style={{ background: f.color }}
                  />
                  <span className="min-w-0 flex-1 truncate text-sm font-semibold text-text">
                    {f.name}
                  </span>
                  {f.is_subscription && (
                    <button
                      type="button"
                      onClick={() => void refresh(f)}
                      disabled={busy}
                      className="rounded-lg p-1.5 text-muted hover:bg-surface-2 disabled:opacity-60"
                      title="Jetzt aktualisieren"
                    >
                      <RotateCcw className="h-4 w-4" />
                    </button>
                  )}
                  <button
                    type="button"
                    onClick={() => void remove(f)}
                    disabled={busy}
                    className="rounded-lg p-1.5 text-red-500 hover:bg-surface-2 disabled:opacity-60"
                    title="Abo löschen"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
                <p className="mt-1 text-xs text-muted">
                  {f.is_subscription
                    ? `Abo · aktualisiert: ${formatSynced(f.last_synced_at)}`
                    : 'Datei-Import (einmalig)'}
                </p>
                {f.last_error && <p className="mt-1 text-xs text-red-600">{f.last_error}</p>}
              </li>
            ))}
          </ul>
        )}

        <form onSubmit={add} className="space-y-3 rounded-xl border border-border p-3">
          <p className="text-sm font-semibold text-text">Kalender hinzufügen</p>
          <div className="inline-flex rounded-lg border border-border bg-surface p-0.5 text-xs">
            <button
              type="button"
              onClick={() => setMode('url')}
              className={`rounded-md px-3 py-1.5 ${mode === 'url' ? 'bg-primary text-white' : 'text-muted'}`}
            >
              URL abonnieren
            </button>
            <button
              type="button"
              onClick={() => setMode('file')}
              className={`rounded-md px-3 py-1.5 ${mode === 'file' ? 'bg-primary text-white' : 'text-muted'}`}
            >
              .ics-Datei
            </button>
          </div>
          <input
            placeholder="Name (z. B. Schule, Abfallkalender)"
            required
            maxLength={100}
            value={name}
            onChange={(e) => setName(e.target.value)}
            className={`${inputClass} w-full`}
          />
          {mode === 'url' ? (
            <input
              placeholder="https://… oder webcal://… (.ics-Adresse)"
              required
              value={url}
              onChange={(e) => setUrl(e.target.value)}
              className={`${inputClass} w-full`}
            />
          ) : (
            <label className="flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-border px-3 py-2 text-sm text-muted hover:bg-surface-2">
              <Upload className="h-4 w-4 shrink-0" />
              <span className="min-w-0 truncate">{file ? file.name : '.ics-Datei wählen …'}</span>
              <input
                type="file"
                accept=".ics,text/calendar"
                required
                onChange={(e) => setFile(e.target.files?.[0] ?? null)}
                className="hidden"
              />
            </label>
          )}
          <div className="flex flex-wrap items-center gap-1.5 text-xs text-muted">
            Farbe:
            {MEMBER_PALETTE.map((c) => (
              <button
                key={c}
                type="button"
                onClick={() => setColor(c)}
                aria-label={`Farbe ${c}`}
                className={`h-6 w-6 rounded-full border-2 ${color === c ? 'border-text' : 'border-transparent'}`}
                style={{ background: c }}
              />
            ))}
          </div>
          <button
            disabled={busy}
            className="w-full rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-hover disabled:opacity-60"
          >
            {busy ? 'Wird geprüft …' : 'Hinzufügen'}
          </button>
        </form>

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
