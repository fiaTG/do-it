import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { MapPin } from '../lib/icons'
import { fetchWeather, weatherInfo, type WeatherData } from '../lib/weather'
import { useAuth } from '../store/auth'

/**
 * Kompaktes Zuhause-Wetter für den Dashboard-Header (weiße Schrift auf dem
 * Farbverlauf). Standort = family.latitude/longitude; ohne Standort sehen
 * Verwalter einen Hinweis, ihn auf der Familienseite festzulegen.
 */
export default function WeatherWidget() {
  const user = useAuth((s) => s.user)
  const family = user?.family
  const isGuardian = user?.role === 'guardian'
  const [weather, setWeather] = useState<WeatherData | null>(null)
  const [failed, setFailed] = useState(false)

  const latitude = family?.latitude ?? null
  const longitude = family?.longitude ?? null

  useEffect(() => {
    if (latitude === null || longitude === null) return
    let cancelled = false
    fetchWeather(latitude, longitude)
      .then((data) => {
        if (!cancelled) setWeather(data)
      })
      .catch(() => {
        if (!cancelled) setFailed(true)
      })
    return () => {
      cancelled = true
    }
  }, [latitude, longitude])

  if (latitude === null || longitude === null) {
    if (!isGuardian) return null
    return (
      <Link
        to="/members"
        className="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs text-white/90 hover:bg-white/25"
      >
        <MapPin className="h-3.5 w-3.5" /> Familienort festlegen für euer Wetter
      </Link>
    )
  }

  if (failed || !weather) return null

  const current = weatherInfo(weather.currentCode)
  const CurrentIcon = current.Icon

  return (
    <div className="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2">
      <div className="flex items-center gap-2">
        <CurrentIcon className="h-8 w-8 text-white/90" />
        <span className="text-2xl font-bold">{weather.currentTemp}°</span>
        <span className="text-sm text-white/80">
          {current.label} · {family?.location_name}
        </span>
      </div>
      <div className="flex items-center gap-4">
        {weather.daily.slice(1).map((day) => {
          const info = weatherInfo(day.code)
          const DayIcon = info.Icon
          return (
            <div key={day.date} className="flex items-center gap-1.5 text-xs text-white/80">
              <span className="capitalize">
                {new Date(day.date).toLocaleDateString('de-DE', { weekday: 'short' })}
              </span>
              <DayIcon className="h-4 w-4" aria-label={info.label} />
              <span>
                {day.max}°<span className="text-white/50">/{day.min}°</span>
              </span>
            </div>
          )
        })}
      </div>
    </div>
  )
}
