import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'
import { ChevronLeft } from '../../lib/icons'
import { LEGAL_PUBLISHED } from '../../lib/legal'

/** Gemeinsames Gerüst für Impressum/Datenschutz (öffentlich erreichbar). */
export default function LegalLayout({ title, children }: { title: string; children: ReactNode }) {
  return (
    <div className="mx-auto min-h-screen max-w-2xl bg-bg px-6 py-10 text-text">
      <Link to="/" className="mb-6 inline-flex items-center gap-1 text-sm text-muted hover:text-primary">
        <ChevronLeft className="h-4 w-4" /> Zurück
      </Link>
      <h1 className="text-2xl font-bold text-primary">{title}</h1>

      {!LEGAL_PUBLISHED && (
        <p className="mt-4 rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
          <strong>Entwurf.</strong> Diese Seite ist noch nicht final – Platzhalter werden vor dem
          öffentlichen Start gefüllt und die Texte geprüft.
        </p>
      )}

      <div className="prose-legal mt-6 space-y-4 text-sm leading-relaxed text-text">{children}</div>
    </div>
  )
}
