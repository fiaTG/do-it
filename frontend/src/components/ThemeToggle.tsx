import { useTheme } from '../store/theme'

/** Kleiner Hell/Dunkel-Umschalter (ADR-0017). */
export default function ThemeToggle({ className = '' }: { className?: string }) {
  const theme = useTheme((s) => s.theme)
  const toggle = useTheme((s) => s.toggle)
  const dark = theme === 'dark'

  return (
    <button
      type="button"
      onClick={toggle}
      aria-label={dark ? 'Zu hellem Design wechseln' : 'Zu dunklem Design wechseln'}
      title={dark ? 'Helles Design' : 'Dunkles Design'}
      className={`flex h-8 w-8 items-center justify-center rounded-full text-base transition hover:bg-white/10 ${className}`}
    >
      <span aria-hidden>{dark ? '☀️' : '🌙'}</span>
    </button>
  )
}
