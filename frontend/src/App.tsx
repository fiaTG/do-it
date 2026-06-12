import { useEffect } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout'
import ProtectedRoute from './components/ProtectedRoute'
import CalendarPage from './pages/CalendarPage'
import DashboardPage from './pages/DashboardPage'
import GalleryPage from './pages/GalleryPage'
import LandingPage from './pages/LandingPage'
import LoginPage from './pages/LoginPage'
import MembersPage from './pages/MembersPage'
import RegisterPage from './pages/RegisterPage'
import SettingsPage from './pages/SettingsPage'
import ShoppingListPage from './pages/ShoppingListPage'
import TodosPage from './pages/TodosPage'
import { useAuth } from './store/auth'

export default function App() {
  const init = useAuth((s) => s.init)
  const loading = useAuth((s) => s.loading)
  const user = useAuth((s) => s.user)

  useEffect(() => {
    void init()
  }, [init])

  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-cream text-brand">
        Lädt …
      </div>
    )
  }

  return (
    <BrowserRouter>
      <Routes>
        {/* Öffentliche Landing Scene (eingeloggt -> Dashboard) */}
        <Route path="/" element={user ? <Navigate to="/dashboard" replace /> : <LandingPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />

        <Route element={<ProtectedRoute />}>
          <Route element={<Layout />}>
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/shopping" element={<ShoppingListPage />} />
            <Route path="/todos" element={<TodosPage />} />
            <Route path="/calendar" element={<CalendarPage />} />
            <Route path="/gallery" element={<GalleryPage />} />
            <Route path="/members" element={<MembersPage />} />
            <Route path="/settings" element={<SettingsPage />} />
          </Route>
        </Route>

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
