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
    'mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand'

  return (
    <AuthLayout title="Willkommen zurück" subtitle="Melde dich bei deinem Family Board an.">
      <form onSubmit={handleSubmit}>
        {error && (
          <p className="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p>
        )}

        <label className="mb-1 block text-sm font-medium text-slate-600">E-Mail</label>
        <input
          type="email"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className={inputClass}
        />

        <label className="mb-1 block text-sm font-medium text-slate-600">Passwort</label>
        <input
          type="password"
          required
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className={inputClass}
        />

        <button
          type="submit"
          disabled={busy}
          className="w-full rounded-lg bg-brand py-2 font-semibold text-white transition hover:bg-brand-dark disabled:opacity-60"
        >
          {busy ? 'Anmelden …' : 'Anmelden'}
        </button>

        <p className="mt-4 text-center text-sm text-slate-500">
          Noch kein Konto?{' '}
          <Link to="/register" className="font-semibold text-brand">
            Registrieren
          </Link>
        </p>
      </form>
    </AuthLayout>
  )
}
