import { useEffect, useState, type FormEvent } from 'react'
import { apiError, shoppingApi, shoppingPdfUrl, shopsApi } from '../api'
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

  async function add(e: FormEvent) {
    e.preventDefault()
    await shoppingApi.create({
      name,
      quantity,
      shop_id: shopId === '' ? null : Number(shopId),
    })
    setName('')
    setQuantity(1)
    setShopId('')
    await load()
  }

  async function toggle(item: ShoppingItem) {
    await shoppingApi.update(item.id, { is_purchased: !item.is_purchased })
    await load()
  }

  async function remove(id: number) {
    await shoppingApi.remove(id)
    await load()
  }

  if (error) return <p className="text-red-600">{error}</p>

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-brand">🛒 Einkaufsliste</h1>
        <a
          href={shoppingPdfUrl}
          target="_blank"
          rel="noreferrer"
          className="rounded-lg border border-brand px-3 py-1.5 text-sm text-brand hover:bg-brand/10"
        >
          📄 PDF
        </a>
      </div>

      <form onSubmit={add} className="flex flex-wrap items-end gap-2 rounded-2xl bg-white p-4 shadow">
        <input
          placeholder="Artikel"
          required
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="flex-1 rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
        />
        <input
          type="number"
          min={1}
          value={quantity}
          onChange={(e) => setQuantity(Number(e.target.value))}
          className="w-20 rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
        />
        <select
          value={shopId}
          onChange={(e) => setShopId(e.target.value)}
          className="rounded-lg border border-slate-300 px-3 py-2 outline-none focus:border-brand"
        >
          <option value="">Shop …</option>
          {shops.map((s) => (
            <option key={s.id} value={s.id}>
              {s.name}
            </option>
          ))}
        </select>
        <button className="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-dark">
          + Hinzufügen
        </button>
      </form>

      <ul className="divide-y rounded-2xl bg-white shadow">
        {items.length === 0 && <li className="p-4 text-slate-500">Liste ist leer.</li>}
        {items.map((item) => (
          <li key={item.id} className="flex items-center gap-3 p-4">
            <input
              type="checkbox"
              checked={item.is_purchased}
              onChange={() => void toggle(item)}
              className="h-5 w-5 accent-brand"
            />
            <span className={item.is_purchased ? 'flex-1 text-slate-400 line-through' : 'flex-1'}>
              {item.name} <span className="text-slate-400">×{item.quantity}</span>
              {item.shop && <span className="ml-2 text-xs text-slate-400">@ {item.shop.name}</span>}
            </span>
            {item.created_by === userId && (
              <button
                onClick={() => void remove(item.id)}
                className="text-slate-300 hover:text-red-500"
                aria-label="Löschen"
              >
                🗑️
              </button>
            )}
          </li>
        ))}
      </ul>
    </div>
  )
}
