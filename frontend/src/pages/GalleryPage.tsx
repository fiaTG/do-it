import { useEffect, useRef, useState, type FormEvent } from 'react'
import { apiError, imagesApi } from '../api'
import { ImageIcon, Trash2 } from '../lib/icons'
import type { ImageItem } from '../types'

export default function GalleryPage() {
  const [images, setImages] = useState<ImageItem[]>([])
  const [title, setTitle] = useState('')
  const [file, setFile] = useState<File | null>(null)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const [lightbox, setLightbox] = useState<ImageItem | null>(null)
  const fileInput = useRef<HTMLInputElement>(null)

  async function load() {
    try {
      setImages(await imagesApi.list())
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

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
      await load()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function remove(id: number) {
    await imagesApi.remove(id)
    await load()
  }

  return (
    <div className="space-y-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <ImageIcon className="h-6 w-6" /> Galerie
      </h1>

      {error && <p className="text-sm text-red-600">{error}</p>}

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

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        {images.length === 0 && <p className="text-muted">Noch keine Bilder.</p>}
        {images.map((img) => (
          <figure key={img.id} className="group relative overflow-hidden rounded-2xl bg-surface shadow">
            <img
              src={img.thumbnail_url}
              alt={img.title ?? ''}
              onClick={() => setLightbox(img)}
              className="aspect-square w-full cursor-pointer object-cover"
            />
            <button
              onClick={(e) => {
                e.stopPropagation()
                void remove(img.id)
              }}
              className="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-black/40 text-white opacity-0 transition group-hover:opacity-100"
              aria-label="Löschen"
            >
              <Trash2 className="h-4 w-4" />
            </button>
            {img.title && (
              <figcaption className="truncate px-2 py-1 text-sm text-muted">
                {img.title}
              </figcaption>
            )}
          </figure>
        ))}
      </div>

      {lightbox && (
        <div
          onClick={() => setLightbox(null)}
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
        >
          <img
            src={lightbox.url}
            srcSet={lightbox.srcset.map((v) => `${v.url} ${v.width}w`).join(', ')}
            sizes="100vw"
            alt={lightbox.title ?? ''}
            className="max-h-full max-w-full rounded-lg object-contain"
          />
        </div>
      )}
    </div>
  )
}
