/**
 * Zentrale Icon-Registry (Lucide, ADR-0018). Ersetzt die plattformabhängigen
 * Standard-Emojis durch einheitliche, themefähige Linien-Icons (erben die
 * aktuelle Textfarbe via `currentColor`, ziehen also Light/Dark automatisch mit).
 *
 * Konvention: Komponenten importieren die benannten Icons hier ODER nutzen
 * `APP_ICONS[slug]` für Feature-Apps, damit Slug→Icon an EINER Stelle gepflegt
 * wird. Standardgröße: `h-5 w-5` (Nav/Buttons), `h-4 w-4` (inline).
 */
import {
  Baby,
  Calendar,
  CalendarDays,
  Car,
  Check,
  CheckSquare,
  ChevronLeft,
  ChevronRight,
  Crown,
  Download,
  Home,
  Image as ImageIcon,
  KeyRound,
  LogOut,
  type LucideIcon,
  Menu,
  MoreVertical,
  PartyPopper,
  Plus,
  RotateCcw,
  Shield,
  ShoppingCart,
  Sparkles,
  Trash2,
  Upload,
  User,
  Users,
  X,
} from 'lucide-react'

export type { LucideIcon }

export {
  Baby,
  Calendar,
  CalendarDays,
  Car,
  Check,
  CheckSquare,
  ChevronLeft,
  ChevronRight,
  Crown,
  Download,
  Home,
  ImageIcon,
  KeyRound,
  LogOut,
  Menu,
  MoreVertical,
  PartyPopper,
  Plus,
  RotateCcw,
  Shield,
  ShoppingCart,
  Sparkles,
  Trash2,
  Upload,
  User,
  Users,
  X,
}

/** Feature-App-Slug → Icon. Einzige Quelle der Wahrheit für App-Icons. */
export const APP_ICONS: Record<string, LucideIcon> = {
  'shopping-list': ShoppingCart,
  todo: CheckSquare,
  calendar: Calendar,
  gallery: ImageIcon,
}
