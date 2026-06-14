import { create } from 'zustand'
import { appsApi } from '../api'
import type { AppItem } from '../types'

/**
 * Geteilter Zustand der vom Nutzer aktivierten Apps (/me/apps). Dashboard und
 * Seitennavigation greifen darauf zu, damit das Aktivieren/Entfernen einer App
 * sofort überall sichtbar ist (nicht nur nach Reload).
 */
interface AppsState {
  mine: AppItem[]
  catalog: AppItem[]
  loaded: boolean
  load: () => Promise<void>
  add: (id: number) => Promise<void>
  remove: (id: number) => Promise<void>
  reset: () => void
}

export const useApps = create<AppsState>((set, get) => ({
  mine: [],
  catalog: [],
  loaded: false,

  load: async () => {
    const [mine, catalog] = await Promise.all([appsApi.mine(), appsApi.catalog()])
    set({ mine, catalog, loaded: true })
  },

  add: async (id) => {
    await appsApi.add(id)
    await get().load()
  },

  remove: async (id) => {
    await appsApi.remove(id)
    await get().load()
  },

  reset: () => set({ mine: [], catalog: [], loaded: false }),
}))
