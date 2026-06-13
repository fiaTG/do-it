import { useState, type FormEvent } from 'react'
import { Link, Navigate, useNavigate } from 'react-router-dom'
import { apiError } from '../api'
import AuthLayout from '../components/AuthLayout'
import { useAuth } from '../store/auth'

export default function LoginPage() {
  const user = useAuth((s) => s.user)
  const login = useAuth((s) => s.login)
  const navigate = useNavigate()

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  if (user) return <Navigate to="/dashboard" replace />

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError('')
    setBusy(true)
    try {
      await login(email, password)
      navigate('/dashboard')
    } catch (err) {
      setError(apiError(err, 'Login fehlgeschlagen.'))
    } finally {
      setBusy(false)
    }
  }

  const inputClass =
    'mb-4 w-full rounded-lg border border-border px-3 py-2 outline-none focus:border-primary'

  return (
    <AuthLayout title="Willkommen zurück" subtitle="Melde dich in deinem Heimathafen an.">
      <form onSubmit={handleSubmit}>
        {error && (
          <p className="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p>
        )}

        <label htmlFor="email" className="mb-1 block text-sm font-medium text-muted">
          E-Mail
        </label>
        <input
          id="email"
          type="email"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className={inputClass}
        />

        <label htmlFor="password" className="mb-1 block text-sm font-medium text-muted">
          Passwort
        </label>
        <input
          id="password"
          type="password"
          required
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className={inputClass}
        />

        <button
          type="submit"
          disabled={busy}
          className="w-full rounded-lg bg-primary py-2 font-semibold text-white transition hover:bg-primary-hover disabled:opacity-60"
        >
          {busy ? 'Anmelden …' : 'Anmelden'}
        </button>

        <p className="mt-4 text-center text-sm text-muted">
          Noch kein Konto?{' '}
          <Link to="/register" className="font-semibold text-primary">
            Registrieren
          </Link>
        </p>
      </form>
    </AuthLayout>
  )
}
