import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiError, fuelApi } from '../api'
import { Crown, Fuel, MapPin, RotateCcw } from '../lib/icons'
import { useAuth } from '../store/auth'
import type { FuelData, FuelStation } from '../types'

type FuelType = 'e5' | 'e10' | 'diesel'

const TYPES: { id: FuelType; label: string }[] = [
  { id: 'diesel', label: 'Diesel' },
  { id: 'e5', label: 'Super (E5)' },
  { id: 'e10', label: 'Super E10' },
]

const RADII = [5, 10, 15, 25]

function price(value: number | false | null): string {
  return typeof value === 'number' ? `${value.toFixed(3).replace('.', ',')} €` : '–'
}

export default function FuelPage() {
  const me = useAuth((s) => s.user)
  const isPremium = me?.family?.is_premium ?? false
  const hasLocation = me?.family?.latitude != null && me?.family?.longitude != null
  const [type, setType] = useState<FuelType>('diesel')
  const [rad, setRad] = useState(5)
  const [data, setData] = useState<FuelData | null>(null)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState('')

  const load = useCallback(async () => {
    setBusy(true)
    setError('')
    try {
      setData(await fuelApi.stations(rad))
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }, [rad])

  // On-Demand gemäß Tankerkönig-Regeln: Laden beim Öffnen der Seite und bei
  // geänderten Filtern (beides Nutzeraktionen) – kein Hintergrund-Polling.
  useEffect(() => {
    if (isPremium && hasLocation) void load()
  }, [isPremium, hasLocation, load])

  if (!isPremium) {
    return (
      <div className="mx-auto max-w-lg space-y-4">
        <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
          <Fuel className="h-6 w-6" /> Spritpreise
        </h1>
        <div className="rounded-2xl bg-surface p-6 text-center shadow">
          <Crown className="mx-auto h-8 w-8 text-primary" />
          <p className="mt-2 font-semibold text-text">Ein Nidula-Premium-Feature</p>
          <p className="mt-1 text-sm text-muted">
            Aktuelle Spritpreise aller Tankstellen rund um euer Zuhause – sortiert nach Preis,
            mit Öffnungsstatus.
          </p>
          <Link
            to="/premium"
            className="mt-4 inline-block rounded-lg bg-primary px-5 py-2 font-semibold text-white hover:bg-primary-hover"
          >
            Mehr zu Premium
          </Link>
        </div>
      </div>
    )
  }

  const stations = [...(data?.stations ?? [])].sort((a, b) => {
    const pa = a[type]
    const pb = b[type]
    if (typeof pa !== 'number') return 1
    if (typeof pb !== 'number') return -1
    return pa - pb
  })

  return (
    <div className="mx-auto max-w-2xl space-y-4">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <Fuel className="h-6 w-6" /> Spritpreise
      </h1>

      {!hasLocation ? (
        <p className="rounded-2xl bg-surface p-4 text-sm text-muted shadow">
          Bitte zuerst den{' '}
          <Link to="/members" className="font-semibold text-primary hover:underline">
            Familienort festlegen
          </Link>{' '}
          – er bestimmt die Tankstellen-Umgebung.
        </p>
      ) : (
        <>
          <div className="flex flex-wrap items-center gap-2 text-sm">
            {TYPES.map((t) => (
              <button
                key={t.id}
                onClick={() => setType(t.id)}
                className={`rounded-full px-3 py-1 font-semibold ${
                  type === t.id ? 'bg-primary text-white' : 'bg-surface-2 text-muted hover:text-primary'
                }`}
              >
                {t.label}
              </button>
            ))}
            <select
              value={rad}
              onChange={(e) => setRad(Number(e.target.value))}
              className="rounded-lg border border-border px-2 py-1 outline-none focus:border-primary"
              aria-label="Suchradius"
            >
              {RADII.map((r) => (
                <option key={r} value={r}>
                  {r} km
                </option>
              ))}
            </select>
            <button
              onClick={() => void load()}
              disabled={busy}
              aria-label="Preise aktualisieren"
              className="flex h-8 w-8 items-center justify-center rounded-full bg-surface-2 text-muted hover:text-primary disabled:opacity-60"
            >
              <RotateCcw className={`h-4 w-4 ${busy ? 'animate-spin' : ''}`} />
            </button>
          </div>

          {error && <p className="text-sm text-red-600">{error}</p>}
          {busy && !data && <p className="text-sm text-muted">Lädt Preise …</p>}

          {data && stations.length === 0 && !error && (
            <p className="text-sm text-muted">Keine Tankstellen im Umkreis gefunden.</p>
          )}

          <ul className="space-y-2">
            {stations.map((s: FuelStation, i) => (
              <li
                key={s.id}
                className={`flex items-center gap-3 rounded-2xl bg-surface p-4 shadow ${
                  i === 0 ? 'ring-1 ring-primary' : ''
                }`}
              >
                <div className="min-w-0 flex-1">
                  <p className="truncate font-semibold text-text">
                    {s.brand || s.name}
                    {!s.isOpen && (
                      <span className="ml-2 rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                        geschlossen
                      </span>
                    )}
                  </p>
                  <p className="flex items-center gap-1 truncate text-xs text-muted">
                    <MapPin className="h-3 w-3 shrink-0" />
                    {s.street} {s.houseNumber ?? ''}, {s.place} · {s.dist.toFixed(1)} km
                  </p>
                </div>
                <span className={`text-lg font-bold ${i === 0 ? 'text-primary' : 'text-text'}`}>
                  {price(s[type])}
                </span>
              </li>
            ))}
          </ul>

          {data && (
            <p className="text-center text-xs text-muted">
              Stand {new Date(data.fetched_at).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })}{' '}
              Uhr · Daten:{' '}
              <a
                href="https://www.tankerkoenig.de"
                target="_blank"
                rel="noreferrer"
                className="underline hover:text-primary"
              >
                Tankerkönig.de
              </a>{' '}
              (MTS-K) · Lizenz CC BY 4.0
            </p>
          )}
        </>
      )}
    </div>
  )
}
