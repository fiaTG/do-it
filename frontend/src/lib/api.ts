import { Capacitor } from '@capacitor/core'
import { Preferences } from '@capacitor/preferences'
import axios, { type AxiosRequestConfig } from 'axios'

const API_URL = import.meta.env.VITE_API_URL as string | undefined
if (!API_URL) {
  // Review N-03: klare Meldung statt kryptischem "Invalid URL" beim Import.
  throw new Error(
    'VITE_API_URL fehlt. Lokal: `cp .env.example .env` in frontend/ (Tests nutzen .env.test).',
  )
}
const ORIGIN = new URL(API_URL).origin

/**
 * Läuft die App in einer nativen Hülle (Capacitor)? Dann gibt es keine
 * gemeinsame Origin/Cookies mit der API → wir nutzen Bearer-Token-Auth statt
 * der Cookie-/CSRF-Auth des Web-SPA (ADR-0012).
 */
export const isNative = Capacitor.isNativePlatform()

const TOKEN_KEY = 'auth_token'
let authToken: string | null = null

/**
 * Axios-Instanz für die API. Im Web sorgen `withCredentials` + `withXSRFToken`
 * für Sanctum-Session-Cookie und CSRF-Token; nativ wird stattdessen ein
 * Bearer-Token mitgeschickt (siehe Request-Interceptor unten).
 */
export const api = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
})

// Native: gespeicherten API-Token an jede Anfrage hängen.
api.interceptors.request.use((config) => {
  if (authToken) {
    config.headers.Authorization = `Bearer ${authToken}`
  }
  return config
})

/** Lädt den persistierten Token (nur nativ; im Web No-op). */
export async function loadStoredToken(): Promise<void> {
  if (!isNative) return
  const { value } = await Preferences.get({ key: TOKEN_KEY })
  authToken = value
}

/** Setzt/entfernt den Token im Speicher und persistiert ihn nativ. */
export async function setAuthToken(token: string | null): Promise<void> {
  authToken = token
  if (!isNative) return
  if (token) {
    await Preferences.set({ key: TOKEN_KEY, value: token })
  } else {
    await Preferences.remove({ key: TOKEN_KEY })
  }
}

export function hasAuthToken(): boolean {
  return authToken !== null
}

/** Holt das CSRF-Cookie (nur Web; Token-Auth braucht keins). */
export async function ensureCsrf(): Promise<void> {
  if (isNative) return
  await axios.get(`${ORIGIN}/sanctum/csrf-cookie`, { withCredentials: true })
}

// Bei abgelaufenem CSRF-Token (419, nur Web) einmal neu holen und wiederholen.
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const cfg = error.config as (AxiosRequestConfig & { __retried?: boolean }) | undefined
    if (error.response?.status === 419 && cfg && !cfg.__retried) {
      cfg.__retried = true
      await ensureCsrf()
      return api(cfg)
    }
    return Promise.reject(error)
  },
)
