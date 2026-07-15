import {
  Cloud,
  CloudDrizzle,
  CloudFog,
  CloudLightning,
  CloudRain,
  CloudSnow,
  CloudSun,
  type LucideIcon,
  Sun,
} from './icons'

/**
 * Wetter über Open-Meteo (kostenlos, kein API-Key, CORS offen) – Free-Feature
 * laut Produkt-Backlog. Der Standort kommt aus family.latitude/longitude.
 */

export interface DailyForecast {
  date: string
  code: number
  min: number
  max: number
}

export interface WeatherData {
  currentTemp: number
  currentCode: number
  daily: DailyForecast[]
}

export async function fetchWeather(latitude: number, longitude: number): Promise<WeatherData> {
  const url =
    'https://api.open-meteo.com/v1/forecast' +
    `?latitude=${latitude}&longitude=${longitude}` +
    '&current=temperature_2m,weather_code' +
    '&daily=temperature_2m_max,temperature_2m_min,weather_code' +
    '&timezone=auto&forecast_days=4'
  const res = await fetch(url)
  if (!res.ok) throw new Error(`Open-Meteo: HTTP ${res.status}`)
  const data = (await res.json()) as {
    current: { temperature_2m: number; weather_code: number }
    daily: {
      time: string[]
      weather_code: number[]
      temperature_2m_max: number[]
      temperature_2m_min: number[]
    }
  }

  return {
    currentTemp: Math.round(data.current.temperature_2m),
    currentCode: data.current.weather_code,
    daily: data.daily.time.map((date, i) => ({
      date,
      code: data.daily.weather_code[i],
      min: Math.round(data.daily.temperature_2m_min[i]),
      max: Math.round(data.daily.temperature_2m_max[i]),
    })),
  }
}

export interface GeocodingResult {
  name: string
  latitude: number
  longitude: number
  admin1?: string
  country?: string
}

/** Ortssuche für den Familien-Standort (Open-Meteo Geocoding, deutschsprachig). */
export async function searchPlaces(query: string): Promise<GeocodingResult[]> {
  const url =
    'https://geocoding-api.open-meteo.com/v1/search' +
    `?name=${encodeURIComponent(query)}&count=5&language=de&format=json`
  const res = await fetch(url)
  if (!res.ok) throw new Error(`Geocoding: HTTP ${res.status}`)
  const data = (await res.json()) as { results?: GeocodingResult[] }
  return data.results ?? []
}

/** WMO-Wettercode → deutsches Label + Icon. */
export function weatherInfo(code: number): { label: string; Icon: LucideIcon } {
  if (code === 0) return { label: 'Klar', Icon: Sun }
  if (code <= 2) return { label: 'Leicht bewölkt', Icon: CloudSun }
  if (code === 3) return { label: 'Bedeckt', Icon: Cloud }
  if (code === 45 || code === 48) return { label: 'Nebel', Icon: CloudFog }
  if (code >= 51 && code <= 57) return { label: 'Nieselregen', Icon: CloudDrizzle }
  if ((code >= 61 && code <= 67) || (code >= 80 && code <= 82)) {
    return { label: 'Regen', Icon: CloudRain }
  }
  if ((code >= 71 && code <= 77) || code === 85 || code === 86) {
    return { label: 'Schnee', Icon: CloudSnow }
  }
  if (code >= 95) return { label: 'Gewitter', Icon: CloudLightning }
  return { label: 'Wechselhaft', Icon: Cloud }
}
