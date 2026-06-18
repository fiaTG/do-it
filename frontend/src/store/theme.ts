import { create } from 'zustand'

export type Theme = 'light' | 'dark'

const STORAGE_KEY = 'nidula-theme'

/** Ermittelt das Start-Theme: gespeicherte Wahl > System-Präferenz > hell. */
function initialTheme(): Theme {
  if (typeof window === 'undefined') return 'light'
  const stored = window.localStorage.getItem(STORAGE_KEY)
  if (stored === 'light' || stored === 'dark') return stored
  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

/** Schreibt das Theme ins <html data-theme="…"> (steuert die semantischen Tokens). */
function apply(theme: Theme) {
  if (typeof document !== 'undefined') {
    document.documentElement.dataset.theme = theme
  }
}

interface ThemeState {
  theme: Theme
  toggle: () => void
  setTheme: (theme: Theme) => void
}

export const useTheme = create<ThemeState>((set) => {
  const theme = initialTheme()
  apply(theme)

  function persist(next: Theme) {
    apply(next)
    window.localStorage.setItem(STORAGE_KEY, next)
    set({ theme: next })
  }

  return {
    theme,
    toggle: () => set((s) => {
      const next: Theme = s.theme === 'dark' ? 'light' : 'dark'
      apply(next)
      window.localStorage.setItem(STORAGE_KEY, next)
      return { theme: next }
    }),
    setTheme: persist,
  }
})
