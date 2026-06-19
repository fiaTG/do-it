import { useState, type ReactNode } from 'react'
import { useNavigate } from 'react-router-dom'
import { type LucideIcon, MoreVertical, Trash2 } from '../../lib/icons'

/**
 * Einheitlicher Rahmen für ein Dashboard-Widget: Titel mit Icon und Inhalt.
 * Ein Klick irgendwo auf die Kachel öffnet die zugehörige App; interaktive
 * Elemente (Checkboxen, das Optionen-Menü) stoppen die Weiterleitung selbst.
 * Das Entfernen vom Dashboard liegt dezent hinter dem Optionen-Menü.
 */
export default function WidgetCard({
  title,
  icon: Icon,
  to,
  onRemove,
  children,
}: {
  title: string
  icon: LucideIcon
  to: string
  onRemove?: () => void
  children: ReactNode
}) {
  const navigate = useNavigate()
  const [menuOpen, setMenuOpen] = useState(false)

  return (
    <div
      onClick={() => navigate(to)}
      onKeyDown={(e) => {
        if (e.key === 'Enter') navigate(to)
      }}
      role="link"
      tabIndex={0}
      aria-label={`${title} öffnen`}
      className="flex cursor-pointer flex-col rounded-2xl bg-surface p-5 shadow-card transition hover:shadow-pop focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)]"
    >
      <div className="mb-3 flex items-center justify-between">
        <h3 className="flex items-center gap-2 font-semibold text-text">
          <Icon className="h-4 w-4 text-primary" aria-hidden /> {title}
        </h3>
        {onRemove && (
          <div className="relative" onClick={(e) => e.stopPropagation()}>
            <button
              onClick={() => setMenuOpen((o) => !o)}
              className="flex h-7 w-7 items-center justify-center rounded-full text-muted transition hover:bg-surface-2"
              title="Optionen"
              aria-label="Optionen"
              aria-haspopup="menu"
              aria-expanded={menuOpen}
            >
              <MoreVertical className="h-4 w-4" />
            </button>
            {menuOpen && (
              <>
                <div className="fixed inset-0 z-10" onClick={() => setMenuOpen(false)} />
                <div className="absolute right-0 top-9 z-20 w-52 overflow-hidden rounded-xl border border-border bg-surface py-1 shadow-pop">
                  <button
                    onClick={() => {
                      setMenuOpen(false)
                      onRemove()
                    }}
                    className="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-text hover:bg-surface-2"
                  >
                    <Trash2 className="h-4 w-4" /> Vom Dashboard entfernen
                  </button>
                </div>
              </>
            )}
          </div>
        )}
      </div>
      <div className="flex-1">{children}</div>
    </div>
  )
}
