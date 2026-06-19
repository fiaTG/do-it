import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'
import Logo from './Logo'

/**
 * Split-Screen-Rahmen für Login/Registrierung: links ein Marken-Panel mit
 * Familienbild (per object-cover korrekt eingepasst), rechts das Formular.
 */
export default function AuthLayout({
  title,
  subtitle,
  children,
}: {
  title: string
  subtitle?: string
  children: ReactNode
}) {
  return (
    <div className="flex min-h-screen">
      <div className="relative hidden w-1/2 lg:block">
        <img src="/img/family.jpg" alt="" className="h-full w-full object-cover" />
        <div className="absolute inset-0 bg-gradient-to-t from-forest via-forest/55 to-forest/10" />
        <div className="absolute inset-0 flex flex-col justify-end p-12 text-white">
          <h2 className="text-3xl font-bold leading-tight">
            Euer Familienleben,
            <br />
            gut behütet.
          </h2>
          <p className="mt-3 max-w-sm text-white/80">
            Einkaufsliste, Kalender, ToDos und Galerie – gemeinsam organisiert.
          </p>
        </div>
      </div>

      <div className="flex w-full items-center justify-center bg-bg p-6 lg:w-1/2">
        <div className="w-full max-w-sm">
          <Link to="/" className="mb-8 inline-flex text-xl text-primary">
            <Logo size={40} />
          </Link>
          <h1 className="mb-1 text-2xl font-bold text-primary">{title}</h1>
          {subtitle && <p className="mb-6 text-sm text-muted">{subtitle}</p>}
          {!subtitle && <div className="mb-6" />}
          {children}
        </div>
      </div>
    </div>
  )
}
