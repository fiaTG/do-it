import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../store/auth'
import ThemeToggle from './ThemeToggle'

const NAV = [
  { to: '/dashboard', label: 'Dashboard', icon: '🏠', end: true },
  { to: '/shopping', label: 'Einkaufsliste', icon: '🛒' },
  { to: '/todos', label: 'ToDo', icon: '✅' },
  { to: '/calendar', label: 'Kalender', icon: '📅' },
  { to: '/gallery', label: 'Galerie', icon: '🖼️' },
  { to: '/members', label: 'Familie', icon: '👪' },
  { to: '/profile', label: 'Profil', icon: '👤' },
  { to: '/premium', label: 'Premium', icon: '⭐' },
]

export default function Layout() {
  const user = useAuth((s) => s.user)
  const logout = useAuth((s) => s.logout)
  const navigate = useNavigate()

  async function handleLogout() {
    await logout()
    navigate('/login')
  }

  return (
    <div className="flex min-h-screen bg-bg text-text">
      <aside className="flex w-60 flex-col bg-sidebar text-sidebar-text">
        <div className="flex items-center justify-between px-6 py-6">
          <span className="text-lg font-bold tracking-wide">⚓ Heimathafen</span>
          <ThemeToggle />
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
              <span className="mt-1 inline-block rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-semibold tracking-wide">
                ⭐ PREMIUM
              </span>
            )}
          </div>
        </div>
        <nav className="flex-1 px-3">
          {NAV.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.end}
              className={({ isActive }) =>
                `mb-1 flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition ${
                  isActive ? 'bg-white/15 font-semibold' : 'hover:bg-white/10'
                }`
              }
            >
              <span aria-hidden>{item.icon}</span>
              {item.label}
            </NavLink>
          ))}
        </nav>
        <button
          onClick={handleLogout}
          className="m-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-white/10"
        >
          🚪 Logout
        </button>
      </aside>

      <main className="flex-1 overflow-y-auto p-6 md:p-10">
        <Outlet />
      </main>
    </div>
  )
}
