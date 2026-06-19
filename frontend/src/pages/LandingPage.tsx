import { Link } from 'react-router-dom'
import Logo from '../components/Logo'
import ThemeToggle from '../components/ThemeToggle'
import { APP_ICONS, type LucideIcon, Users } from '../lib/icons'

const FEATURES: { icon: LucideIcon; title: string; text: string }[] = [
  { icon: APP_ICONS['shopping-list'], title: 'Einkaufsliste', text: 'Gemeinsame Liste – jeder ergänzt, was fehlt.' },
  { icon: APP_ICONS.calendar, title: 'Kalender', text: 'Termine der ganzen Familie, inkl. Auto-Reservierung.' },
  { icon: APP_ICONS.todo, title: 'ToDos', text: 'Aufgaben verteilen und abhaken.' },
  { icon: APP_ICONS.gallery, title: 'Galerie', text: 'Eure schönsten Momente an einem Ort.' },
]

export default function LandingPage() {
  return (
    <div className="min-h-screen bg-bg text-text">
      {/* Navigation */}
      <header className="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
        <Logo size={30} className="text-lg font-bold text-primary" />
        <nav className="flex items-center gap-2">
          <ThemeToggle className="text-text hover:bg-surface-2" />
          <Link to="/login" className="rounded-lg px-4 py-2 text-sm font-medium text-primary hover:bg-primary-soft">
            Anmelden
          </Link>
          <Link
            to="/register"
            className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-contrast transition hover:bg-primary-hover"
          >
            Loslegen
          </Link>
        </nav>
      </header>

      {/* Hero */}
      <section className="mx-auto grid max-w-6xl items-center gap-10 px-6 py-12 lg:grid-cols-2 lg:py-20">
        <div>
          <h1 className="text-4xl font-bold leading-tight text-primary sm:text-5xl">
            Euer Familienleben, <span className="text-accent">gut behütet.</span>
          </h1>
          <p className="mt-5 max-w-md text-lg text-muted">
            Einkaufsliste, Kalender, ToDos und Galerie – an einem Ort, für die ganze
            Familie. Gründe eine Familie oder tritt per Einladung bei.
          </p>
          <div className="mt-8 flex flex-wrap gap-4">
            <Link
              to="/register"
              className="rounded-xl bg-primary px-6 py-3 font-semibold text-primary-contrast shadow-lg transition hover:bg-primary-hover"
            >
              Kostenlos starten
            </Link>
            <Link
              to="/login"
              className="rounded-xl border border-primary px-6 py-3 font-semibold text-primary transition hover:bg-primary-soft"
            >
              Anmelden
            </Link>
          </div>
        </div>

        <div className="relative">
          <img
            src="/img/family.jpg"
            alt="Familie"
            className="aspect-[4/3] w-full rounded-3xl object-cover shadow-2xl"
          />
          <div className="absolute -bottom-4 -left-4 hidden rounded-2xl bg-surface px-5 py-3 shadow-pop sm:block">
            <p className="flex items-center gap-2 text-sm font-semibold text-primary">
              <Users className="h-4 w-4" /> Eine App für alle
            </p>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="mx-auto max-w-6xl px-6 py-12">
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {FEATURES.map((f) => (
            <div key={f.title} className="rounded-2xl bg-surface p-6 shadow-card transition hover:shadow-pop">
              <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-soft text-primary">
                <f.icon className="h-6 w-6" />
              </div>
              <h3 className="mt-3 font-semibold text-text">{f.title}</h3>
              <p className="mt-1 text-sm text-muted">{f.text}</p>
            </div>
          ))}
        </div>
      </section>

      <footer className="border-t border-border py-8 text-center text-sm text-muted">
        © {new Date().getFullYear()} Nidula
      </footer>
    </div>
  )
}
