import { useEffect, useState } from 'react'
import { todosApi } from '../../api'
import type { Todo } from '../../types'
import WidgetCard from './WidgetCard'

export default function TodoWidget({ onRemove }: { onRemove?: () => void }) {
  const [todos, setTodos] = useState<Todo[]>([])

  function load() {
    todosApi.list().then(setTodos).catch(() => {})
  }

  useEffect(() => {
    load()
  }, [])

  const open = todos.filter((t) => !t.is_done)

  // Optimistisch: Eintrag sofort als erledigt markieren (verschwindet aus der
  // offenen Liste); bei Fehler zurückdrehen.
  async function markDone(todo: Todo) {
    setTodos((prev) => prev.map((t) => (t.id === todo.id ? { ...t, is_done: true } : t)))
    try {
      await todosApi.update(todo.id, { is_done: true })
    } catch {
      setTodos((prev) => prev.map((t) => (t.id === todo.id ? { ...t, is_done: false } : t)))
    }
  }

  return (
    <WidgetCard
      title={`ToDos${open.length ? ` (${open.length})` : ''}`}
      icon="✅"
      to="/todos"
      onRemove={onRemove}
    >
      {open.length === 0 ? (
        <p className="text-sm text-muted">Alles erledigt 🎉</p>
      ) : (
        <ul className="space-y-2">
          {open.slice(0, 5).map((t) => (
            <li key={t.id} className="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                onChange={() => void markDone(t)}
                className="h-4 w-4 accent-primary"
              />
              <span className="text-text">{t.title}</span>
            </li>
          ))}
          {open.length > 5 && (
            <li className="text-xs text-muted">+{open.length - 5} weitere</li>
          )}
        </ul>
      )}
    </WidgetCard>
  )
}
