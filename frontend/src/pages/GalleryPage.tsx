import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { apiError, imagesApi } from '../api'
import {
  Check,
  CheckSquare,
  ChevronLeft,
  ChevronRight,
  Download,
  ImageIcon,
  Trash2,
  Upload,
  X,
} from '../lib/icons'
import type { ImageItem } from '../types'

interface ImageGroup {
  label: string
  items: { img: ImageItem; index: number }[]
}

interface UploadEntry {
  id: number
  name: string
  percent: number
  status: 'pending' | 'uploading' | 'done' | 'error'
  error?: string
}

/** Aufnahmedatum, falls bekannt (EXIF) – sonst Fallback aufs Upload-Datum. */
function effectiveDate(img: ImageItem): Date {
  return new Date(img.taken_at ?? img.created_at)
}

function startOfDay(date: Date): number {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime()
}

function groupLabel(date: Date): string {
  const days = Math.round((startOfDay(new Date()) - startOfDay(date)) / 86_400_000)
  if (days === 0) return 'Heute'
  if (days === 1) return 'Gestern'
  return date.toLocaleDateString('de-DE', { month: 'long', year: 'numeric' })
}

/** Bilder kommen server-sortiert (neueste zuerst) – hier nur noch nach Tages-Label bündeln. */
function groupImages(images: ImageItem[]): ImageGroup[] {
  const groups: ImageGroup[] = []
  images.forEach((img, index) => {
    const label = groupLabel(effectiveDate(img))
    const current = groups[groups.length - 1]
    if (current?.label === label) {
      current.items.push({ img, index })
    } else {
      groups.push({ label, items: [{ img, index }] })
    }
  })
  return groups
}

