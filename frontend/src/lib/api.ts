import axios, { type AxiosRequestConfig } from 'axios'

const API_URL = import.meta.env.VITE_API_URL as string
const ORIGIN = new URL(API_URL).origin

/**
 * Axios-Instanz für die API. `withCredentials` + `withXSRFToken` sorgen dafür,
 * dass das Sanctum-Session-Cookie und der CSRF-Token gesendet werden.
 */
export const api = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
})

/** Holt das CSRF-Cookie (vor Login/Registrierung bzw. bei Token-Mismatch). */
export async function ensureCsrf(): Promise<void> {
  await axios.get(`${ORIGIN}/sanctum/csrf-cookie`, { withCredentials: true })
}

// Bei abgelaufenem CSRF-Token (419) einmal neu holen und Request wiederholen.
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
