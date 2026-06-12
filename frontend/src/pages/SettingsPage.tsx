import { useState, type FormEvent } from 'react'
import { apiError, authApi } from '../api'

export default function SettingsPage() {
  const [current, setCurrent] = useState('')
  const [password, setPassword] = useState('')
  const [confirmation, setConfirmation] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  async function submit(e: FormEvent) {
    e.preventDefault()
    setMessage('')
    setError('')
    try {
      await authApi.updatePassword({
        current_password: current,
        password,
        password_confirmation: confirmation,
      })
      setMessage('Passwort erfolgreich geändert.')
      setCurrent('')
      setPassword('')
      setConfirmation('')
    } catch (err) {
      setError(apiError(err))
    }
  }

  const inputClass =
    'mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand'

  return (
    <div className="mx-auto max-w-md space-y-6">
      <h1 className="text-2xl font-bold text-brand">⚙️ Einstellungen</h1>

      <form onSubmit={submit} className="rounded-2xl bg-white p-6 shadow">
        <h2 className="mb-4 font-semibold text-slate-700">Passwort ändern</h2>

        {message && <p className="mb-4 text-sm text-green-700">{message}</p>}
        {error && <p className="mb-4 text-sm text-red-600">{error}</p>}

        <input
          type="password"
          placeholder="Aktuelles Passwort"
          required
          value={current}
          onChange={(e) => setCurrent(e.target.value)}
          className={inputClass}
        />
        <input
          type="password"
          placeholder="Neues Passwort"
          required
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className={inputClass}
        />
        <input
          type="password"
          placeholder="Neues Passwort bestätigen"
          required
          value={confirmation}
          onChange={(e) => setConfirmation(e.target.value)}
          className={inputClass}
        />
        <p className="mb-4 text-xs text-slate-400">
          Mind. 8 Zeichen, mit Buchstabe, Zahl und Sonderzeichen.
        </p>
        <button className="w-full rounded-lg bg-brand py-2 font-semibold text-white hover:bg-brand-dark">
          Speichern
        </button>
      </form>
    </div>
  )
}
