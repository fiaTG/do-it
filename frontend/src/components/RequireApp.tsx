import type { ReactNode } from 'react'
import { Navigate } from 'react-router-dom'
import { useApps } from '../store/apps'
import { useAuth } from '../store/auth'

/**
 * Route-Guard für Feature-Apps: eine Seite ist nur erreichbar, wenn der Nutzer
 * die zugehörige App aktiviert hat (/me/apps) – auch bei direkter URL-Eingabe.
 * Andernfalls geht es zurück aufs Dashboard.
 *
 * Die Apps werden vom Layout geladen; bis sie da sind, wird nichts entschieden
 * (kein vorschnelles Wegleiten während des Ladens).
 */
export default function RequireApp({ slug, children }: { slug: string; children: ReactNode }) {
  const hasFamily = useAuth((s) => Boolean(s.user?.family_id))
  const loaded = useApps((s) => s.loaded)
  const active = useApps((s) => s.mine.some((app) => app.slug === slug))

  if (!hasFamily) return <Navigate to="/dashboard" replace />
  if (!loaded) return null // Apps werden gerade geladen
  if (!active) return <Navigate to="/dashboard" replace />

  return <>{children}</>
}
