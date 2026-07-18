import { useEffect, useState, type FormEvent } from 'react'
import { apiError, familyApi, todosApi } from '../api'
import ErrorBanner from '../components/ErrorBanner'
import MemberAvatar from '../components/MemberAvatar'
import { CheckSquare, Trash2, Trophy } from '../lib/icons'
import { useAuth } from '../store/auth'
import type { Todo, TodoPoints, User } from '../types'

export default function TodosPage() {
  const userId = useAuth((s) => s.user?.id)
  const [todos, setTodos] = useState<Todo[]>([])
  const [members, setMembers] = useState<User[]>([])
  const [points, setPoints] = useState<TodoPoints | null>(null)
  const [title, setTitle] = useState('')
  const [error, setError] = useState('')

  async function load() {
    try {
      const [t, m, p] = await Promise.all([
        todosApi.list(),
        familyApi.members(),
        todosApi.points(),
      ])
      setTodos(t)
      setMembers(m)
      setPoints(p)
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  const memberById = (id: number | null | undefined): User | undefined =>
    members.find((m) => m.id === id)

  // Nest-Blätter (ADR-0026): eigener Stand + Wochen-Champion der Familie.
  const myWeek = points?.week[String(userId)] ?? 0
  const myTotal = points?.totals[String(userId)] ?? 0
  const champion = points
    ? Object.entries(points.week).sort((a, b) => b[1] - a[1])[0]
    : undefined
  const championMember = champion ? memberById(Number(champion[0])) : undefined

  // Optimistisch anlegen: Platzhalter sofort zeigen, nach der Antwort durch den
  // echten Datensatz ersetzen; bei Fehler wieder entfernen + Eingabe zurück.
  async function add(e: FormEvent) {
    e.preventDefault()
    const value = title.trim()
    if (!value) return
    setTitle('')
    const temp: Todo = {
      id: -Date.now(),
      title: value,
      is_done: false,
      created_by: userId ?? null,
      created_at: new Date().toISOString(),
    }
    setTodos((prev) => [...prev, temp])
    try {
      const created = await todosApi.create(value)
      setTodos((prev) => prev.map((t) => (t.id === temp.id ? created : t)))
    } catch (err) {
      setTodos((prev) => prev.filter((t) => t.id !== temp.id))
      setTitle(value)
      setError(apiError(err))
    }
  }

  // Optimistisch abhaken: Zustand sofort umschalten, bei Fehler zurückdrehen.
  // Danach Blätter-Stände nachladen (Punkt kommt/geht serverseitig).
  async function toggle(todo: Todo) {
    const next = !todo.is_done
    setTodos((prev) =>
      prev.map((t) =>
        t.id === todo.id ? { ...t, is_done: next, completed_by: next ? userId : null } : t,
      ),
    )
    try {
      await todosApi.update(todo.id, { is_done: next })
      setPoints(await todosApi.points())
    } catch (err) {
      setTodos((prev) => prev.map((t) => (t.id === todo.id ? todo : t)))
      setError(apiError(err))
    }
  }

  // Optimistisch löschen: sofort ausblenden, bei Fehler Eintrag wiederherstellen.
  async function remove(todo: Todo) {
    setTodos((prev) => prev.filter((t) => t.id !== todo.id))
    try {
      await todosApi.remove(todo.id)
    } catch (err) {
      setTodos((prev) => [...prev, todo].sort((a, b) => a.id - b.id))
      setError(apiError(err))
    }
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <CheckSquare className="h-6 w-6" /> ToDo-Liste
      </h1>
      <ErrorBanner message={error} onDismiss={() => setError('')} />

      {/* Nest-Blätter: eigener Stand + Wochen-Champion (ADR-0026, ehrlich &
          freundlich – keine Straf-Mechaniken, kein Streak-Drama). */}
      <div className="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-2xl bg-surface p-4 shadow">
        <p className="text-sm text-text">
          <span className="font-semibold">Deine Blätter:</span> {myTotal} 🍃
          <span className="text-muted"> · diese Woche {myWeek}</span>
        </p>
        {championMember && champion && champion[1] > 0 && (
          <p className="flex items-center gap-1.5 text-sm text-muted">
            <Trophy className="h-4 w-4 text-primary" />
            Champion der Woche:
            <MemberAvatar member={championMember} size="sm" />
            <span className="font-semibold text-text">
              {championMember.first_name} ({champion[1]} 🍃)
            </span>
          </p>
        )}
      </div>

      <form onSubmit={add} className="flex gap-2 rounded-2xl bg-surface p-4 shadow">
        <input
          placeholder="Neue Aufgabe"
          required
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          className="flex-1 rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
        />
        <button className="rounded-lg bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-hover">
          + Hinzufügen
        </button>
      </form>

      <ul className="divide-y divide-border rounded-2xl bg-surface shadow">
        {todos.length === 0 && <li className="p-4 text-muted">Keine Aufgaben.</li>}
        {todos.map((todo) => {
          const pending = todo.id < 0 // optimistischer Platzhalter (noch nicht gespeichert)
          const doneBy = todo.is_done ? memberById(todo.completed_by) : undefined
          return (
            <li key={todo.id} className={`flex items-center gap-3 p-4 ${pending ? 'opacity-60' : ''}`}>
              <input
                type="checkbox"
                checked={todo.is_done}
                disabled={pending}
                onChange={() => void toggle(todo)}
                className="h-5 w-5 accent-primary"
              />
              <span className={todo.is_done ? 'flex-1 text-muted line-through' : 'flex-1'}>
                {todo.title}
              </span>
              {doneBy && (
                <span title={`Erledigt von ${doneBy.first_name} (+1 🍃)`}>
                  <MemberAvatar member={doneBy} size="sm" />
                </span>
              )}
              {!pending && todo.created_by === userId && (
                <button
                  onClick={() => void remove(todo)}
                  className="text-muted hover:text-red-500"
                  aria-label="Löschen"
                >
                  <Trash2 className="h-4 w-4" />
                </button>
              )}
            </li>
          )
        })}
      </ul>
    </div>
  )
}
