import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '../store/auth'

export default function ProtectedRoute() {
  const user = useAuth((s) => s.user)
  return user ? <Outlet /> : <Navigate to="/login" replace />
}
