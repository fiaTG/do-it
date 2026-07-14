import type { User } from '../types'

/**
 * Nidula-Personenpalette (warm-erdig, ADR-0018) – Farbe = WER, nicht welcher
 * Lebensbereich. Jedes Mitglied kann seine Farbe im Profil wählen; ohne Wahl
 * greift ein ID-basierter Fallback, der stabil bleibt, wenn Mitglieder
 * dazukommen oder gehen (früher: Listenposition -> Farben verschoben sich).
 */
export const MEMBER_PALETTE = [
  '#3E7C9B',
  '#E58A72',
  '#8FCBB8',
  '#F4C95D',
  '#A9825A',
  '#9B6FB0',
  '#5BA88A',
  '#D08770',
]

export const FALLBACK_COLOR = '#5b7689'

export function memberColor(member: User | undefined | null): string {
  if (!member) return FALLBACK_COLOR
  return member.color ?? MEMBER_PALETTE[member.id % MEMBER_PALETTE.length]
}

/** Dezente Hintergrund-Tönung einer Personenfarbe (Spalten, Chips). */
export function memberTint(color: string): string {
  return `${color}1a` // ~10 % Alpha als Hex-Suffix
}
