import { beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('../api', () => ({
  authApi: {
    login: vi.fn(),
    logout: vi.fn(),
    me: vi.fn(),
    register: vi.fn(),
  },
}))
vi.mock('../lib/api', () => ({ ensureCsrf: vi.fn() }))

import { authApi } from '../api'
import { useAuth } from '../store/auth'
import type { User } from '../types'

const demoUser: User = {
  id: 1,
  first_name: 'Doris',
  last_name: 'Dozent',
  email: 'dozent@example.com',
  family_id: 1,
  family: { id: 1, name: 'Musterfamilie' },
  avatar_url: null,
  birthdate: null,
  gender: null,
  socials: { facebook: null, instagram: null, linkedin: null },
  created_at: '2026-01-01T00:00:00+00:00',
}

describe('auth store', () => {
  beforeEach(() => {
    useAuth.setState({ user: null, loading: false })
    vi.clearAllMocks()
  })

  it('sets the user after a successful login', async () => {
    vi.mocked(authApi.login).mockResolvedValue(demoUser)

    await useAuth.getState().login('dozent@example.com', 'test123!')

    expect(authApi.login).toHaveBeenCalledWith('dozent@example.com', 'test123!')
    expect(useAuth.getState().user).toEqual(demoUser)
  })

  it('clears the user on logout', async () => {
    useAuth.setState({ user: demoUser, loading: false })
    vi.mocked(authApi.logout).mockResolvedValue(undefined)

    await useAuth.getState().logout()

    expect(useAuth.getState().user).toBeNull()
  })
})
