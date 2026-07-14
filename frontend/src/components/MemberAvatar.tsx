import { memberColor } from '../lib/memberColors'
import type { User } from '../types'

const SIZES = {
  xs: 'h-5 w-5 text-[9px]',
  sm: 'h-7 w-7 text-[11px]',
  md: 'h-9 w-9 text-sm',
  lg: 'h-16 w-16 text-xl',
} as const

/**
 * Rundes Mitglieder-Bild mit Ring in der Personenfarbe; ohne Profilbild
 * erscheinen die Initialen auf der Personenfarbe. Macht im Kalender auf einen
 * Blick klar, WER gemeint ist (Farbe + Gesicht statt nur Farbpunkt).
 */
export default function MemberAvatar({
  member,
  size = 'sm',
}: {
  member: User
  size?: keyof typeof SIZES
}) {
  const color = memberColor(member)
  const initials = `${member.first_name[0] ?? ''}${member.last_name[0] ?? ''}`.toUpperCase()

  return (
    <span
      className={`inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full font-semibold text-white ring-2 ${SIZES[size]}`}
      style={{ background: color, ['--tw-ring-color' as string]: color }}
      aria-hidden="true"
    >
      {member.avatar_url ? (
        <img src={member.avatar_url} alt="" className="h-full w-full object-cover" />
      ) : (
        initials
      )}
    </span>
  )
}
