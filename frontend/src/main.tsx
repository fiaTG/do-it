import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
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
