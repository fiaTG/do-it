/**
 * Nidula-Markenzeichen: gerendertes Logo-Badge (Nest mit Haus & Herz auf
 * Salbei-Grün) + Wortmarke. Nutzt `public/logo-mark.png` – die ENG getrimmte
 * Variante (Marke füllt die Kachel, gut erkennbar ab 24px), nicht das maskable
 * App-Icon `public/icon.png` (das braucht Rand). Bewusst das Badge statt der
 * cremefarbenen Roh-Marke, weil die auf hellem Grund verschwinden würde.
 */
export default function Logo({
  size = 28,
  className = '',
  withText = true,
}: {
  size?: number
  className?: string
  withText?: boolean
}) {
  return (
    <span className={`inline-flex items-center gap-2 ${className}`}>
      <img
        src="/logo-mark.png"
        alt="Nidula"
        width={size}
        height={size}
        style={{ width: size, height: size }}
        className="rounded-lg"
      />
      {withText && <span className="font-serif">Nidula</span>}
    </span>
  )
}
