import { useEffect, useState, type FormEvent } from 'react'
import { apiError, shoppingApi, shoppingPdfUrl, shopsApi } from '../api'
import ErrorBanner from '../components/ErrorBanner'
import { useAuth } from '../store/auth'
import type { Shop, ShoppingItem } from '../types'

export default function ShoppingListPage() {
  const userId = useAuth((s) => s.user?.id)
  const [items, setItems] = useState<ShoppingItem[]>([])
  const [shops, setShops] = useState<Shop[]>([])
  const [name, setName] = useState('')
  const [quantity, setQuantity] = useState(1)
  const [shopId, setShopId] = useState('')
  const [error, setError] = useState('')

  async function load() {
    try {
      setItems(await shoppingApi.list())
      setShops(await shopsApi.list())
    } catch (err) {
      setError(apiError(err))
    }
  }

  useEffect(() => {
    void load()
  }, [])

  // Optimistisch anlegen. Der Server führt gleichnamige Artikel zusammen
  // (Mengen-Merge) – darum ersetzen wir den Platzhalter entweder durch einen
  // bestehenden Eintrag (Merge) oder hängen den neuen an.
  async function add(e: FormEvent) {
    e.preventDefault()
    const value = name.trim()
    if (!value) return
    const sid = shopId === '' ? null : Number(shopId)
    const shop = shops.find((s) => s.id === sid) ?? null
    const temp: ShoppingItem = {
      id: -Date.now(),
      name: value,
      quantity,
      is_purchased: false,
      shop,
      created_by: userId ?? null,
      created_at: new Date().toISOString(),
    }
    setName('')
    setQuantity(1)
    setShopId('')
    setItems((prev) => [...prev, temp])
    try {
      const created = await shoppingApi.create({ name: value, quantity, shop_id: sid })
      setItems((prev) => {
        const base = prev.filter((i) => i.id !== temp.id)
        const merged = base.some((i) => i.id === created.id)
        return merged
          ? base.map((i) => (i.id === created.id ? created : i))
          : [...base, created]
      })
    } catch (err) {
      setItems((prev) => prev.filter((i) => i.id !== temp.id))
      setName(value)
      setQuantity(temp.quantity)
      setError(apiError(err))
    }
  }

  // Optimistisch abhaken: Zustand sofort umschalten, bei Fehler zurückdrehen.
  async function toggle(item: ShoppingItem) {
    const next = !item.is_purchased
    setItems((prev) => prev.map((i) => (i.id === item.id ? { ...i, is_purchased: next } : i)))
    try {
      await shoppingApi.update(item.id, { is_purchased: next })
    } catch (err) {
      setItems((prev) =>
        prev.map((i) => (i.id === item.id ? { ...i, is_purchased: item.is_purchased } : i)),
      )
      setError(apiError(err))
    }
  }

  // Optimistisch löschen: sofort ausblenden, bei Fehler wiederherstellen.
  async function remove(item: ShoppingItem) {
    setItems((prev) => prev.filter((i) => i.id !== item.id))
    try {
      await shoppingApi.remove(item.id)
    } catch (err) {
      setItems((prev) => [...prev, item].sort((a, b) => a.id - b.id))
      setError(apiError(err))
    }
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-primary">🛒 Einkaufsliste</h1>
        <a
          href={shoppingPdfUrl}
          target="_blank"
          rel="noreferrer"
          className="rounded-lg border border-primary px-3 py-1.5 text-sm text-primary hover:bg-primary/10"
        >
          📄 PDF
        </a>
      </div>

      <ErrorBanner message={error} onDismiss={() => setError('')} />

      <form onSubmit={add} className="flex flex-wrap items-end gap-2 rounded-2xl bg-surface p-4 shadow">
        <input
          placeholder="Artikel"
          required
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="flex-1 rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
        />
        <input
          type="number"
          min={1}
          value={quantity}
          onChange={(e) => setQuantity(Number(e.target.value))}
          className="w-20 rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
        />
        <select
          value={shopId}
          onChange={(e) => setShopId(e.target.value)}
          className="rounded-lg border border-border px-3 py-2 outline-none focus:border-primary"
        >
          <option value="">Shop …</option>
          {shops.map((s) => (
            <option key={s.id} value={s.id}>
              {s.name}
            </option>
          ))}
        </select>
        <button className="rounded-lg bg-primary px-4 py-2 font-semibold text-white hover:bg-primary-hover">
          + Hinzufügen
        </button>
      </form>

      <ul className="divide-y divide-border rounded-2xl bg-surface shadow">
        {items.length === 0 && <li className="p-4 text-muted">Liste ist leer.</li>}
        {items.map((item) => {
          const pending = item.id < 0 // optimistischer Platzhalter
          return (
            <li key={item.id} className={`flex items-center gap-3 p-4 ${pending ? 'opacity-60' : ''}`}>
              <input
                type="checkbox"
                checked={item.is_purchased}
                disabled={pending}
                onChange={() => void toggle(item)}
                className="h-5 w-5 accent-primary"
              />
              <span className={item.is_purchased ? 'flex-1 text-muted line-through' : 'flex-1'}>
                {item.name} <span className="text-muted">×{item.quantity}</span>
                {item.shop && <span className="ml-2 text-xs text-muted">@ {item.shop.name}</span>}
              </span>
              {!pending && item.created_by === userId && (
                <button
                  onClick={() => void remove(item)}
                  className="text-muted hover:text-red-500"
                  aria-label="Löschen"
                >
                  🗑️
                </button>
              )}
            </li>
          )
        })}
      </ul>
    </div>
  )
}
