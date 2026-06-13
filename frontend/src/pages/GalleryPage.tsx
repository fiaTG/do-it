import { useEffect, useRef, useState, type FormEvent } from 'react'
import { apiError, imagesApi } from '../api'
import type { ImageItem } from '../types'

export default function GalleryPage() {
  const [images, setImages] = useState<ImageItem[]>([])
  const [title, setTitle] = useState('')
  const [file, setFile] = useState<File | null>(null)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const [lightbox, setLightbox] = useState<string | null>(null)
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
      <h1 className="text-2xl font-bold text-brand">🖼️ Galerie</h1>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <form onSubmit={upload} className="flex flex-wrap items-center gap-3 rounded-2xl bg-white p-4 shadow">
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
          className="rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
        />
        <button
          disabled={busy}
          className="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-dark disabled:opacity-60"
        >
          {busy ? 'Lädt …' : 'Hochladen'}
        </button>
      </form>

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        {images.length === 0 && <p className="text-slate-500">Noch keine Bilder.</p>}
        {images.map((img) => (
          <figure key={img.id} className="group relative overflow-hidden rounded-2xl bg-white shadow">
            <img
              src={img.thumbnail_url}
              alt={img.title ?? ''}
              loading="lazy"
              onClick={() => setLightbox(img.url)}
              className="aspect-square w-full cursor-pointer object-cover"
            />
            <button
              onClick={(e) => {
                e.stopPropagation()
                void remove(img.id)
              }}
              className="absolute right-2 top-2 rounded-full bg-black/40 px-2 text-white opacity-0 transition group-hover:opacity-100"
              aria-label="Löschen"
            >
              ✕
            </button>
            {img.title && (
              <figcaption className="truncate px-2 py-1 text-sm text-slate-600">
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
          <img src={lightbox} alt="" className="max-h-full max-w-full rounded-lg object-contain" />
        </div>
      )}
    </div>
  )
}
