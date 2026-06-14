import { useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'

/**
 * Einheitlicher Rahmen für ein Dashboard-Widget: Titel mit Icon, Link zur
 * vollen App und der eigentliche Inhalt. Das Entfernen vom Dashboard liegt
 * dezent hinter einem ⋯-Menü.
 */
export default function WidgetCard({
  title,
  icon,
  to,
  onRemove,
  children,
}: {
  title: string
  icon: string
  to: string
  onRemove?: () => void
  children: ReactNode
}) {
  const [menuOpen, setMenuOpen] = useState(false)

  return (
    <div className="flex flex-col rounded-2xl bg-surface p-5 shadow-card">
      <div className="mb-3 flex items-center justify-between">
        <h3 className="font-semibold text-text">
          <span aria-hidden>{icon}</span> {title}
        </h3>
        <div className="flex items-center gap-3">
          <Link to={to} className="text-sm text-primary hover:underline">
            öffnen →
          </Link>
          {onRemove && (
            <div className="relative">
              <button
                onClick={() => setMenuOpen((o) => !o)}
                className="flex h-7 w-7 items-center justify-center rounded-full text-muted transition hover:bg-surface-2"
                title="Optionen"
                aria-label="Optionen"
                aria-haspopup="menu"
                aria-expanded={menuOpen}
              >
                ⋯
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
                      🗑️ Vom Dashboard entfernen
                    </button>
                  </div>
                </>
              )}
            </div>
          )}
        </div>
      </div>
      <div className="flex-1">{children}</div>
    </div>
  )
}
