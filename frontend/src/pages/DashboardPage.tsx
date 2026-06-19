import { useEffect, useState, type ComponentType, type FormEvent } from 'react'
import { apiError, familyApi } from '../api'
import DashboardStats from '../components/DashboardStats'
import CalendarWidget from '../components/widgets/CalendarWidget'
import GalleryWidget from '../components/widgets/GalleryWidget'
import ShoppingWidget from '../components/widgets/ShoppingWidget'
import TodoWidget from '../components/widgets/TodoWidget'
import { useApps } from '../store/apps'
import { useAuth } from '../store/auth'
import { APP_ICONS, Plus } from '../lib/icons'

// Welche Widget-Komponente gehört zu welcher App (Slug)?
const WIDGETS: Record<string, ComponentType<{ onRemove?: () => void }>> = {
  calendar: CalendarWidget,
  todo: TodoWidget,
  'shopping-list': ShoppingWidget,
  gallery: GalleryWidget,
}

export default function DashboardPage() {
  const user = useAuth((s) => s.user)
  const setUser = useAuth((s) => s.setUser)
  const hasFamily = Boolean(user?.family_id)

  // Geteilter Apps-Store (synchron mit der Seitennavigation).
  const mine = useApps((s) => s.mine)
  const catalog = useApps((s) => s.catalog)
  const load = useApps((s) => s.load)
  const addApp = useApps((s) => s.add)
  const removeApp = useApps((s) => s.remove)

  const [familyName, setFamilyName] = useState('')
  const [error, setError] = useState('')

  useEffect(() => {
    if (hasFamily) void load().catch(() => {})
  }, [hasFamily, load])

  async function createFamily(e: FormEvent) {
    e.preventDefault()
    setError('')
    try {
      const family = await familyApi.create(familyName)
      if (user) setUser({ ...user, family_id: family.id, family })
    } catch (err) {
      setError(apiError(err))
    }
  }

  if (!hasFamily) {
    return (
      <div className="mx-auto max-w-md rounded-2xl bg-surface p-8 shadow">
        <h1 className="mb-2 text-2xl font-bold text-primary">Willkommen! 👋</h1>
        <p className="mb-6 text-muted">
          Du gehörst noch keiner Familie an. Gründe jetzt eine – oder nimm eine
          Einladung an.
        </p>
        {error && <p className="mb-4 text-sm text-red-600">{error}</p>}
        <form onSubmit={createFamily} className="flex gap-2">
          <input
            placeholder="Familienname"
            required
            value={familyName}
            onChange={(e) => setFamilyName(e.target.value)}
            className="flex-1 rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
          />
          <button className="rounded-lg bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-hover">
            Gründen
          </button>
        </form>
      </div>
    )
  }

  const available = catalog.filter((c) => !mine.some((m) => m.id === c.id))
  const today = new Date().toLocaleDateString('de-DE', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  })

  return (
    <div className="space-y-8">
      {/* Begrüßungs-Header */}
      <header className="overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-forest-dark p-6 text-white shadow-card">
        <p className="text-sm text-white/70 capitalize">{today}</p>
        <h1 className="mt-1 text-2xl font-bold">Moin, {user?.first_name}! 👋</h1>
        <p className="mt-1 text-white/80">
          {user?.family ? `Willkommen in eurem Nest, Familie ${user.family.name}.` : 'Willkommen in eurem Nest.'}
        </p>
      </header>

      {mine.length > 0 && <DashboardStats slugs={mine.map((a) => a.slug)} />}

      {mine.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border bg-surface p-8 text-center">
          <div className="text-4xl">🧭</div>
          <p className="mt-3 font-medium text-text">Dein Dashboard ist noch leer.</p>
          <p className="mt-1 text-sm text-muted">
            Füge unten Apps hinzu – ihre Widgets erscheinen dann hier.
          </p>
        </div>
      ) : (
        <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
          {mine.map((app) => {
            const Widget = WIDGETS[app.slug]
            return Widget ? <Widget key={app.id} onRemove={() => void removeApp(app.id)} /> : null
          })}
        </div>
      )}

      {available.length > 0 && (
        <section>
          <h2 className="mb-3 text-lg font-semibold text-text">App hinzufügen</h2>
          <div className="flex flex-wrap gap-3">
            {available.map((app) => (
              <button
                key={app.id}
                onClick={() => void addApp(app.id)}
                className="group flex items-center gap-3 rounded-xl border border-border bg-surface px-4 py-3 text-left shadow-card transition hover:border-primary hover:shadow-pop"
              >
                <span
                  className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary"
                  aria-hidden
                >
                  {(() => {
                    const Icon = APP_ICONS[app.slug] ?? Plus
                    return <Icon className="h-5 w-5" />
                  })()}
                </span>
                <span>
                  <span className="block text-sm font-medium text-text">{app.name}</span>
                  <span className="block text-xs text-muted group-hover:text-primary">
                    Hinzufügen
                  </span>
                </span>
              </button>
            ))}
          </div>
        </section>
      )}
    </div>
  )
}
