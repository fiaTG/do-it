import { lazy, Suspense, useEffect } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout'
import ProtectedRoute from './components/ProtectedRoute'
import RequireApp from './components/RequireApp'
import DashboardPage from './pages/DashboardPage'
import GalleryPage from './pages/GalleryPage'
import LandingPage from './pages/LandingPage'
import LoginPage from './pages/LoginPage'
import MembersPage from './pages/MembersPage'
import PremiumPage from './pages/PremiumPage'
import ProfilePage from './pages/ProfilePage'
import RegisterPage from './pages/RegisterPage'
import ShoppingListPage from './pages/ShoppingListPage'
import TodosPage from './pages/TodosPage'
import { useAuth } from './store/auth'

// FullCalendar ist groß -> nur beim Öffnen des Kalenders laden (Code-Splitting).
const CalendarPage = lazy(() => import('./pages/CalendarPage'))

export default function App() {
  const init = useAuth((s) => s.init)
  const loading = useAuth((s) => s.loading)
  const user = useAuth((s) => s.user)

  useEffect(() => {
    void init()
  }, [init])

  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-bg text-primary">
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
            <Route
              path="/shopping"
              element={
                <RequireApp slug="shopping-list">
                  <ShoppingListPage />
                </RequireApp>
              }
            />
            <Route
              path="/todos"
              element={
                <RequireApp slug="todo">
                  <TodosPage />
                </RequireApp>
              }
            />
            <Route
              path="/calendar"
              element={
                <RequireApp slug="calendar">
                  <Suspense fallback={<div className="p-6 text-primary">Kalender lädt …</div>}>
                    <CalendarPage />
                  </Suspense>
                </RequireApp>
              }
            />
            <Route
              path="/gallery"
              element={
                <RequireApp slug="gallery">
                  <GalleryPage />
                </RequireApp>
              }
            />
            <Route path="/members" element={<MembersPage />} />
            <Route path="/profile" element={<ProfilePage />} />
            <Route path="/premium" element={<PremiumPage />} />
            {/* Einstellungen sind ins Profil gewandert – alte Links umleiten. */}
            <Route path="/settings" element={<Navigate to="/profile" replace />} />
          </Route>
        </Route>

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
