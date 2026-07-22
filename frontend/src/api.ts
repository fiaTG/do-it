import { api, ensureCsrf, isNative, setAuthToken } from './lib/api'
import type {
  AppItem,
  CalendarFeed,
  Contact,
  FeedEvent,
  FuelData,
  GameScores,
  EventItem,
  Family,
  ImageItem,
  ImagePage,
  Invite,
  Shop,
  ShoppingItem,
  Subscription,
  Todo,
  TodoPoints,
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

// Kennung des nativen Clients beim Ausstellen eines API-Tokens.
const DEVICE_NAME = 'Nidula Mobile'

export const authApi = {
  async login(email: string, password: string): Promise<User> {
    // Nativ: Token holen, speichern, dann Nutzer laden. Web: Cookie-Login.
    if (isNative) {
      const { data } = await api.post<{ token: string }>('/auth/login', {
        email,
        password,
        device_name: DEVICE_NAME,
      })
      await setAuthToken(data.token)
      return authApi.me()
    }
    await ensureCsrf()
    const { data } = await api.post<{ data: User }>('/auth/login', { email, password })
    return data.data
  },
  async register(payload: RegisterPayload): Promise<User> {
    if (isNative) {
      const { data } = await api.post<{ token: string }>('/auth/register', {
        ...payload,
        device_name: DEVICE_NAME,
      })
      await setAuthToken(data.token)
      return authApi.me()
    }
    await ensureCsrf()
    const { data } = await api.post<{ data: User }>('/auth/register', payload)
    return data.data
  },
  async logout(): Promise<void> {
    try {
      await api.post('/auth/logout')
    } finally {
      // Token nativ in jedem Fall lokal entfernen.
      if (isNative) await setAuthToken(null)
    }
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
  color: string | null
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
  /** DSGVO-Datenexport (Art. 15/20): eigene Daten als JSON-Blob. */
  async exportData(): Promise<Blob> {
    const { data } = await api.get('/me/export', { responseType: 'blob' })
    return data as Blob
  },
}

/** Direkt-URL zum PDF-Export der Einkaufsliste (per Cookie authentifiziert). */
export const shoppingPdfUrl = `${import.meta.env.VITE_API_URL}/shopping-items/pdf`

export const subscriptionApi = {
  async show(): Promise<Subscription> {
    const { data } = await api.get<{ data: Subscription }>('/subscription')
    return data.data
  },
  async activate(plan: 'monthly' | 'yearly' = 'monthly'): Promise<Subscription> {
    const { data } = await api.post<{ data: Subscription }>('/subscription', { plan })
    return data.data
  },
  async cancel(): Promise<void> {
    await api.delete('/subscription')
  },
}

export const familyApi = {
  async create(name: string): Promise<Family> {
    const { data } = await api.post<{ data: Family }>('/family', { name })
    return data.data
  },
  async members(): Promise<User[]> {
    const { data } = await api.get<{ data: User[] }>('/family/members')
    return data.data
  },
  async updateRole(userId: number, role: 'guardian' | 'child'): Promise<User> {
    const { data } = await api.patch<{ data: User }>(`/family/members/${userId}/role`, { role })
    return data.data
  },
  /** Heimatort fürs Wetter setzen (nur Verwalter). */
  async updateLocation(payload: {
    location_name: string
    latitude: number
    longitude: number
  }): Promise<Family> {
    const { data } = await api.patch<{ data: Family }>('/family/location', payload)
    return data.data
  },
}

export interface ContactPayload {
  name: string
  category: string | null
  phone: string | null
  email: string | null
  website: string | null
  address: string | null
  notes: string | null
  photo?: File | null
}

function contactForm(payload: ContactPayload): FormData {
  const form = new FormData()
  form.append('name', payload.name)
  for (const key of ['category', 'phone', 'email', 'website', 'address', 'notes'] as const) {
    const value = payload[key]
    if (value) form.append(key, value)
  }
  if (payload.photo) form.append('photo', payload.photo)
  return form
}

export const fuelApi = {
  /** Spritpreise rund um den Familienort (Premium; on demand, serverseitig gecacht).
   *  Liefert immer alle Sorten je Station – Sortierung/Auswahl macht die UI. */
  async stations(rad: number): Promise<FuelData> {
    const { data } = await api.get<{ data: FuelData }>('/fuel-stations', { params: { rad } })
    return data.data
  },
}

export const gamesApi = {
  async scores(game: string): Promise<GameScores> {
    const { data } = await api.get<{ data: GameScores }>(`/games/${game}/scores`)
    return data.data
  },
  async submit(
    game: string,
    score: number,
  ): Promise<{ personal_record: boolean; family_record: boolean }> {
    const { data } = await api.post<{
      data: { personal_record: boolean; family_record: boolean }
    }>(`/games/${game}/scores`, { score })
    return data.data
  },
}

export const contactsApi = {
  async list(): Promise<Contact[]> {
    const { data } = await api.get<{ data: Contact[] }>('/contacts')
    return data.data
  },
  async create(payload: ContactPayload): Promise<Contact> {
    const { data } = await api.post<{ data: Contact }>('/contacts', contactForm(payload))
    return data.data
  },
  async update(id: number, payload: ContactPayload): Promise<Contact> {
    // Multipart + PATCH verträgt sich nicht (PHP parst den Body nicht) –
    // daher POST mit Laravel-Method-Spoofing.
    const form = contactForm(payload)
    form.append('_method', 'PATCH')
    const { data } = await api.post<{ data: Contact }>(`/contacts/${id}`, form)
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/contacts/${id}`)
  },
}

export const inviteApi = {
  async create(email: string, role: 'guardian' | 'child' = 'guardian'): Promise<Invite> {
    const { data } = await api.post<{ data: Invite }>('/invites', { email, role })
    return data.data
  },
  /** Offene Einladungen der Familie (nicht eingelöst, nicht abgelaufen). */
  async list(): Promise<Invite[]> {
    const { data } = await api.get<{ data: Invite[] }>('/invites')
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/invites/${id}`)
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
  /** Nest-Blätter-Stände (ADR-0026): Woche + Gesamt je Mitglied. */
  async points(): Promise<TodoPoints> {
    const { data } = await api.get<{ data: TodoPoints }>('/todos/points')
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
    car_reserved: boolean
    owner_id: number
    recurrence: string | null
    recurrence_until: string | null
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
      car_reserved: boolean
      owner_id: number
      recurrence: string | null
      recurrence_until: string | null
    }>,
  ): Promise<EventItem> {
    const { data } = await api.patch<{ data: EventItem }>(`/events/${id}`, payload)
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/events/${id}`)
  },
}

export interface CalendarFeedPayload {
  name: string
  color: string
  /** Entweder url (Abo) ODER file (einmaliger Import). */
  url?: string
  file?: File | null
}

export const calendarFeedsApi = {
  async list(): Promise<CalendarFeed[]> {
    const { data } = await api.get<{ data: CalendarFeed[] }>('/calendar-feeds')
    return data.data
  },
  async create(payload: CalendarFeedPayload): Promise<CalendarFeed> {
    if (payload.file) {
      const form = new FormData()
      form.append('name', payload.name)
      form.append('color', payload.color)
      form.append('file', payload.file)
      const { data } = await api.post<{ data: CalendarFeed }>('/calendar-feeds', form)
      return data.data
    }
    const { data } = await api.post<{ data: CalendarFeed }>('/calendar-feeds', {
      name: payload.name,
      color: payload.color,
      url: payload.url,
    })
    return data.data
  },
  /** Manuell neu laden (nur URL-Abos). */
  async refresh(id: number): Promise<CalendarFeed> {
    const { data } = await api.post<{ data: CalendarFeed }>(`/calendar-feeds/${id}/refresh`)
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/calendar-feeds/${id}`)
  },
  /** Serverseitig expandierte Abo-Termine im Zeitfenster (Premium). */
  async events(from: Date, to: Date): Promise<FeedEvent[]> {
    const { data } = await api.get<{ data: { events: FeedEvent[] } }>('/calendar-feeds/events', {
      params: { from: from.toISOString(), to: to.toISOString() },
    })
    return data.data.events
  },
}

