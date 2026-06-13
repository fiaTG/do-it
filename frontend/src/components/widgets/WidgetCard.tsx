import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'

/**
 * Einheitlicher Rahmen für ein Dashboard-Widget: Titel mit Icon, Link zur
 * vollen App und der eigentliche Inhalt.
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
  return (
    <div className="flex flex-col rounded-2xl bg-surface p-5 shadow">
      <div className="mb-3 flex items-center justify-between">
        <h3 className="font-semibold text-text">
          <span aria-hidden>{icon}</span> {title}
        </h3>
        <div className="flex items-center gap-3">
          <Link to={to} className="text-sm text-primary hover:underline">
            öffnen →
          </Link>
          {onRemove && (
            <button
              onClick={onRemove}
              className="text-muted hover:text-red-500"
              title="Widget entfernen"
              aria-label="Widget entfernen"
            >
              ✕
            </button>
          )}
        </div>
      </div>
      <div className="flex-1">{children}</div>
    </div>
  )
}
