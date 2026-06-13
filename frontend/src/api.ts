import { api, ensureCsrf } from './lib/api'
import type {
  AppItem,
  EventItem,
  Family,
  ImageItem,
  Invite,
  Shop,
  ShoppingItem,
  Todo,
  User,
} from './types'

export interface RegisterPayload {
  first_name: string
  last_name: string
  email: string
  password: string
  password_confirmation: string
  token?: string
}

export const authApi = {
  async login(email: string, password: string): Promise<User> {
    await ensureCsrf()
    const { data } = await api.post<{ data: User }>('/auth/login', { email, password })
    return data.data
  },
  async register(payload: RegisterPayload): Promise<User> {
    await ensureCsrf()
    const { data } = await api.post<{ data: User }>('/auth/register', payload)
    return data.data
  },
  async logout(): Promise<void> {
    await api.post('/auth/logout')
  },
  async me(): Promise<User> {
    const { data } = await api.get<{ data: User }>('/auth/me')
    return data.data
  },
  async updatePassword(payload: {
    current_password: string
    password: string
    password_confirmation: string
  }): Promise<void> {
    await api.put('/auth/password', payload)
  },
}

export interface ProfilePayload {
  first_name: string
  last_name: string
  birthdate: string | null
  gender: string | null
  facebook: string | null
  instagram: string | null
  linkedin: string | null
}

export const profileApi = {
  async update(payload: ProfilePayload): Promise<User> {
    const { data } = await api.put<{ data: User }>('/profile', payload)
    return data.data
  },
  async avatar(file: File): Promise<User> {
    const form = new FormData()
    form.append('avatar', file)
    const { data } = await api.post<{ data: User }>('/profile/avatar', form)
    return data.data
  },
}

/** Direkt-URL zum PDF-Export der Einkaufsliste (per Cookie authentifiziert). */
export const shoppingPdfUrl = `${import.meta.env.VITE_API_URL}/shopping-items/pdf`

export const familyApi = {
  async create(name: string): Promise<Family> {
    const { data } = await api.post<{ data: Family }>('/family', { name })
    return data.data
  },
  async members(): Promise<User[]> {
    const { data } = await api.get<{ data: User[] }>('/family/members')
    return data.data
  },
}

export const inviteApi = {
  async create(email: string): Promise<Invite> {
    const { data } = await api.post<{ data: Invite }>('/invites', { email })
    return data.data
  },
  async show(token: string): Promise<Invite> {
    const { data } = await api.get<{ data: Invite }>(`/invites/${token}`)
    return data.data
  },
}

export const appsApi = {
  async catalog(): Promise<AppItem[]> {
    const { data } = await api.get<{ data: AppItem[] }>('/apps')
    return data.data
  },
  async mine(): Promise<AppItem[]> {
    const { data } = await api.get<{ data: AppItem[] }>('/me/apps')
    return data.data
  },
  async add(appId: number): Promise<void> {
    await api.post('/me/apps', { app_id: appId })
  },
  async remove(appId: number): Promise<void> {
    await api.delete(`/me/apps/${appId}`)
  },
}

export const shopsApi = {
  async list(): Promise<Shop[]> {
    const { data } = await api.get<{ data: Shop[] }>('/shops')
    return data.data
  },
}

export const shoppingApi = {
  async list(): Promise<ShoppingItem[]> {
    const { data } = await api.get<{ data: ShoppingItem[] }>('/shopping-items')
    return data.data
  },
  async create(payload: { name: string; quantity: number; shop_id: number | null }): Promise<ShoppingItem> {
    const { data } = await api.post<{ data: ShoppingItem }>('/shopping-items', payload)
    return data.data
  },
  async update(id: number, payload: Partial<{ name: string; quantity: number; shop_id: number | null; is_purchased: boolean }>): Promise<ShoppingItem> {
    const { data } = await api.patch<{ data: ShoppingItem }>(`/shopping-items/${id}`, payload)
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/shopping-items/${id}`)
  },
}

export const todosApi = {
  async list(): Promise<Todo[]> {
    const { data } = await api.get<{ data: Todo[] }>('/todos')
    return data.data
  },
  async create(title: string): Promise<Todo> {
    const { data } = await api.post<{ data: Todo }>('/todos', { title })
    return data.data
  },
  async update(id: number, payload: Partial<{ title: string; is_done: boolean }>): Promise<Todo> {
    const { data } = await api.patch<{ data: Todo }>(`/todos/${id}`, payload)
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/todos/${id}`)
  },
}

export const eventsApi = {
  async list(): Promise<EventItem[]> {
    const { data } = await api.get<{ data: EventItem[] }>('/events')
    return data.data
  },
  async create(payload: {
    title: string
    starts_at: string
    ends_at: string
    category: string
    car_reserved: boolean
  }): Promise<EventItem> {
    const { data } = await api.post<{ data: EventItem }>('/events', payload)
    return data.data
  },
  async update(
    id: number,
    payload: Partial<{
      title: string
      starts_at: string
      ends_at: string
      category: string
      car_reserved: boolean
    }>,
  ): Promise<EventItem> {
    const { data } = await api.patch<{ data: EventItem }>(`/events/${id}`, payload)
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/events/${id}`)
  },
}

export const imagesApi = {
  async list(): Promise<ImageItem[]> {
    const { data } = await api.get<{ data: ImageItem[] }>('/images')
    return data.data
  },
  async upload(file: File, title: string): Promise<ImageItem> {
    const form = new FormData()
    form.append('image', file)
    if (title) form.append('title', title)
    const { data } = await api.post<{ data: ImageItem }>('/images', form)
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/images/${id}`)
  },
}

/** Extrahiert eine lesbare Fehlermeldung aus einer Axios-Fehlerantwort. */
export function apiError(error: unknown, fallback = 'Etwas ist schiefgelaufen.'): string {
  if (axiosLike(error)) {
    return error.response?.data?.message ?? fallback
  }
  return fallback
}

function axiosLike(
  error: unknown,
): error is { response?: { data?: { message?: string } } } {
  return typeof error === 'object' && error !== null && 'response' in error
}
