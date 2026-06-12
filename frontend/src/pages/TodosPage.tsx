import { useEffect, useState, type FormEvent } from 'react'
import { apiError, todosApi } from '../api'
import type { Todo } from '../types'

export default function TodosPage() {
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

  async function add(e: FormEvent) {
    e.preventDefault()
    await todosApi.create(title)
    setTitle('')
    await load()
  }

  async function toggle(todo: Todo) {
    await todosApi.update(todo.id, { is_done: !todo.is_done })
    await load()
  }

  async function remove(id: number) {
    await todosApi.remove(id)
    await load()
  }

  if (error) return <p className="text-red-600">{error}</p>

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <h1 className="text-2xl font-bold text-brand">✅ ToDo-Liste</h1>

      <form onSubmit={add} className="flex gap-2 rounded-2xl bg-white p-4 shadow">
        <input
          placeholder="Neue Aufgabe"
          required
          value={title}
          onChange={(e) => setTitle(e.target.value)}
          className="flex-1 rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
        />
        <button className="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-dark">
          + Hinzufügen
        </button>
      </form>

      <ul className="divide-y rounded-2xl bg-white shadow">
        {todos.length === 0 && <li className="p-4 text-slate-500">Keine Aufgaben.</li>}
        {todos.map((todo) => (
          <li key={todo.id} className="flex items-center gap-3 p-4">
            <input
              type="checkbox"
              checked={todo.is_done}
              onChange={() => void toggle(todo)}
              className="h-5 w-5 accent-brand"
            />
            <span className={todo.is_done ? 'flex-1 text-slate-400 line-through' : 'flex-1'}>
              {todo.title}
            </span>
            <button
              onClick={() => void remove(todo.id)}
              className="text-slate-300 hover:text-red-500"
              aria-label="Löschen"
            >
              🗑️
            </button>
          </li>
        ))}
      </ul>
    </div>
  )
}
