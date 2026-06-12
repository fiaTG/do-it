import { useCallback, useEffect, useState, type FormEvent } from 'react'
import { useNavigate } from 'react-router-dom'
import { apiError, appsApi, familyApi } from '../api'
import { useAuth } from '../store/auth'
import type { AppItem } from '../types'

const ROUTE_BY_SLUG: Record<string, string> = {
  gallery: '/gallery',
  'shopping-list': '/shopping',
  todo: '/todos',
  calendar: '/calendar',
}

export default function DashboardPage() {
  const user = useAuth((s) => s.user)
  const setUser = useAuth((s) => s.setUser)
  const navigate = useNavigate()
  const hasFamily = Boolean(user?.family_id)

  const [mine, setMine] = useState<AppItem[]>([])
  const [catalog, setCatalog] = useState<AppItem[]>([])
  const [familyName, setFamilyName] = useState('')
  const [error, setError] = useState('')

  const load = useCallback(async () => {
    setMine(await appsApi.mine())
    setCatalog(await appsApi.catalog())
  }, [])

  useEffect(() => {
    if (hasFamily) void load()
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

  async function addApp(id: number) {
    await appsApi.add(id)
    await load()
  }

  async function removeApp(id: number) {
    await appsApi.remove(id)
    await load()
  }

  if (!hasFamily) {
    return (
      <div className="mx-auto max-w-md rounded-2xl bg-white p-8 shadow">
        <h1 className="mb-2 text-2xl font-bold text-brand">Willkommen! 👋</h1>
        <p className="mb-6 text-slate-600">
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
            className="flex-1 rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
          />
          <button className="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-dark">
            Gründen
          </button>
        </form>
      </div>
    )
  }

  const available = catalog.filter((c) => !mine.some((m) => m.id === c.id))

  return (
    <div className="space-y-8">
      <h1 className="text-2xl font-bold text-brand">Dashboard</h1>

      <section>
        <h2 className="mb-3 text-lg font-semibold text-slate-700">Deine Apps</h2>
        {mine.length === 0 && (
          <p className="text-slate-500">Noch keine Apps – füge unten welche hinzu.</p>
        )}
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          {mine.map((app) => (
            <div
              key={app.id}
              className="group relative cursor-pointer rounded-2xl bg-white p-6 text-center shadow transition hover:shadow-lg"
              onClick={() => navigate(ROUTE_BY_SLUG[app.slug] ?? '/')}
            >
              <button
                onClick={(e) => {
                  e.stopPropagation()
                  void removeApp(app.id)
                }}
                className="absolute right-2 top-2 text-slate-300 opacity-0 transition group-hover:opacity-100 hover:text-red-500"
                aria-label="Entfernen"
              >
                ✕
              </button>
              <div className="text-3xl">
                <i className={app.icon ?? ''} />
                {!app.icon && '📦'}
              </div>
              <div className="mt-2 font-medium text-slate-700">{app.name}</div>
            </div>
          ))}
        </div>
      </section>

      {available.length > 0 && (
        <section>
          <h2 className="mb-3 text-lg font-semibold text-slate-700">App hinzufügen</h2>
          <div className="flex flex-wrap gap-3">
            {available.map((app) => (
              <button
                key={app.id}
                onClick={() => void addApp(app.id)}
                className="rounded-full border border-brand px-4 py-2 text-sm text-brand transition hover:bg-brand hover:text-white"
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