/** Kalender-Freigabe (ADR-0024, Premium): geheime .ics-Abo-URL der Familie. */
export interface CalendarExportState {
  enabled: boolean
  url: string | null
}

export const calendarExportApi = {
  async show(): Promise<CalendarExportState> {
    const { data } = await api.get<{ data: CalendarExportState }>('/calendar-export')
    return data.data
  },
  /** Aktivieren bzw. neue Adresse erzeugen – alte URL wird sofort ungültig. */
  async rotate(): Promise<CalendarExportState> {
    const { data } = await api.post<{ data: CalendarExportState }>('/calendar-export/rotate')
    return data.data
  },
  async disable(): Promise<void> {
    await api.delete('/calendar-export')
  },
}

export const imagesApi = {
  /** Seitenweise (60/Seite), sortiert nach Aufnahme- (Fallback: Upload-)Datum. */
  async list(page = 1): Promise<ImagePage> {
    const { data } = await api.get<{
      data: ImageItem[]
      meta: { current_page: number; last_page: number; total: number; limit: number | null }
    }>('/images', { params: { page } })
    return {
      images: data.data,
      currentPage: data.meta.current_page,
      lastPage: data.meta.last_page,
      total: data.meta.total,
      limit: data.meta.limit,
    }
  },
  /** Frisches Bild mit neu signierten URLs holen (z. B. nach Ablauf der 60-Minuten-Signatur). */
  async show(id: number): Promise<ImageItem> {
    const { data } = await api.get<{ data: ImageItem }>(`/images/${id}`)
    return data.data
  },
  async upload(file: File, title: string, onProgress?: (percent: number) => void): Promise<ImageItem> {
    const form = new FormData()
    form.append('image', file)
    if (title) form.append('title', title)
    const { data } = await api.post<{ data: ImageItem }>('/images', form, {
      onUploadProgress: (event) => {
        // event.total kann fehlen (z. B. chunked Transfer) – dann kein Prozentwert.
        if (onProgress && event.total) onProgress(Math.round((event.loaded / event.total) * 100))
      },
    })
    return data.data
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/images/${id}`)
  },
  async batchRemove(ids: number[]): Promise<void> {
    // Das Backend nimmt max. 100 IDs pro Request (Validierung) – größere
    // Auswahlen daher in Blöcken löschen.
    for (let i = 0; i < ids.length; i += 100) {
      await api.post('/images/batch-delete', { ids: ids.slice(i, i + 100) })
    }
  },
  /** Papierkorb (ADR-0020): zuletzt gelöschte zuerst. */
  async trash(): Promise<ImageItem[]> {
    const { data } = await api.get<{ data: ImageItem[] }>('/images/trash')
    return data.data
  },
  async restore(ids: number[]): Promise<void> {
    for (let i = 0; i < ids.length; i += 100) {
      await api.post('/images/restore', { ids: ids.slice(i, i + 100) })
    }
  },
  /** Endgültig löschen – nur für Bilder, die bereits im Papierkorb liegen. */
  async purge(ids: number[]): Promise<void> {
    for (let i = 0; i < ids.length; i += 100) {
      await api.post('/images/purge', { ids: ids.slice(i, i + 100) })
    }
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
