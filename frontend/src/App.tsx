import { useEffect, useState } from 'react'

const API_URL = import.meta.env.VITE_API_URL as string

type Health = {
  status: string
  service: string
  time: string
}

type State =
  | { kind: 'loading' }
  | { kind: 'ok'; data: Health }
  | { kind: 'error'; message: string }

function App() {
  const [state, setState] = useState<State>({ kind: 'loading' })

  useEffect(() => {
    fetch(`${API_URL}/health`)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        return res.json() as Promise<Health>
      })
      .then((data) => setState({ kind: 'ok', data }))
      .catch((err: unknown) =>
        setState({
          kind: 'error',
          message: err instanceof Error ? err.message : 'Unbekannter Fehler',
        }),
      )
  }, [])

  return (
    <main style={{ fontFamily: 'system-ui, sans-serif', maxWidth: 640, margin: '4rem auto', padding: '0 1rem' }}>
      <h1>Family Board</h1>
      <p>React-SPA · Phase 0 – Konnektivitäts-Check zur Laravel-API</p>

      <section
        style={{
          marginTop: '1.5rem',
          padding: '1rem 1.25rem',
          borderRadius: 12,
          border: '1px solid #ddd',
          background: '#fafafa',
        }}
      >
        {state.kind === 'loading' && <p>⏳ Verbinde mit der API …</p>}

        {state.kind === 'ok' && (
          <>
            <p style={{ color: 'green', fontWeight: 600 }}>✅ API erreichbar</p>
            <ul>
              <li>Status: {state.data.status}</li>
              <li>Service: {state.data.service}</li>
              <li>Zeit: {state.data.time}</li>
            </ul>
          </>
        )}

        {state.kind === 'error' && (
          <>
            <p style={{ color: 'crimson', fontWeight: 600 }}>❌ API nicht erreichbar</p>
            <p>{state.message}</p>
            <p style={{ fontSize: '0.85rem', color: '#666' }}>
              Läuft Sail? <code>cd backend &amp;&amp; ./vendor/bin/sail up</code> – API erwartet unter{' '}
              <code>{API_URL}</code>
            </p>
          </>
        )}
      </section>
    </main>
  )
}

export default App
