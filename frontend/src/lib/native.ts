import { App } from '@capacitor/app'
import { Capacitor } from '@capacitor/core'
import { StatusBar, Style } from '@capacitor/status-bar'
import type { Theme } from '../store/theme'

/** 'ios' | 'android' | 'web' */
export const platform = Capacitor.getPlatform()

/**
 * Setzt `data-platform` am <html> (analog `data-theme`). Erlaubt plattform-
 * bewusste Token-Overrides in CSS (ADR-0018), wird bewusst sparsam genutzt.
 */
export function applyPlatformClass(): void {
  if (typeof document !== 'undefined') {
    document.documentElement.dataset.platform = platform
  }
}

/**
 * Statusleisten-Stil ans Theme angleichen, damit Uhr/Akku immer lesbar sind.
 * Style.Dark = dunkle Symbole (für helle Hintergründe), Style.Light = helle.
 */
export async function syncStatusBar(theme: Theme): Promise<void> {
  if (!Capacitor.isNativePlatform()) return
  try {
    await StatusBar.setStyle({ style: theme === 'dark' ? Style.Light : Style.Dark })
    if (platform === 'android') {
      await StatusBar.setBackgroundColor({ color: theme === 'dark' ? '#1f221d' : '#f2ece1' })
    }
  } catch {
    // Im Web nicht verfügbar – ignorieren.
  }
}

/**
 * Android-Hardware-Zurück: eine Ebene zurück, auf der Wurzel die App schließen
 * (Standard-Android-Verhalten). Gibt eine Cleanup-Funktion zurück.
 */
export function registerBackButton(): () => void {
  if (!Capacitor.isNativePlatform()) return () => {}

  const handle = App.addListener('backButton', ({ canGoBack }) => {
    if (canGoBack) {
      window.history.back()
    } else {
      void App.exitApp()
    }
  })

  return () => {
    void handle.then((h) => h.remove())
  }
}