export default function GalleryPage() {
  const [images, setImages] = useState<ImageItem[]>([])
  const [limit, setLimit] = useState<number | null>(null)
  const [total, setTotal] = useState(0)
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [loadingMore, setLoadingMore] = useState(false)
  const [title, setTitle] = useState('')
  const [uploads, setUploads] = useState<UploadEntry[]>([])
  const [dragOver, setDragOver] = useState(false)
  const [selectMode, setSelectMode] = useState(false)
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set())
  const [deleting, setDeleting] = useState(false)
  const [error, setError] = useState('')
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null)
  const fileInput = useRef<HTMLInputElement>(null)
  const sentinelRef = useRef<HTMLDivElement>(null)
  const refreshedIds = useRef<Set<number>>(new Set())
  const uploadQueue = useRef<{ entryId: number; file: File; title: string }[]>([])
  const processingQueue = useRef(false)
  const nextEntryId = useRef(1)
  // Eingereihte, noch nicht abgeschlossene Uploads – total im State hinkt bis
  // zum Reload hinterher, fürs Quota-Gate zählen laufende Uploads darum mit.
  const inFlight = useRef(0)

  async function loadFirstPage() {
    try {
      const result = await imagesApi.list(1)
      setImages(result.images)
      setLimit(result.limit)
      setTotal(result.total)
      setPage(result.currentPage)
      setLastPage(result.lastPage)
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void loadFirstPage()
  }, [])

  const loadMore = useCallback(async () => {
    if (loadingMore || page >= lastPage) return
    setLoadingMore(true)
    try {
      const result = await imagesApi.list(page + 1)
      setImages((prev) => [...prev, ...result.images])
      setPage(result.currentPage)
      setLastPage(result.lastPage)
    } catch {
      // Stiller Fehlschlag: erneutes Scrollen versucht es wieder.
    } finally {
      setLoadingMore(false)
    }
  }, [loadingMore, page, lastPage])

  // Re-observed bei jeder Bildanzahl-Änderung, damit auch der Fall abgedeckt ist,
  // dass die geladenen Bilder den Viewport nicht füllen und der Sentinel schon
  // vor dem Laden durchgehend sichtbar war (IntersectionObserver feuert sonst
  // nur bei einem Sichtbarkeits-*Wechsel*, nicht bei Datenänderungen).
  useEffect(() => {
    const sentinel = sentinelRef.current
    if (!sentinel) return
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0]?.isIntersecting) void loadMore()
      },
      { rootMargin: '400px' },
    )
    observer.observe(sentinel)
    return () => observer.disconnect()
  }, [loadMore, images.length])

  function handleFiles(dropped: File[]) {
    const files = dropped.filter((file) => file.type.startsWith('image/'))
    if (files.length === 0) return
    // Nur so viele Dateien einreihen, wie im Kontingent noch frei sind – der
    // Rest wird sofort als Fehler markiert statt eine 4xx-Antwort abzuwarten.
    const freeSlots =
      limit === null ? files.length : Math.max(0, limit - total - inFlight.current)
    const entries: UploadEntry[] = []
    files.forEach((file, i) => {
      const id = nextEntryId.current++
      if (i < freeSlots) {
        entries.push({ id, name: file.name, percent: 0, status: 'pending' })
        uploadQueue.current.push({ entryId: id, file, title })
        inFlight.current++
      } else {
        entries.push({ id, name: file.name, percent: 0, status: 'error', error: 'Galerie-Limit erreicht' })
      }
    })
    setUploads((prev) => [...prev, ...entries])
    void processQueue()
  }

  async function processQueue() {
    if (processingQueue.current) return
    processingQueue.current = true
    let uploaded = 0
    while (uploadQueue.current.length > 0) {
      const item = uploadQueue.current.shift()
      if (!item) break
      setUploads((prev) => prev.map((u) => (u.id === item.entryId ? { ...u, status: 'uploading' } : u)))
      try {
        await imagesApi.upload(item.file, item.title, (percent) => {
          setUploads((prev) => prev.map((u) => (u.id === item.entryId ? { ...u, percent } : u)))
        })
        uploaded++
        setUploads((prev) =>
          prev.map((u) => (u.id === item.entryId ? { ...u, status: 'done', percent: 100 } : u)),
        )
      } catch (err) {
        setUploads((prev) =>
          prev.map((u) => (u.id === item.entryId ? { ...u, status: 'error', error: apiError(err) } : u)),
        )
        // Fehlgeschlagener Upload belegt keinen Platz mehr.
        inFlight.current--
      }
    }
    processingQueue.current = false
    if (uploaded > 0) {
      setTitle('')
      await loadFirstPage()
      // Erst NACH dem Reload freigeben: bis dahin steckt der belegte Platz
      // weder im (veralteten) total noch wäre er sonst im Gate mitgezählt.
      inFlight.current -= uploaded
      // Fertige Einträge ausblenden, fehlgeschlagene sichtbar lassen.
      setUploads((prev) => prev.filter((u) => u.status !== 'done'))
    }
  }

  async function remove(img: ImageItem) {
    const confirmed = window.confirm(
      img.title ? `„${img.title}" wirklich löschen?` : 'Dieses Bild wirklich löschen?',
    )
    if (!confirmed) return

    try {
      await imagesApi.remove(img.id)
      // Sofort ausblenden, dann Serverstand nachladen: die Offset-Pagination
      // verschiebt sich durch Löschungen, sonst überspränge loadMore Bilder.
      setImages((prev) => prev.filter((i) => i.id !== img.id))
      setLightboxIndex(null)
      await loadFirstPage()
    } catch (err) {
      setError(apiError(err))
    }
  }

  function toggleSelect(id: number) {
    setSelectedIds((prev) => {
      const next = new Set(prev)
      if (next.has(id)) {
        next.delete(id)
      } else {
        next.add(id)
      }
      return next
    })
  }

  function cancelSelect() {
    setSelectMode(false)
    setSelectedIds(new Set())
  }

  async function removeSelected() {
    if (deleting) return
    const ids = Array.from(selectedIds)
    if (ids.length === 0) return
    const confirmed = window.confirm(
      ids.length === 1 ? 'Dieses Bild wirklich löschen?' : `${ids.length} Bilder wirklich löschen?`,
    )
    if (!confirmed) return

    setDeleting(true)
    try {
      await imagesApi.batchRemove(ids)
      // Sofort ausblenden, dann Serverstand nachladen: die Offset-Pagination
      // verschiebt sich durch Löschungen, sonst überspränge loadMore Bilder –
      // und total stimmt danach garantiert wieder mit dem Server überein.
      setImages((prev) => prev.filter((i) => !selectedIds.has(i.id)))
      setLightboxIndex(null)
      cancelSelect()
      await loadFirstPage()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setDeleting(false)
    }
  }

  // Signierte URLs laufen nach 60 Min ab (ADR-0015) – bei einer wer offen
  // gelassenen Galerie holt sich das kaputte Bild einmalig eine frische URL.
  async function handleImageError(img: ImageItem) {
    if (refreshedIds.current.has(img.id)) return
    refreshedIds.current.add(img.id)
    try {
      const fresh = await imagesApi.show(img.id)
      setImages((prev) => prev.map((i) => (i.id === img.id ? fresh : i)))
    } catch {
      // Bild existiert nicht mehr o. ä. -> bleibt kaputt, kein weiterer Versuch.
    }
  }

  async function download(img: ImageItem) {
    try {
      const res = await fetch(img.url)
      const blob = await res.blob()
      const href = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = href
      a.download = img.title || `nidula-${img.id}.jpg`
      a.click()
      URL.revokeObjectURL(href)
    } catch {
      setError('Download fehlgeschlagen.')
    }
  }

  const groups = useMemo(() => groupImages(images), [images])
  const quotaReached = limit !== null && total >= limit

  useEffect(() => {
    if (lightboxIndex === null) return
    function onKeyDown(e: KeyboardEvent) {
      if (e.key === 'Escape') setLightboxIndex(null)
      else if (e.key === 'ArrowLeft') setLightboxIndex((i) => (i !== null && i > 0 ? i - 1 : i))
      else if (e.key === 'ArrowRight') {
        setLightboxIndex((i) => (i !== null && i < images.length - 1 ? i + 1 : i))
      }
    }
    window.addEventListener('keydown', onKeyDown)
    return () => window.removeEventListener('keydown', onKeyDown)
  }, [lightboxIndex, images.length])

  const lightboxImage = lightboxIndex !== null ? images[lightboxIndex] : undefined

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
          <ImageIcon className="h-6 w-6" /> Galerie
        </h1>
        <div className="flex flex-wrap items-center gap-2">
          {images.length > 0 && !selectMode && (
            <button
              onClick={() => {
                setSelectedIds(new Set())
                setSelectMode(true)
              }}
              className="flex items-center gap-2 rounded-lg bg-surface-2 px-3 py-1.5 text-sm font-semibold text-muted hover:text-primary"
            >
              <CheckSquare className="h-4 w-4" /> Auswählen
            </button>
          )}
          {limit !== null && (
            <span
              className={`rounded-full px-3 py-1 text-sm font-semibold ${
                quotaReached ? 'bg-primary text-white' : 'bg-surface-2 text-muted'
              }`}
            >
              {total}/{limit} Bilder
            </span>
          )}
        </div>
      </div>

      {selectMode && (
        <div className="flex flex-wrap items-center gap-3 rounded-2xl bg-surface p-3 shadow">
          <span className="text-sm font-semibold">{selectedIds.size} ausgewählt</span>
          <button
            onClick={() => void removeSelected()}
            disabled={selectedIds.size === 0 || deleting}
            className="flex items-center gap-2 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
          >
            <Trash2 className="h-4 w-4" /> {deleting ? 'Löscht …' : 'Löschen'}
          </button>
          <button
            onClick={cancelSelect}
            className="flex items-center gap-2 rounded-lg bg-surface-2 px-3 py-1.5 text-sm font-semibold text-muted hover:text-primary"
          >
            <X className="h-4 w-4" /> Abbrechen
          </button>
        </div>
      )}

      {error && <p className="text-sm text-red-600">{error}</p>}

      {quotaReached ? (
        <p className="rounded-2xl bg-surface p-4 text-sm text-muted shadow">
          Galerie-Limit erreicht.{' '}
          <Link to="/premium" className="font-semibold text-primary hover:underline">
            Mit Premium unbegrenzt speichern
          </Link>
          .
        </p>
      ) : (
        <div className="space-y-3 rounded-2xl bg-surface p-4 shadow">
          <div
            role="button"
            tabIndex={0}
            onClick={() => fileInput.current?.click()}
            onKeyDown={(e) => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault()
                fileInput.current?.click()
              }
            }}
            onDragOver={(e) => {
              e.preventDefault()
              setDragOver(true)
            }}
            onDragLeave={(e) => {
              // Dragleave feuert auch beim Wechsel auf Kind-Elemente – nur beim
              // echten Verlassen der Zone den Hover-Zustand zurücksetzen.
              if (!e.currentTarget.contains(e.relatedTarget as Node | null)) setDragOver(false)
            }}
            onDrop={(e) => {
              e.preventDefault()
              setDragOver(false)
              handleFiles(Array.from(e.dataTransfer.files))
            }}
            className={`flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed p-6 text-center transition ${
              dragOver ? 'border-primary bg-surface-2' : 'border-border'
            }`}
          >
            <Upload className="h-6 w-6 text-muted" />
            <p className="text-sm text-muted">Bilder hierher ziehen oder klicken zum Auswählen</p>
          </div>
          <input
            ref={fileInput}
            type="file"
            accept="image/*"
            multiple
            onChange={(e) => {
              handleFiles(Array.from(e.target.files ?? []))
              e.target.value = ''
            }}
            className="hidden"
          />
          <input
            placeholder="Titel (optional, gilt für alle Dateien)"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="w-full rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
          />
        </div>
      )}

      {uploads.length > 0 && (
        <ul className="space-y-2 rounded-2xl bg-surface p-4 shadow">
          {uploads.map((u) => (
            <li key={u.id} className="text-sm">
              <div className="flex items-center justify-between gap-3">
                <span className="truncate">{u.name}</span>
                {u.status === 'error' ? (
                  <span className="shrink-0 text-red-600">{u.error}</span>
                ) : (
                  <span className="shrink-0 text-muted">{u.percent} %</span>
                )}
              </div>
              {u.status !== 'error' && (
                <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-surface-2">
                  <div
                    className="h-full rounded-full bg-primary transition-[width]"
                    style={{ width: `${u.percent}%` }}
                  />
                </div>
              )}
            </li>
          ))}
        </ul>
      )}

      {images.length === 0 && !error && <p className="text-muted">Noch keine Bilder.</p>}

      {groups.map((group) => (
        <section key={group.label} className="space-y-2">
          <h2 className="text-sm font-semibold text-muted">{group.label}</h2>
          <div className="columns-2 gap-3 sm:columns-3 lg:columns-4">
            {group.items.map(({ img, index }) => (
              <figure
                key={img.id}
                className={`group relative mb-3 break-inside-avoid overflow-hidden rounded-2xl bg-surface shadow ${
                  selectMode && selectedIds.has(img.id) ? 'ring-2 ring-primary' : ''
                }`}
              >
                <img
                  src={img.thumbnail_url}
                  alt={img.title ?? ''}
                  loading="lazy"
                  width={img.width ?? undefined}
                  height={img.height ?? undefined}
                  style={img.width && img.height ? { aspectRatio: `${img.width} / ${img.height}` } : undefined}
                  onClick={() => (selectMode ? toggleSelect(img.id) : setLightboxIndex(index))}
                  onError={() => void handleImageError(img)}
                  className="w-full cursor-pointer object-cover"
                />
                {selectMode && (
                  <span
                    className={`pointer-events-none absolute left-2 top-2 flex h-6 w-6 items-center justify-center rounded-full ${
                      selectedIds.has(img.id) ? 'bg-primary text-white' : 'border border-white bg-black/40'
                    }`}
                    aria-hidden="true"
                  >
                    {selectedIds.has(img.id) && <Check className="h-4 w-4" />}
                  </span>
                )}
                {!selectMode && (
                  <button
                    onClick={(e) => {
                      e.stopPropagation()
                      void remove(img)
                    }}
                    className="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/40 text-white opacity-0 transition group-hover:opacity-100"
                    aria-label="Löschen"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                )}
                {img.title && (
                  <figcaption className="truncate px-2 py-1 text-sm text-muted">{img.title}</figcaption>
                )}
              </figure>
            ))}
          </div>
        </section>
      ))}

      <div ref={sentinelRef} className="h-1" />
      {loadingMore && <p className="text-center text-sm text-muted">Lädt weitere Bilder …</p>}

      {lightboxIndex !== null && lightboxImage && (
        <div
          onClick={() => setLightboxIndex(null)}
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
        >
          <button
            onClick={(e) => {
              e.stopPropagation()
              setLightboxIndex(null)
            }}
            className="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/60"
            aria-label="Schließen"
          >
            <X className="h-5 w-5" />
          </button>

          {lightboxIndex > 0 && (
            <button
              onClick={(e) => {
                e.stopPropagation()
                setLightboxIndex((i) => (i !== null ? i - 1 : i))
              }}
              className="absolute left-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/60 sm:left-4"
              aria-label="Vorheriges Bild"
            >
              <ChevronLeft className="h-6 w-6" />
            </button>
          )}
          {lightboxIndex < images.length - 1 && (
            <button
              onClick={(e) => {
                e.stopPropagation()
                setLightboxIndex((i) => (i !== null ? i + 1 : i))
              }}
              className="absolute right-2 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/60 sm:right-4"
              aria-label="Nächstes Bild"
            >
              <ChevronRight className="h-6 w-6" />
            </button>
          )}

          <img
            src={lightboxImage.url}
            srcSet={lightboxImage.srcset.map((v) => `${v.url} ${v.width}w`).join(', ')}
            sizes="100vw"
            alt={lightboxImage.title ?? ''}
            onClick={(e) => e.stopPropagation()}
            onError={() => void handleImageError(lightboxImage)}
            className="max-h-full max-w-full rounded-lg object-contain"
          />

          <div
            onClick={(e) => e.stopPropagation()}
            className="absolute bottom-4 flex gap-2"
          >
            <button
              onClick={() => void download(lightboxImage)}
              className="flex items-center gap-2 rounded-full bg-black/40 px-4 py-2 text-sm text-white hover:bg-black/60"
            >
              <Download className="h-4 w-4" /> Herunterladen
            </button>
            <button
              onClick={() => void remove(lightboxImage)}
              className="flex items-center gap-2 rounded-full bg-black/40 px-4 py-2 text-sm text-white hover:bg-red-600/80"
            >
              <Trash2 className="h-4 w-4" /> Löschen
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
