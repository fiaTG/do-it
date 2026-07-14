import { useCallback, useEffect, useMemo, useRef, useState, type FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { apiError, imagesApi } from '../api'
import { ChevronLeft, ChevronRight, Download, ImageIcon, Trash2, X } from '../lib/icons'
import type { ImageItem } from '../types'

interface ImageGroup {
  label: string
  items: { img: ImageItem; index: number }[]
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
  const [file, setFile] = useState<File | null>(null)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null)
  const fileInput = useRef<HTMLInputElement>(null)
  const sentinelRef = useRef<HTMLDivElement>(null)
  const refreshedIds = useRef<Set<number>>(new Set())

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

  async function upload(e: FormEvent) {
    e.preventDefault()
    if (!file) return
    setError('')
    setBusy(true)
    try {
      await imagesApi.upload(file, title)
      setTitle('')
      setFile(null)
      if (fileInput.current) fileInput.current.value = ''
      await loadFirstPage()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function remove(img: ImageItem) {
    const confirmed = window.confirm(
      img.title ? `„${img.title}" wirklich löschen?` : 'Dieses Bild wirklich löschen?',
    )
    if (!confirmed) return

    try {
      await imagesApi.remove(img.id)
      setImages((prev) => prev.filter((i) => i.id !== img.id))
      setTotal((prev) => Math.max(0, prev - 1))
      setLightboxIndex(null)
    } catch (err) {
      setError(apiError(err))
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
        <form onSubmit={upload} className="flex flex-wrap items-center gap-3 rounded-2xl bg-surface p-4 shadow">
          <input
            ref={fileInput}
            type="file"
            accept="image/*"
            required
            onChange={(e) => setFile(e.target.files?.[0] ?? null)}
            className="text-sm"
          />
          <input
            placeholder="Titel (optional)"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
          />
          <button
            disabled={busy}
            className="rounded-lg bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-hover disabled:opacity-60"
          >
            {busy ? 'Lädt …' : 'Hochladen'}
          </button>
        </form>
      )}

      {images.length === 0 && !error && <p className="text-muted">Noch keine Bilder.</p>}

      {groups.map((group) => (
        <section key={group.label} className="space-y-2">
          <h2 className="text-sm font-semibold text-muted">{group.label}</h2>
          <div className="columns-2 gap-3 sm:columns-3 lg:columns-4">
            {group.items.map(({ img, index }) => (
              <figure
                key={img.id}
                className="group relative mb-3 break-inside-avoid overflow-hidden rounded-2xl bg-surface shadow"
              >
                <img
                  src={img.thumbnail_url}
                  alt={img.title ?? ''}
                  loading="lazy"
                  width={img.width ?? undefined}
                  height={img.height ?? undefined}
                  style={img.width && img.height ? { aspectRatio: `${img.width} / ${img.height}` } : undefined}
                  onClick={() => setLightboxIndex(index)}
                  onError={() => void handleImageError(img)}
                  className="w-full cursor-pointer object-cover"
                />
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
