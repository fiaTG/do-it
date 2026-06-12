import { beforeEach, describe, expect, it, vi } from 'vitest'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import LoginPage from '../pages/LoginPage'
import { authApi } from '../api'
import { useAuth } from '../store/auth'

vi.mock('../api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('../api')>()
  return { ...actual, authApi: { ...actual.authApi, login: vi.fn() } }
})

function renderLogin() {
  return render(
    <MemoryRouter>
      <LoginPage />
    </MemoryRouter>,
  )
}

describe('LoginPage', () => {
  beforeEach(() => {
    useAuth.setState({ user: null, loading: false })
    vi.clearAllMocks()
  })

  it('renders the login form', () => {
    renderLogin()
    expect(screen.getByText('Willkommen zurück')).toBeInTheDocument()
    expect(screen.getByLabelText('E-Mail')).toBeInTheDocument()
    expect(screen.getByLabelText('Passwort')).toBeInTheDocument()
  })

  it('shows an error message when the login fails', async () => {
    vi.mocked(authApi.login).mockRejectedValue({
      response: { data: { message: 'Login fehlgeschlagen.' } },
    })

    renderLogin()
    fireEvent.change(screen.getByLabelText('E-Mail'), { target: { value: 'a@b.de' } })
    fireEvent.change(screen.getByLabelText('Passwort'), { target: { value: 'secret1!' } })
    fireEvent.click(screen.getByRole('button', { name: /anmelden/i }))

    await waitFor(() => {
      expect(screen.getByText('Login fehlgeschlagen.')).toBeInTheDocument()
    })
    expect(authApi.login).toHaveBeenCalledWith('a@b.de', 'secret1!')
  })
})
