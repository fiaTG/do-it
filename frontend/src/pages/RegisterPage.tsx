import { useEffect, useState, type FormEvent } from 'react'
import { Link, Navigate, useNavigate, useSearchParams } from 'react-router-dom'
import { apiError, inviteApi } from '../api'
import AuthLayout from '../components/AuthLayout'
import { useAuth } from '../store/auth'

export default function RegisterPage() {
  const user = useAuth((s) => s.user)
  const register = useAuth((s) => s.register)
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const token = params.get('token') ?? undefined

  const [firstName, setFirstName] = useState('')
  const [lastName, setLastName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [inviteFamily, setInviteFamily] = useState<string | null>(null)
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  useEffect(() => {
    if (!token) return
    inviteApi
      .show(token)
      .then((invite) => {
        setInviteFamily(invite.family?.name ?? null)
        setEmail(invite.email)
      })
      .catch(() => setError('Diese Einladung ist ungültig oder abgelaufen.'))
  }, [token])

  if (user) return <Navigate to="/dashboard" replace />

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setError('')
    setBusy(true)
    try {
      await register({
        first_name: firstName,
        last_name: lastName,
        email,
        password,
        password_confirmation: passwordConfirmation,
        token,
      })
      navigate('/dashboard')
    } catch (err) {
      setError(apiError(err, 'Registrierung fehlgeschlagen.'))
    } finally {
      setBusy(false)
    }
  }

  const inputClass =
    'mb-4 w-full rounded-lg border border-border px-3 py-2 outline-none focus:border-primary'

  return (
    <AuthLayout
      title="Konto erstellen"
      subtitle={
        inviteFamily ? `Beitritt zur Familie ${inviteFamily}` : 'Gründe euer Familien-Nest.'
      }
    >
      <form onSubmit={handleSubmit}>
        {error && (
          <p className="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p>
        )}

        <div className="flex gap-3">
          <input
            placeholder="Vorname"
            required
            value={firstName}
            onChange={(e) => setFirstName(e.target.value)}
            className={inputClass}
          />
          <input
            placeholder="Nachname"
            required
            value={lastName}
            onChange={(e) => setLastName(e.target.value)}
            className={inputClass}
          />
        </div>

        <input
          type="email"
          placeholder="E-Mail"
          required
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className={inputClass}
        />
        <input
          type="password"
          placeholder="Passwort"
          required
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className={inputClass}
        />
        <input
          type="password"
          placeholder="Passwort bestätigen"
          required
          value={passwordConfirmation}
          onChange={(e) => setPasswordConfirmation(e.target.value)}
          className={inputClass}
        />

        <p className="mb-4 text-xs text-muted">
          Mind. 8 Zeichen, mit Buchstabe, Zahl und Sonderzeichen.
        </p>

        <button
          type="submit"
          disabled={busy}
          className="w-full rounded-lg bg-primary py-2 font-semibold text-white transition hover:bg-primary-hover disabled:opacity-60"
        >
          {busy ? 'Registrieren …' : 'Registrieren'}
        </button>

        <p className="mt-4 text-center text-sm text-muted">
          Schon ein Konto?{' '}
          <Link to="/login" className="font-semibold text-primary">
            Anmelden
          </Link>
        </p>
      </form>
    </AuthLayout>
  )
}
