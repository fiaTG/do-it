import { beforeEach, describe, expect, it, vi } from 'vitest'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import TodosPage from '../pages/TodosPage'
import { familyApi, todosApi } from '../api'
import { useAuth } from '../store/auth'
import type { Todo } from '../types'

vi.mock('../api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('../api')>()
  return {
    ...actual,
    // Nest-Blätter (ADR-0026): die Seite lädt jetzt auch Mitglieder + Punkte.
    todosApi: { ...actual.todosApi, list: vi.fn(), update: vi.fn(), points: vi.fn() },
    familyApi: { ...actual.familyApi, members: vi.fn() },
  }
})

const todo: Todo = {
  id: 1,
  title: 'Müll rausbringen',
  is_done: false,
  created_by: 1,
  created_at: '2026-06-13T00:00:00Z',
}

function renderPage() {
  return render(
    <MemoryRouter>
      <TodosPage />
    </MemoryRouter>,
  )
}

describe('TodosPage – Optimistic UI', () => {
  beforeEach(() => {
    useAuth.setState({ user: { id: 1 } as never, loading: false })
    vi.clearAllMocks()
    vi.mocked(todosApi.list).mockResolvedValue([todo])
    vi.mocked(todosApi.points).mockResolvedValue({ week: {}, totals: {} })
    vi.mocked(familyApi.members).mockResolvedValue([])
  })

  it('hakt sofort optimistisch ab', async () => {
    vi.mocked(todosApi.update).mockResolvedValue({ ...todo, is_done: true })
    renderPage()

    const checkbox = await screen.findByRole('checkbox')
    expect(checkbox).not.toBeChecked()

    fireEvent.click(checkbox)
    // Sofort sichtbar – ohne auf die API zu warten.
    expect(checkbox).toBeChecked()
    expect(todosApi.update).toHaveBeenCalledWith(1, { is_done: true })
  })

  it('macht den Haken bei API-Fehler rückgängig und zeigt eine Meldung', async () => {
    vi.mocked(todosApi.update).mockRejectedValue({
      response: { data: { message: 'Speichern fehlgeschlagen.' } },
    })
    renderPage()

    const checkbox = await screen.findByRole('checkbox')
    fireEvent.click(checkbox)
    expect(checkbox).toBeChecked() // optimistisch

    await waitFor(() => {
      expect(screen.getByText('Speichern fehlgeschlagen.')).toBeInTheDocument()
    })
    expect(checkbox).not.toBeChecked() // Rollback
  })
})
