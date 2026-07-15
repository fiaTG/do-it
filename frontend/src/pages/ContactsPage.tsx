import { useEffect, useMemo, useRef, useState, type FormEvent } from 'react'
import { apiError, contactsApi, type ContactPayload } from '../api'
import { BookUser, Globe, Mail, MapPin, Phone, Plus } from '../lib/icons'
import { useAuth } from '../store/auth'
import type { Contact } from '../types'

// Vorschläge fürs Kategorie-Feld – frei überschreibbar (datalist).
const CATEGORY_SUGGESTIONS = ['Arzt', 'Familie', 'Schule & Kita', 'Verein', 'Notfall', 'Handwerker']

const EMPTY: ContactPayload = {
  name: '',
  category: null,
  phone: null,
  email: null,
  website: null,
  address: null,
  notes: null,
}

interface ModalState {
  open: boolean
  id: number | null
  payload: ContactPayload
}

function initials(name: string): string {
  const parts = name.trim().split(/\s+/)
  return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase()
}

export default function ContactsPage() {
  const me = useAuth((s) => s.user)
  const isGuardian = me?.role !== 'child'
  const [contacts, setContacts] = useState<Contact[]>([])
  const [filter, setFilter] = useState<string | null>(null)
  const [modal, setModal] = useState<ModalState>({ open: false, id: null, payload: EMPTY })
  const [photo, setPhoto] = useState<File | null>(null)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState('')
  const photoInput = useRef<HTMLInputElement>(null)

  async function load() {
    try {
      setContacts(await contactsApi.list())
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  const categories = useMemo(
    () => [...new Set(contacts.map((c) => c.category).filter((c): c is string => !!c))].sort(),
    [contacts],
  )
  const visible = filter ? contacts.filter((c) => c.category === filter) : contacts

  const canManage = (c: Contact): boolean => isGuardian || c.created_by === me?.id

  function openCreate() {
    setPhoto(null)
    setModal({ open: true, id: null, payload: EMPTY })
  }

  function openEdit(c: Contact) {
    setPhoto(null)
    setModal({
      open: true,
      id: c.id,
      payload: {
        name: c.name,
        category: c.category,
        phone: c.phone,
        email: c.email,
        website: c.website,
        address: c.address,
        notes: c.notes,
      },
    })
  }

  function setField<K extends keyof ContactPayload>(key: K, value: ContactPayload[K]) {
    setModal((m) => ({ ...m, payload: { ...m.payload, [key]: value } }))
  }

  async function submit(e: FormEvent) {
    e.preventDefault()
    setBusy(true)
    setError('')
    try {
      const payload = { ...modal.payload, photo }
      if (modal.id === null) {
        await contactsApi.create(payload)
      } else {
        await contactsApi.update(modal.id, payload)
      }
      setModal({ open: false, id: null, payload: EMPTY })
      await load()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  async function remove() {
    if (modal.id === null) return
    const contact = contacts.find((c) => c.id === modal.id)
    if (!window.confirm(`„${contact?.name ?? 'Eintrag'}" wirklich löschen?`)) return
    setBusy(true)
    try {
      await contactsApi.remove(modal.id)
      setModal({ open: false, id: null, payload: EMPTY })
      await load()
    } catch (err) {
      setError(apiError(err))
    } finally {
      setBusy(false)
    }
  }

  const inputClass =
    'w-full rounded-lg border border-border px-3 py-2 outline-none focus:border-primary'

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
          <BookUser className="h-6 w-6" /> Adressbuch
        </h1>
        <button
          onClick={openCreate}
          className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-hover"
        >
          <Plus className="h-4 w-4" /> Neuer Eintrag
        </button>
      </div>

      {error && <p className="text-sm text-red-600">{error}</p>}

      {categories.length > 0 && (
        <div className="flex flex-wrap gap-2 text-sm">
          <button
            onClick={() => setFilter(null)}
            className={`rounded-full px-3 py-1 font-semibold ${
              filter === null ? 'bg-primary text-white' : 'bg-surface-2 text-muted hover:text-primary'
            }`}
          >
            Alle
          </button>
          {categories.map((cat) => (
            <button
              key={cat}
              onClick={() => setFilter(filter === cat ? null : cat)}
              className={`rounded-full px-3 py-1 font-semibold ${
                filter === cat ? 'bg-primary text-white' : 'bg-surface-2 text-muted hover:text-primary'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>
      )}

      {contacts.length === 0 && !error && (
        <p className="text-muted">
          Noch keine Einträge – lege den ersten an, z. B. Kinderarzt, Schule oder die Tante in
          Amerika. 🌎
        </p>
      )}

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        {visible.map((c) => (
          <div key={c.id} className="flex flex-col rounded-2xl bg-surface p-5 shadow">
            <div className="flex items-start gap-3">
              <span className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-soft text-sm font-bold text-primary">
                {c.photo_url ? (
                  <img src={c.photo_url} alt="" loading="lazy" className="h-full w-full object-cover" />
                ) : (
                  initials(c.name) || <BookUser className="h-5 w-5" />
                )}
              </span>
              <div className="min-w-0 flex-1">
                <p className="truncate font-semibold text-text">{c.name}</p>
                {c.category && (
                  <span className="mt-0.5 inline-block rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                    {c.category}
                  </span>
                )}
              </div>
              {canManage(c) && (
                <button
                  onClick={() => openEdit(c)}
                  className="shrink-0 text-xs text-primary hover:underline"
                >
                  Bearbeiten
                </button>
              )}
            </div>

            <div className="mt-3 space-y-1.5 text-sm text-muted">
              {c.phone && (
                <a href={`tel:${c.phone.replace(/\s/g, '')}`} className="flex items-center gap-2 hover:text-primary">
                  <Phone className="h-4 w-4 shrink-0" /> {c.phone}
                </a>
              )}
              {c.email && (
                <a href={`mailto:${c.email}`} className="flex items-center gap-2 hover:text-primary">
                  <Mail className="h-4 w-4 shrink-0" /> <span className="truncate">{c.email}</span>
                </a>
              )}
              {c.website && (
                <a
                  href={c.website}
                  target="_blank"
                  rel="noreferrer"
                  className="flex items-center gap-2 hover:text-primary"
                >
                  <Globe className="h-4 w-4 shrink-0" />
                  <span className="truncate">{c.website.replace(/^https?:\/\//, '')}</span>
                </a>
              )}
              {c.address && (
                <p className="flex items-start gap-2 whitespace-pre-line">
                  <MapPin className="mt-0.5 h-4 w-4 shrink-0" /> {c.address}
                </p>
              )}
              {c.notes && <p className="pt-1 text-xs italic">{c.notes}</p>}
            </div>
          </div>
        ))}
      </div>

      {modal.open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
          <form
            onSubmit={submit}
            className="max-h-full w-full max-w-md space-y-3 overflow-y-auto rounded-2xl bg-surface p-6 shadow-xl"
          >
            <h2 className="text-lg font-semibold text-primary">
              {modal.id === null ? 'Neuer Eintrag' : 'Eintrag bearbeiten'}
            </h2>
            <input
              autoFocus
              placeholder="Name *"
              required
              value={modal.payload.name}
              onChange={(e) => setField('name', e.target.value)}
              className={inputClass}
            />
            <input
              placeholder="Kategorie (z. B. Arzt)"
              list="contact-categories"
              value={modal.payload.category ?? ''}
              onChange={(e) => setField('category', e.target.value || null)}
              className={inputClass}
            />
            <datalist id="contact-categories">
              {[...new Set([...CATEGORY_SUGGESTIONS, ...categories])].map((cat) => (
                <option key={cat} value={cat} />
              ))}
            </datalist>
            <div className="flex gap-2">
              <input
                placeholder="Telefon"
                value={modal.payload.phone ?? ''}
                onChange={(e) => setField('phone', e.target.value || null)}
                className={inputClass}
              />
              <input
                type="email"
                placeholder="E-Mail"
                value={modal.payload.email ?? ''}
                onChange={(e) => setField('email', e.target.value || null)}
                className={inputClass}
              />
            </div>
            <input
              type="url"
              placeholder="Website (https://…)"
              value={modal.payload.website ?? ''}
              onChange={(e) => setField('website', e.target.value || null)}
              className={inputClass}
            />
            <textarea
              placeholder="Adresse"
              rows={2}
              value={modal.payload.address ?? ''}
              onChange={(e) => setField('address', e.target.value || null)}
              className={inputClass}
            />
            <textarea
              placeholder="Notizen"
              rows={2}
              value={modal.payload.notes ?? ''}
              onChange={(e) => setField('notes', e.target.value || null)}
              className={inputClass}
            />
            <div className="flex items-center gap-2 text-sm text-muted">
              <button
                type="button"
                onClick={() => photoInput.current?.click()}
                className="rounded-lg border border-border px-3 py-1.5 hover:border-primary hover:text-primary"
              >
                Foto wählen
              </button>
              <span className="truncate">{photo ? photo.name : 'optional'}</span>
              <input
                ref={photoInput}
                type="file"
                accept="image/*"
                hidden
                onChange={(e) => setPhoto(e.target.files?.[0] ?? null)}
              />
            </div>

            <div className="flex items-center justify-between pt-2">
              {modal.id !== null ? (
                <button
                  type="button"
                  onClick={() => void remove()}
                  disabled={busy}
                  className="text-sm text-red-500 hover:underline disabled:opacity-60"
                >
                  Löschen
                </button>
              ) : (
                <span />
              )}
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => setModal({ open: false, id: null, payload: EMPTY })}
                  className="rounded-lg px-4 py-2 text-sm text-muted hover:bg-surface-2"
                >
                  Abbrechen
                </button>
                <button
                  disabled={busy}
                  className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-hover disabled:opacity-60"
                >
                  {busy ? 'Speichert …' : modal.id === null ? 'Anlegen' : 'Speichern'}
                </button>
              </div>
            </div>
          </form>
        </div>
      )}
    </div>
  )
}
