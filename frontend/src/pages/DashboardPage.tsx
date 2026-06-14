import { useEffect, useState, type ComponentType, type FormEvent } from 'react'
import { apiError, familyApi } from '../api'
import CalendarWidget from '../components/widgets/CalendarWidget'
import GalleryWidget from '../components/widgets/GalleryWidget'
import ShoppingWidget from '../components/widgets/ShoppingWidget'
import TodoWidget from '../components/widgets/TodoWidget'
import { useApps } from '../store/apps'
import { useAuth } from '../store/auth'

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

  return (
    <div className="space-y-8">
      <h1 className="text-2xl font-bold text-primary">Dashboard</h1>

      {mine.length === 0 ? (
        <p className="text-muted">
          Noch keine Apps aktiv – füge unten welche hinzu, dann erscheinen hier ihre Widgets.
        </p>
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
                className="rounded-full border border-primary px-4 py-2 text-sm text-primary transition hover:bg-primary hover:text-white"
              >
                + {app.name}
              </button>
            ))}
          </div>
        </section>
      )}
    </div>
  )
}
