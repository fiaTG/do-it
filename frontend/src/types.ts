export interface Family {
  id: number
  name: string
  is_premium: boolean
}

export interface Subscription {
  is_premium: boolean
  plan: string
  expires_at: string | null
}

export interface User {
  id: number
  first_name: string
  last_name: string
  email: string
  family_id: number | null
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
}

export interface ImageItem {
  id: number
  title: string | null
  url: string
  thumbnail_url: string
  created_by: number | null
  created_at: string
}

export interface Invite {
  id: number
  email: string
  family: Family | null
  expires_at: string | null
}
