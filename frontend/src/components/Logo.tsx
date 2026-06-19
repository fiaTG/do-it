/**
 * Nidula-Markenzeichen: gerendertes Logo-Badge (Nest mit Haus & Herz auf
 * Salbei-Grün) + Wortmarke. Nutzt bewusst das generierte App-Icon
 * (`public/icon.png`) statt der transparenten Roh-Marke (`brand/nidula-mark.png`),
 * weil die Marke cremefarben ist und auf hellen Hintergründen verschwinden
 * würde – das Badge hat auf hell wie dunkel garantiert Kontrast.
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
        src="/icon.png"
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
