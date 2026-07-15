import { useEffect, useState } from 'react'
import { contactsApi } from '../../api'
import { APP_ICONS, Phone } from '../../lib/icons'
import type { Contact } from '../../types'
import WidgetCard from './WidgetCard'

function initials(name: string): string {
  const parts = name.trim().split(/\s+/)
  return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase()
}

export default function ContactsWidget({ onRemove }: { onRemove?: () => void }) {
  const [contacts, setContacts] = useState<Contact[]>([])

  useEffect(() => {
    contactsApi.list().then(setContacts).catch(() => {})
  }, [])

  const preview = contacts.slice(0, 5)

  return (
    <WidgetCard title="Adressbuch" icon={APP_ICONS.contacts} to="/contacts" onRemove={onRemove}>
      {preview.length === 0 ? (
        <p className="text-sm text-muted">Noch keine Einträge.</p>
      ) : (
        <ul className="space-y-2">
          {preview.map((c) => (
            <li key={c.id} className="flex items-center gap-2.5">
              <span className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-soft text-[11px] font-bold text-primary">
                {c.photo_url ? (
                  <img src={c.photo_url} alt="" loading="lazy" className="h-full w-full object-cover" />
                ) : (
                  initials(c.name)
                )}
              </span>
              <span className="min-w-0 flex-1 truncate text-sm text-text">{c.name}</span>
              {c.phone && (
                <a
                  href={`tel:${c.phone.replace(/\s/g, '')}`}
                  onClick={(e) => e.stopPropagation()}
                  aria-label={`${c.name} anrufen`}
                  className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-muted hover:bg-surface-2 hover:text-primary"
                >
                  <Phone className="h-3.5 w-3.5" />
                </a>
              )}
            </li>
          ))}
        </ul>
      )}
    </WidgetCard>
  )
}
