import { useRef, useState, type ChangeEvent, type FormEvent } from 'react'
import { apiError, profileApi } from '../api'
import { useAuth } from '../store/auth'

export default function ProfilePage() {
  const user = useAuth((s) => s.user)
  const setUser = useAuth((s) => s.setUser)
  const fileInput = useRef<HTMLInputElement>(null)

  const [firstName, setFirstName] = useState(user?.first_name ?? '')
  const [lastName, setLastName] = useState(user?.last_name ?? '')
  const [birthdate, setBirthdate] = useState(user?.birthdate ?? '')
  const [gender, setGender] = useState(user?.gender ?? '')
  const [facebook, setFacebook] = useState(user?.socials.facebook ?? '')
  const [instagram, setInstagram] = useState(user?.socials.instagram ?? '')
  const [linkedin, setLinkedin] = useState(user?.socials.linkedin ?? '')
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  if (!user) return null

  async function save(e: FormEvent) {
    e.preventDefault()
    setMessage('')
    setError('')
    try {
      const updated = await profileApi.update({
        first_name: firstName,
        last_name: lastName,
        birthdate: birthdate || null,
        gender: gender || null,
        facebook: facebook || null,
        instagram: instagram || null,
        linkedin: linkedin || null,
      })
      setUser(updated)
      setMessage('Profil gespeichert.')
    } catch (err) {
      setError(apiError(err))
    }
  }

  async function uploadAvatar(e: ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0]
    if (!file) return
    try {
      const updated = await profileApi.avatar(file)
      setUser(updated)
      setMessage('Profilbild aktualisiert.')
    } catch (err) {
      setError(apiError(err))
    }
  }

  const inputClass =
    'w-full rounded-lg border border-border px-3 py-2 outline-none focus:border-primary'

  return (
    <div className="mx-auto max-w-lg space-y-6">
      <h1 className="text-2xl font-bold text-primary">👤 Profil</h1>

      {message && <p className="text-sm text-green-700">{message}</p>}
      {error && <p className="text-sm text-red-600">{error}</p>}

      {/* Avatar */}
      <div className="flex items-center gap-5 rounded-2xl bg-white p-5 shadow">
        <div className="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-primary text-2xl font-bold text-white">
          {user.avatar_url ? (
            <img src={user.avatar_url} alt="" className="h-full w-full object-cover" />
          ) : (
            `${firstName[0] ?? ''}${lastName[0] ?? ''}`.toUpperCase()
          )}
        </div>
        <div>
          <button
            type="button"
            onClick={() => fileInput.current?.click()}
            className="rounded-lg border border-primary px-4 py-2 text-sm text-primary hover:bg-primary/10"
          >
            Profilbild ändern
          </button>
          <input ref={fileInput} type="file" accept="image/*" hidden onChange={uploadAvatar} />
        </div>
      </div>

      {/* Felder */}
      <form onSubmit={save} className="space-y-4 rounded-2xl bg-white p-6 shadow">
        <div className="flex gap-3">
          <input className={inputClass} placeholder="Vorname" required value={firstName} onChange={(e) => setFirstName(e.target.value)} />
          <input className={inputClass} placeholder="Nachname" required value={lastName} onChange={(e) => setLastName(e.target.value)} />
        </div>
        <div className="flex gap-3">
          <label className="flex-1 text-sm text-muted">
            Geburtsdatum
            <input type="date" className={`${inputClass} mt-1`} value={birthdate} onChange={(e) => setBirthdate(e.target.value)} />
          </label>
          <label className="flex-1 text-sm text-muted">
            Geschlecht
            <select className={`${inputClass} mt-1`} value={gender} onChange={(e) => setGender(e.target.value)}>
              <option value="">–</option>
              <option value="m">männlich</option>
              <option value="w">weiblich</option>
              <option value="other">divers</option>
            </select>
          </label>
        </div>

        <p className="pt-2 text-sm font-semibold text-muted">Social Media</p>
        <input className={inputClass} placeholder="Facebook-URL" value={facebook} onChange={(e) => setFacebook(e.target.value)} />
        <input className={inputClass} placeholder="Instagram-URL" value={instagram} onChange={(e) => setInstagram(e.target.value)} />
        <input className={inputClass} placeholder="LinkedIn-URL" value={linkedin} onChange={(e) => setLinkedin(e.target.value)} />

        <button className="w-full rounded-lg bg-primary py-2 font-semibold text-white hover:bg-primary-hover">
          Speichern
        </button>
      </form>
    </div>
  )
}
