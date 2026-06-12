import { useEffect, useState, type FormEvent } from 'react'
import { Link, Navigate, useNavigate, useSearchParams } from 'react-router-dom'
import { apiError, inviteApi } from '../api'
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

  // Bei Einladungs-Token: Familie anzeigen und E-Mail vorbefüllen.
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

  if (user) return <Navigate to="/" replace />

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
      navigate('/')
    } catch (err) {
      setError(apiError(err, 'Registrierung fehlgeschlagen.'))
    } finally {
      setBusy(false)
    }
  }

  const inputClass =
    'mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand'

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand to-sand p-4">
      <form onSubmit={handleSubmit} className="w-full max-w-sm rounded-2xl bg-white p-8 shadow-xl">
        <h1 className="mb-1 text-center text-2xl font-bold text-brand">Registrierung</h1>
        {inviteFamily && (
          <p className="mb-4 text-center text-sm text-slate-500">
            Beitritt zur Familie <strong>{inviteFamily}</strong>
          </p>
        )}

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

        <p className="mb-4 text-xs text-slate-400">
          Mind. 8 Zeichen, mit Buchstabe, Zahl und Sonderzeichen.
        </p>

        <button
          type="submit"
          disabled={busy}
          className="w-full rounded-lg bg-brand py-2 font-semibold text-white transition hover:bg-brand-dark disabled:opacity-60"
        >
          {busy ? 'Registrieren …' : 'Registrieren'}
        </button>

        <p className="mt-4 text-center text-sm text-slate-500">
          Schon ein Konto?{' '}
          <Link to="/login" className="font-semibold text-brand">
            Anmelden
          </Link>
        </p>
      </form>
    </div>
  )
}
