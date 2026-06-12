import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../store/auth'

const NAV = [
  { to: '/dashboard', label: 'Dashboard', icon: '🏠', end: true },
  { to: '/shopping', label: 'Einkaufsliste', icon: '🛒' },
  { to: '/todos', label: 'ToDo', icon: '✅' },
  { to: '/calendar', label: 'Kalender', icon: '📅' },
  { to: '/gallery', label: 'Galerie', icon: '🖼️' },
  { to: '/members', label: 'Familie', icon: '👪' },
  { to: '/profile', label: 'Profil', icon: '👤' },
  { to: '/settings', label: 'Einstellungen', icon: '⚙️' },
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
    <div className="flex min-h-screen bg-cream text-slate-800">
      <aside className="flex w-60 flex-col bg-brand text-cream">
        <div className="px-6 py-6 text-lg font-bold tracking-wide">Family Board</div>
        <div className="flex items-center gap-3 px-6 pb-4">
          <div className="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-cream/20 text-sm font-bold">
            {user?.avatar_url ? (
              <img src={user.avatar_url} alt="" className="h-full w-full object-cover" />
            ) : (
              `${user?.first_name?.[0] ?? ''}${user?.last_name?.[0] ?? ''}`.toUpperCase()
            )}
          </div>
          <div className="text-sm">
            <div className="text-cream/90">Hey, {user?.first_name}!</div>
            <div className="text-cream/60">
              {user?.family ? `Familie ${user.family.name}` : 'Noch keine Familie'}
            </div>
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
                  isActive ? 'bg-cream/20 font-semibold' : 'hover:bg-cream/10'
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
          className="m-3 rounded-lg px-3 py-2 text-left text-sm hover:bg-cream/10"
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
