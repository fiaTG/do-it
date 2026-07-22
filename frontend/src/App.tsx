import { lazy, Suspense, useEffect } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout'
import ProtectedRoute from './components/ProtectedRoute'
import RequireApp from './components/RequireApp'
import ContactsPage from './pages/ContactsPage'
import DashboardPage from './pages/DashboardPage'
import FuelPage from './pages/FuelPage'
import GalleryPage from './pages/GalleryPage'
import GamesPage from './pages/GamesPage'
import HelpPage from './pages/HelpPage'
import DatenschutzPage from './pages/legal/DatenschutzPage'
import ImpressumPage from './pages/legal/ImpressumPage'
import LandingPage from './pages/LandingPage'
import LoginPage from './pages/LoginPage'
import MembersPage from './pages/MembersPage'
import PremiumPage from './pages/PremiumPage'
import ProfilePage from './pages/ProfilePage'
import RegisterPage from './pages/RegisterPage'
import ShoppingListPage from './pages/ShoppingListPage'
import TodosPage from './pages/TodosPage'
import { registerBackButton, syncStatusBar } from './lib/native'
import { useAuth } from './store/auth'
import { useTheme } from './store/theme'

// FullCalendar ist groß -> nur beim Öffnen des Kalenders laden (Code-Splitting).
const CalendarPage = lazy(() => import('./pages/CalendarPage'))

export default function App() {
  const init = useAuth((s) => s.init)
  const loading = useAuth((s) => s.loading)
  const user = useAuth((s) => s.user)
  const theme = useTheme((s) => s.theme)

  useEffect(() => {
    void init()
  }, [init])

  // Native UI (ADR-0018): Statusleiste ans Theme angleichen, Android-Zurück.
  useEffect(() => {
    void syncStatusBar(theme)
  }, [theme])

  useEffect(() => registerBackButton(), [])

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
        {/* Rechtstexte: öffentlich erreichbar (Entwurf bis Platzhalter gefüllt). */}
        <Route path="/impressum" element={<ImpressumPage />} />
        <Route path="/datenschutz" element={<DatenschutzPage />} />

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
            <Route
              path="/fuel"
              element={
                <RequireApp slug="fuel">
                  <FuelPage />
                </RequireApp>
              }
            />
            <Route
              path="/games"
              element={
                <RequireApp slug="games">
                  <GamesPage />
                </RequireApp>
              }
            />
            <Route
              path="/contacts"
              element={
                <RequireApp slug="contacts">
                  <ContactsPage />
                </RequireApp>
              }
            />
            <Route path="/members" element={<MembersPage />} />
            <Route path="/profile" element={<ProfilePage />} />
            <Route path="/premium" element={<PremiumPage />} />
            <Route path="/help" element={<HelpPage />} />
            {/* Einstellungen sind ins Profil gewandert – alte Links umleiten. */}
            <Route path="/settings" element={<Navigate to="/profile" replace />} />
          </Route>
        </Route>

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
