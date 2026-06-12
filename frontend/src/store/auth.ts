import { create } from 'zustand'
import { authApi, type RegisterPayload } from '../api'
import { ensureCsrf } from '../lib/api'
import type { User } from '../types'

interface AuthState {
  user: User | null
  loading: boolean
  init: () => Promise<void>
  login: (email: string, password: string) => Promise<void>
  register: (payload: RegisterPayload) => Promise<void>
  logout: () => Promise<void>
  setUser: (user: User) => void
}

export const useAuth = create<AuthState>((set) => ({
  user: null,
  loading: true,

  init: async () => {
    try {
      await ensureCsrf()
      const user = await authApi.me()
      set({ user })
    } catch {
      set({ user: null })
    } finally {
      set({ loading: false })
    }
  },

  login: async (email, password) => {
    const user = await authApi.login(email, password)
    set({ user })
  },

  register: async (payload) => {
    const user = await authApi.register(payload)
    set({ user })
  },

  logout: async () => {
    await authApi.logout()
    set({ user: null })
  },

  setUser: (user) => set({ user }),
}))
