import { useState, useEffect } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useApps } from '../store/apps'
import { useAuth } from '../store/auth'
import {
  APP_ICONS,
  Crown,
  Home,
  LogOut,
  type LucideIcon,
  Menu,
  User,
  Users,
  X,
} from '../lib/icons'
import Logo from './Logo'
import ThemeToggle from './ThemeToggle'

type NavItem = { to: string; label: string; icon: LucideIcon; end?: boolean }

// Feature-Apps erscheinen nur in der Navigation, wenn der Nutzer sie auf dem
// Dashboard aktiviert hat (Slug -> Navigationseintrag).
const FEATURE_NAV: Record<string, NavItem> = {
  'shopping-list': { to: '/shopping', label: 'Einkaufsliste', icon: APP_ICONS['shopping-list'] },
  todo: { to: '/todos', label: 'ToDo', icon: APP_ICONS.todo },
  calendar: { to: '/calendar', label: 'Kalender', icon: APP_ICONS.calendar },
  gallery: { to: '/gallery', label: 'Galerie', icon: APP_ICONS.gallery },
  contacts: { to: '/contacts', label: 'Adressbuch', icon: APP_ICONS.contacts },
  games: { to: '/games', label: 'Fun Area', icon: APP_ICONS.games },
  fuel: { to: '/fuel', label: 'Spritpreise', icon: APP_ICONS.fuel },
}

export default function Layout() {
  const user = useAuth((s) => s.user)
  const logout = useAuth((s) => s.logout)
  const mine = useApps((s) => s.mine)
  const loadApps = useApps((s) => s.load)
  const resetApps = useApps((s) => s.reset)
  const navigate = useNavigate()

  // Mobile: Sidebar als Schublade (Drawer); ab md fest sichtbar.
  const [drawerOpen, setDrawerOpen] = useState(false)
  const hasFamily = Boolean(user?.family_id)

  useEffect(() => {
    if (hasFamily) void loadApps().catch(() => {})
  }, [hasFamily, loadApps])

  // Reihenfolge fix vorgeben; nur aktivierte Apps einblenden.
  const featureItems = Object.entries(FEATURE_NAV)
    .filter(([slug]) => mine.some((app) => app.slug === slug))
    .map(([, item]) => item)

  const nav: NavItem[] = [
    { to: '/dashboard', label: 'Dashboard', icon: Home, end: true },
    ...featureItems,
    { to: '/members', label: 'Familie', icon: Users },
    { to: '/profile', label: 'Profil', icon: User },
    { to: '/premium', label: 'Premium', icon: Crown },
  ]

  async function handleLogout() {
    await logout()
    resetApps()
    navigate('/login')
  }

  return (
    <div className="flex min-h-screen bg-bg text-text">
      {/* Verdunkelung hinter der Schublade (nur mobil, wenn offen) */}
      {drawerOpen && (
        <div
          className="fixed inset-0 z-30 bg-black/40 md:hidden"
          onClick={() => setDrawerOpen(false)}
          aria-hidden
        />
      )}

      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-64 flex-col overflow-y-auto bg-sidebar text-sidebar-text pt-[env(safe-area-inset-top)] transition-transform duration-200 md:static md:z-auto md:w-60 md:translate-x-0 ${
          drawerOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex items-center justify-between px-6 py-6">
          <Logo size={30} className="text-lg font-bold tracking-wide" />
          <button
            onClick={() => setDrawerOpen(false)}
            className="flex h-8 w-8 items-center justify-center rounded-full hover:bg-white/10 md:hidden"
            aria-label="Menü schließen"
          >
            <X className="h-5 w-5" />
          </button>
        </div>
        <div className="flex items-center gap-3 px-6 pb-4">
          <div className="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-white/15 text-sm font-bold">
            {user?.avatar_url ? (
              <img src={user.avatar_url} alt="" className="h-full w-full object-cover" />
            ) : (
              `${user?.first_name?.[0] ?? ''}${user?.last_name?.[0] ?? ''}`.toUpperCase()
            )}
          </div>
          <div className="text-sm">
            <div className="text-sidebar-text">Hey, {user?.first_name}!</div>
            <div className="text-sidebar-muted">
              {user?.family ? `Familie ${user.family.name}` : 'Noch keine Familie'}
            </div>
            {user?.family?.is_premium && (
              <span className="mt-1 inline-flex items-center gap-1 rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-semibold tracking-wide">
                <Crown className="h-3 w-3" /> PREMIUM
              </span>
            )}
          </div>
        </div>
        <nav className="flex-1 px-3">
          {nav.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.end}
              onClick={() => setDrawerOpen(false)}
              className={({ isActive }) =>
                `mb-1 flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition ${
                  isActive ? 'bg-white/15 font-semibold' : 'hover:bg-white/10'
                }`
              }
            >
              <item.icon className="h-5 w-5 shrink-0" aria-hidden />
              {item.label}
            </NavLink>
          ))}
        </nav>
        <button
          onClick={handleLogout}
          className="m-3 flex items-center gap-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-white/10"
        >
          <LogOut className="h-5 w-5 shrink-0" /> Logout
        </button>
      </aside>

      <main className="flex min-w-0 flex-1 flex-col overflow-y-auto">
        <header className="flex items-center gap-2 px-4 pt-[calc(env(safe-area-inset-top)_+_0.75rem)] md:px-10 md:pt-4">
          {/* Hamburger nur mobil */}
          <button
            onClick={() => setDrawerOpen(true)}
            className="flex h-9 w-9 items-center justify-center rounded-full text-muted transition hover:bg-surface-2 md:hidden"
            aria-label="Menü öffnen"
          >
            <Menu className="h-5 w-5" />
          </button>
          <Logo size={24} className="font-bold text-primary md:hidden" />
          <ThemeToggle className="ml-auto text-muted hover:bg-surface-2" />
        </header>
        <div className="min-w-0 flex-1 px-4 pb-[calc(env(safe-area-inset-bottom)_+_1.5rem)] md:px-10 md:pb-10">
          <Outlet />
        </div>
      </main>
    </div>
  )
}
