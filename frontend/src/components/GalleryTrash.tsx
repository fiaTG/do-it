import { useEffect, useState } from 'react'
import { apiError, imagesApi } from '../api'
import { RotateCcw, Trash2 } from '../lib/icons'
import type { ImageItem } from '../types'

/** Resttage bis zum endgültigen Löschen (ADR-0020: 30 Tage Aufbewahrung). */
function daysLeft(img: ImageItem): number {
  if (!img.expires_at) return 0
  return Math.max(0, Math.ceil((new Date(img.expires_at).getTime() - Date.now()) / 86_400_000))
}

/**
 * Papierkorb der Galerie: gelöschte Bilder wiederherstellen oder endgültig
 * entfernen. Wird bei jedem Öffnen frisch gemountet und lädt dann selbst.
 */
export default function GalleryTrash({ onChanged }: { onChanged: () => void }) {
  const [items, setItems] = useState<ImageItem[]>([])
  const [loaded, setLoaded] = useState(false)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState('')

  async function load() {
    try {
      setItems(await imagesApi.trash())
    } catch (err) {
      setError(apiError(err))
    } finally {
      setLoaded(true)
    }
  }

  useEffect(() => {
    void load()
  }, [])

  async function restore(ids: number[]) {
    if (busy) return
    setBusy(true)
    setError('')
    try {
      await imagesApi.restore(ids)
      setItems((prev) => prev.filter((i) => !ids.includes(i.id)))
      onChanged()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function purge(ids: number[], message: string) {
    if (busy || !window.confirm(message)) return
    setBusy(true)
    setError('')
    try {
      await imagesApi.purge(ids)
      setItems((prev) => prev.filter((i) => !ids.includes(i.id)))
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="space-y-3 rounded-2xl bg-surface p-4 shadow">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <p className="text-sm text-muted">
          Bilder bleiben 30 Tage im Papierkorb und werden dann endgültig gelöscht.
        </p>
        {items.length > 0 && (
          <button
            onClick={() =>
              void purge(items.map((i) => i.id), 'Papierkorb wirklich leeren? Das kann nicht rückgängig gemacht werden.')
            }
            disabled={busy}
            className="rounded-lg bg-surface-2 px-3 py-1.5 text-sm font-semibold text-muted hover:text-red-600 disabled:opacity-60"
          >
            Papierkorb leeren
          </button>
        )}
      </div>

      {error && <p className="text-sm text-red-600">{error}</p>}
      {loaded && items.length === 0 && <p className="text-sm text-muted">Der Papierkorb ist leer.</p>}

      <ul className="space-y-2">
        {items.map((img) => (
          <li key={img.id} className="flex items-center gap-3 rounded-xl bg-surface-2 p-2">
            <img
              src={img.thumbnail_url}
              alt={img.title ?? ''}
              loading="lazy"
              className="h-14 w-14 shrink-0 rounded-lg object-cover"
            />
            <div className="min-w-0 flex-1">
              <p className="truncate text-sm font-semibold">{img.title ?? 'Ohne Titel'}</p>
              <p className="text-xs text-muted">
                {daysLeft(img) === 0 ? 'Wird bald endgültig gelöscht' : `Noch ${daysLeft(img)} Tage`}
              </p>
            </div>
            <button
              onClick={() => void restore([img.id])}
              disabled={busy}
              className="flex items-center gap-1.5 rounded-lg bg-surface px-3 py-1.5 text-sm font-semibold text-primary hover:bg-primary hover:text-white disabled:opacity-60"
            >
              <RotateCcw className="h-4 w-4" /> Wiederherstellen
            </button>
            <button
              onClick={() =>
                void purge(
                  [img.id],
                  img.title ? `„${img.title}" endgültig löschen?` : 'Dieses Bild endgültig löschen?',
                )
              }
              disabled={busy}
              aria-label="Endgültig löschen"
              className="flex h-8 w-8 items-center justify-center rounded-lg bg-surface text-muted hover:bg-red-600 hover:text-white disabled:opacity-60"
            >
              <Trash2 className="h-4 w-4" />
            </button>
          </li>
        ))}
      </ul>
    </div>
  )
}
