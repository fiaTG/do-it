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

  async function markDone(todo: Todo) {
    await todosApi.update(todo.id, { is_done: true })
    load()
  }

  return (
    <WidgetCard
      title={`ToDos${open.length ? ` (${open.length})` : ''}`}
      icon="✅"
      to="/todos"
      onRemove={onRemove}
    >
      {open.length === 0 ? (
        <p className="text-sm text-slate-400">Alles erledigt 🎉</p>
      ) : (
        <ul className="space-y-2">
          {open.slice(0, 5).map((t) => (
            <li key={t.id} className="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                onChange={() => void markDone(t)}
                className="h-4 w-4 accent-brand"
              />
              <span className="text-slate-700">{t.title}</span>
            </li>
          ))}
          {open.length > 5 && (
            <li className="text-xs text-slate-400">+{open.length - 5} weitere</li>
          )}
        </ul>
      )}
    </WidgetCard>
  )
}
