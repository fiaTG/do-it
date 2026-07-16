import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { fuelApi } from '../../api'
import { APP_ICONS, Crown } from '../../lib/icons'
import { useAuth } from '../../store/auth'
import type { FuelStation } from '../../types'
import WidgetCard from './WidgetCard'

function price(value: number | false | null): string {
  return typeof value === 'number' ? `${value.toFixed(3).replace('.', ',')} €` : '–'
}

export default function FuelWidget({ onRemove }: { onRemove?: () => void }) {
  const user = useAuth((s) => s.user)
  const isPremium = user?.family?.is_premium ?? false
  const hasLocation = user?.family?.latitude != null && user?.family?.longitude != null
  const [stations, setStations] = useState<FuelStation[]>([])

  useEffect(() => {
    if (!isPremium || !hasLocation) return
    fuelApi
      .stations(5)
      .then((data) => setStations(data.stations))
      .catch(() => {})
  }, [isPremium, hasLocation])

  const cheapest = [...stations]
    .filter((s) => typeof s.diesel === 'number' && s.isOpen)
    .sort((a, b) => (a.diesel as number) - (b.diesel as number))
    .slice(0, 3)

  return (
    <WidgetCard title="Spritpreise" icon={APP_ICONS.fuel} to="/fuel" onRemove={onRemove}>
      {!isPremium ? (
        <p className="flex flex-wrap items-center gap-1.5 text-sm text-muted">
          <Crown className="h-4 w-4 shrink-0 text-primary" /> Premium-Feature –{' '}
          <Link
            to="/premium"
            onClick={(e) => e.stopPropagation()}
            className="text-primary hover:underline"
          >
            mehr erfahren
          </Link>
        </p>
      ) : !hasLocation ? (
        <p className="text-sm text-muted">Familienort festlegen, dann gibt’s hier Preise.</p>
      ) : cheapest.length === 0 ? (
        <p className="text-sm text-muted">Keine offenen Tankstellen im 5-km-Umkreis.</p>
      ) : (
        <ul className="space-y-1.5">
          {cheapest.map((s, i) => (
            <li key={s.id} className="flex items-center gap-2 text-sm">
              <span className="min-w-0 flex-1 truncate text-text">{s.brand || s.name}</span>
              <span className={`font-bold ${i === 0 ? 'text-primary' : 'text-text'}`}>
                {price(s.diesel)}
              </span>
            </li>
          ))}
          <li className="pt-0.5 text-[10px] text-muted">Diesel · 5 km · Daten: Tankerkönig.de</li>
        </ul>
      )}
    </WidgetCard>
  )
}
