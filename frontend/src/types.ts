export interface Family {
  id: number
  name: string
  is_premium: boolean
  /** Heimatort fürs Wetter-Widget (von Verwaltern gepflegt). */
  location_name: string | null
  latitude: number | null
  longitude: number | null
}

export interface Subscription {
  is_premium: boolean
  plan: string
  expires_at: string | null
}

export type FamilyRole = 'guardian' | 'child'

export interface User {
  id: number
  first_name: string
  last_name: string
  email: string
  family_id: number | null
  role: FamilyRole
  /** Persönliche Kalenderfarbe (Hex); null = ID-basierter Palette-Fallback. */
  color: string | null
  family?: Family | null
  avatar_url: string | null
  birthdate: string | null
  gender: string | null
  socials: {
    facebook: string | null
    instagram: string | null
    linkedin: string | null
  }
  created_at: string
}

export interface AppItem {
  id: number
  slug: string
  name: string
  icon: string | null
}

export interface Shop {
  id: number
  name: string
}

export interface ShoppingItem {
  id: number
  name: string
  quantity: number
  is_purchased: boolean
  shop: Shop | null
  created_by: number | null
  created_at: string
}

export interface Todo {
  id: number
  title: string
  is_done: boolean
  created_by: number | null
  created_at: string
}

export interface EventItem {
  id: number
  title: string
  starts_at: string
  ends_at: string
  category: string
  car_reserved: boolean
  created_by: number | null
  owner_id: number | null
  owner_name: string | null
  /** Serie (ADR-lose Produktentscheidung): null = einmalig. */
  recurrence: 'daily' | 'weekly' | 'biweekly' | 'monthly' | 'yearly' | null
  recurrence_until: string | null
}

export interface ImageVariant {
  width: number
  url: string
}

export interface ImageItem {
  id: number
  title: string | null
  url: string
  thumbnail_url: string
  srcset: ImageVariant[]
  created_by: number | null
  created_at: string
  taken_at: string | null
  width: number | null
  height: number | null
  /** Blur-up-Platzhalter als data-URI; null bei Altbestand/laufender Verarbeitung. */
  placeholder: string | null
  /** true, solange der Thumbnail-Job noch nicht durchgelaufen ist. */
  processing: boolean
  /** Nur im Papierkorb gesetzt (ADR-0020). */
  deleted_at: string | null
  expires_at: string | null
}

export interface ImagePage {
  images: ImageItem[]
  currentPage: number
  lastPage: number
  /** Free-Tier-Limit der Galerie, `null` = Premium/unbegrenzt. */
  limit: number | null
  /** Gesamtzahl aller Bilder der Familie (nicht nur diese Seite). */
  total: number
}

export interface FuelStation {
  id: string
  name: string
  brand: string
  street: string
  houseNumber: string | null
  postCode: number
  place: string
  dist: number
  isOpen: boolean
  e5: number | false | null
  e10: number | false | null
  diesel: number | false | null
}

export interface FuelData {
  stations: FuelStation[]
  fetched_at: string
}

export interface GameScoreEntry {
  user_id: number
  score: number
}

export interface GameScores {
  top: GameScoreEntry[]
  my_best: number | null
}

export interface Contact {
  id: number
  name: string
  category: string | null
  phone: string | null
  email: string | null
  website: string | null
  address: string | null
  notes: string | null
  photo_url: string | null
  created_by: number | null
  created_at: string | null
}

/** Kalender-Abo (ADR-0023, Premium): externer iCal-Kalender als Lese-Ebene. */
export interface CalendarFeed {
  id: number
  name: string
  /** Layer-Farbe (Hex) für alle Termine dieses Abos. */
  color: string
  url: string | null
  /** true = URL-Abo (wird aktualisiert), false = einmaliger Datei-Import. */
  is_subscription: boolean
  last_synced_at: string | null
  /** Letzter Sync-Fehler (deutsch) – null, wenn alles ok. */
  last_error: string | null
  created_by: number | null
  created_at: string | null
}

/** Einzelnes, serverseitig expandiertes Vorkommen aus einem Kalender-Abo. */
export interface FeedEvent {
  id: string
  feed_id: number
  uid: string
  title: string
  /** Ganztägig: reines Datum (YYYY-MM-DD), sonst ISO mit Zeit (UTC). */
  starts_at: string
  /** Ende exklusiv bei Ganztagsterminen (RFC 5545 = FullCalendar). */
  ends_at: string
  all_day: boolean
  location: string | null
}

export interface Invite {
  id: number
  email: string
  role: FamilyRole
  family: Family | null
  created_at: string | null
  expires_at: string | null
}
