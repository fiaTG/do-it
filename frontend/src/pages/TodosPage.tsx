import { useEffect, useState, type FormEvent } from 'react'
import { apiError, todosApi } from '../api'
import ErrorBanner from '../components/ErrorBanner'
import { CheckSquare, Trash2 } from '../lib/icons'
import { useAuth } from '../store/auth'
import type { Todo } from '../types'

export default function TodosPage() {
  const userId = useAuth((s) => s.user?.id)
  const [todos, setTodos] = useState<Todo[]>([])
  const [title, setTitle] = useState('')
  const [error, setError] = useState('')

  async function load() {
    try {
      setTodos(await todosApi.list())
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

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
  async function toggle(todo: Todo) {
    const next = !todo.is_done
    setTodos((prev) => prev.map((t) => (t.id === todo.id ? { ...t, is_done: next } : t)))
    try {
      await todosApi.update(todo.id, { is_done: next })
    } catch (err) {
      setTodos((prev) => prev.map((t) => (t.id === todo.id ? { ...t, is_done: todo.is_done } : t)))
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
