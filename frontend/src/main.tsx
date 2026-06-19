import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
// Marken-Schriften gebündelt (funktionieren so auch offline in der nativen
// WKWebView, anders als Google-Fonts-CDN): Inter = Fließtext, Fraunces = Headlines.
import '@fontsource-variable/inter/index.css'
import '@fontsource-variable/fraunces/index.css'
import './index.css'
import App from './App.tsx'
import { applyPlatformClass } from './lib/native'

// data-platform früh setzen (vor dem ersten Paint), analog zum Theme.
applyPlatformClass()

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
