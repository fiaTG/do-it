import { useEffect, useState, type FormEvent } from 'react'
import { apiError, familyApi, inviteApi } from '../api'
import type { User } from '../types'

function initials(user: User): string {
  return (user.first_name[0] ?? '').concat(user.last_name[0] ?? '').toUpperCase()
}

export default function MembersPage() {
  const [members, setMembers] = useState<User[]>([])
  const [email, setEmail] = useState('')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  async function load() {
    try {
      setMembers(await familyApi.members())
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  async function invite(e: FormEvent) {
    e.preventDefault()
    setError('')
    setMessage('')
    try {
      await inviteApi.create(email)
      setMessage(`Einladung an ${email} verschickt (im Dev-Setup landet sie in Mailpit).`)
      setEmail('')
    } catch (err) {
      setError(apiError(err))
    }
  }

  if (error) return <p className="text-red-600">{error}</p>

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <h1 className="text-2xl font-bold text-primary">👪 Familienmitglieder</h1>

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
        {members.map((m) => (
          <div key={m.id} className="flex flex-col items-center rounded-2xl bg-surface p-5 shadow">
            <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-primary text-xl font-bold text-white">
              {m.avatar_url ? (
                <img src={m.avatar_url} alt="" className="h-full w-full object-cover" />
              ) : (
                initials(m)
              )}
            </div>
            <div className="mt-2 text-center font-medium text-text">
              {m.first_name} {m.last_name}
            </div>
          </div>
        ))}
      </div>

      <form onSubmit={invite} className="rounded-2xl bg-surface p-4 shadow">
        <h2 className="mb-3 font-semibold text-text">Mitglied einladen</h2>
        {message && <p className="mb-3 text-sm text-green-700">{message}</p>}
        <div className="flex gap-2">
          <input
            type="email"
            placeholder="E-Mail-Adresse"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="flex-1 rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
          />
          <button className="rounded-lg bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-hover">
            Einladen
          </button>
        </div>
      </form>
    </div>
  )
}
