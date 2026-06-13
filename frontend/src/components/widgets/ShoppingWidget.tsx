import { useEffect, useState } from 'react'
import { shoppingApi } from '../../api'
import type { ShoppingItem } from '../../types'
import WidgetCard from './WidgetCard'

export default function ShoppingWidget({ onRemove }: { onRemove?: () => void }) {
  const [items, setItems] = useState<ShoppingItem[]>([])

  function load() {
    shoppingApi.list().then(setItems).catch(() => {})
  }

  useEffect(() => {
    load()
  }, [])

  const open = items.filter((i) => !i.is_purchased)

  async function markPurchased(item: ShoppingItem) {
    await shoppingApi.update(item.id, { is_purchased: true })
    load()
  }

  return (
    <WidgetCard
      title={`Einkaufsliste${open.length ? ` (${open.length})` : ''}`}
      icon="🛒"
      to="/shopping"
      onRemove={onRemove}
    >
      {open.length === 0 ? (
        <p className="text-sm text-muted">Nichts zu besorgen.</p>
      ) : (
        <ul className="space-y-2">
          {open.slice(0, 5).map((item) => (
            <li key={item.id} className="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                onChange={() => void markPurchased(item)}
                className="h-4 w-4 accent-brand"
              />
              <span className="text-text">
                {item.name} <span className="text-muted">×{item.quantity}</span>
                {item.shop && <span className="ml-1 text-xs text-muted">@ {item.shop.name}</span>}
              </span>
            </li>
          ))}
          {open.length > 5 && (
            <li className="text-xs text-muted">+{open.length - 5} weitere</li>
          )}
        </ul>
      )}
    </WidgetCard>
  )
}
