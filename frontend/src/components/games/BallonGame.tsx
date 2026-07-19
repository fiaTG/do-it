import { useCallback, useEffect, useRef, useState } from 'react'
import { PartyPopper } from '../../lib/icons'

// „Ballon-Knallerei" (ADR-0022-Teaser, erstes Premium-Spiel): Ballons steigen
// vom Familienfest auf – antippen zum Zerplatzen, kleine Ballons bringen mehr,
// die goldene Laterne +5, nur die freche Wespe kostet Punkte. 60-Sekunden-
// Runden, eigenes Nidula-Thema, keine fremden Marken.

const W = 480
const H = 560
const ROUND_MS = 60_000

type Kind = 'normal' | 'gold' | 'wasp'

interface Balloon {
  x: number
  y: number
  r: number
  vy: number
  sway: number
  phase: number
  kind: Kind
  color: string
}

interface Floaty {
  x: number
  y: number
  text: string
  ttl: number
  good: boolean
}

const BALLOON_COLORS = ['#E58A72', '#8FCBB8', '#F4C95D', '#9B6FB0', '#3E7C9B', '#D08770']

function themeColor(name: string, fallback: string): string {
  if (typeof window === 'undefined') return fallback
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
  return value || fallback
}

function makeBalloon(elapsedMs: number): Balloon {
  const roll = Math.random()
  // Selten & besonders: goldene Laterne (+5) und freche Wespe (-3).
  const kind: Kind = roll < 0.07 ? 'gold' : roll < 0.17 ? 'wasp' : 'normal'
  const r = kind === 'gold' ? 15 : kind === 'wasp' ? 13 : 14 + Math.random() * 20
  // Mit der Zeit etwas schneller – Spannung ohne Frust.
  const speed = (0.9 + Math.random() * 0.9) * (1 + elapsedMs / ROUND_MS)

  return {
    x: 30 + Math.random() * (W - 60),
    y: H + r,
    r,
    vy: kind === 'wasp' ? speed * 1.4 : speed,
    sway: 12 + Math.random() * 22,
    phase: Math.random() * Math.PI * 2,
    kind,
    color: BALLOON_COLORS[Math.floor(Math.random() * BALLOON_COLORS.length)],
  }
}

function pointsFor(b: Balloon): number {
  if (b.kind === 'gold') return 5
  if (b.kind === 'wasp') return -3
  return b.r < 20 ? 3 : b.r < 27 ? 2 : 1 // klein ist schwerer zu treffen
}

interface Props {
  /** Wird einmal pro Game Over gerufen; liefert die Rekord-Flags fürs Feiern. */
  onGameOver: (score: number) => Promise<{ personal_record: boolean; family_record: boolean }>
}

export default function BallonGame({ onGameOver }: Props) {
  const canvasRef = useRef<HTMLCanvasElement>(null)
  const balloonsRef = useRef<Balloon[]>([])
  const floatiesRef = useRef<Floaty[]>([])
  const scoreRef = useRef(0)
  const startAtRef = useRef(0)
  const lastSpawnRef = useRef(0)
  const rafRef = useRef(0)
  const runningRef = useRef(false)

  const [score, setScore] = useState(0)
  const [timeLeft, setTimeLeft] = useState(ROUND_MS / 1000)
  const [over, setOver] = useState(false)
  const [started, setStarted] = useState(false)
  const [records, setRecords] = useState<{ personal: boolean; family: boolean } | null>(null)

  const draw = useCallback((now: number) => {
    const ctx = canvasRef.current?.getContext('2d')
    if (!ctx) return

    ctx.fillStyle = themeColor('--surface-2', '#efe9dd')
    ctx.fillRect(0, 0, W, H)

    for (const b of balloonsRef.current) {
      const x = b.x + Math.sin(b.phase + now / 700) * b.sway * 0.15
      if (b.kind === 'wasp') {
        // Wespe: gestreifter Körper + Flügel.
        ctx.fillStyle = '#F4C95D'
        ctx.beginPath()
        ctx.ellipse(x, b.y, b.r * 1.2, b.r * 0.8, 0, 0, Math.PI * 2)
        ctx.fill()
        ctx.strokeStyle = '#3a3a32'
        ctx.lineWidth = 3
        for (const off of [-b.r * 0.4, 0.5, b.r * 0.5]) {
          ctx.beginPath()
          ctx.moveTo(x + off, b.y - b.r * 0.7)
          ctx.lineTo(x + off, b.y + b.r * 0.7)
          ctx.stroke()
        }
        ctx.fillStyle = 'rgba(255,255,255,0.7)'
        ctx.beginPath()
        ctx.ellipse(x - b.r * 0.7, b.y - b.r * 0.9, b.r * 0.7, b.r * 0.4, -0.5, 0, Math.PI * 2)
        ctx.ellipse(x + b.r * 0.7, b.y - b.r * 0.9, b.r * 0.7, b.r * 0.4, 0.5, 0, Math.PI * 2)
        ctx.fill()
      } else {
        // Schnur + Ballon-/Laternenkörper mit Glanzpunkt.
        ctx.strokeStyle = 'rgba(120,110,90,0.6)'
        ctx.lineWidth = 1
        ctx.beginPath()
        ctx.moveTo(x, b.y + b.r)
        ctx.quadraticCurveTo(x + 4, b.y + b.r + 12, x, b.y + b.r + 24)
        ctx.stroke()
        ctx.fillStyle = b.kind === 'gold' ? '#E8B93B' : b.color
        ctx.beginPath()
        ctx.ellipse(x, b.y, b.r * 0.85, b.r, 0, 0, Math.PI * 2)
        ctx.fill()
        if (b.kind === 'gold') {
          ctx.strokeStyle = '#B8860B'
          ctx.lineWidth = 2
          ctx.stroke()
        }
        ctx.fillStyle = 'rgba(255,255,255,0.45)'
        ctx.beginPath()
        ctx.ellipse(x - b.r * 0.3, b.y - b.r * 0.35, b.r * 0.22, b.r * 0.3, -0.4, 0, Math.PI * 2)
        ctx.fill()
      }
    }

    // Punkte-Schnipsel (+3 / -3) kurz aufsteigen lassen.
    for (const f of floatiesRef.current) {
      ctx.globalAlpha = Math.max(0, f.ttl / 700)
      ctx.fillStyle = f.good ? themeColor('--primary', '#3f5547') : '#c0392b'
      ctx.font = 'bold 20px sans-serif'
      ctx.textAlign = 'center'
      ctx.fillText(f.text, f.x, f.y)
      ctx.globalAlpha = 1
    }
  }, [])

  const endRound = useCallback(() => {
    runningRef.current = false
    cancelAnimationFrame(rafRef.current)
    setOver(true)
    void onGameOver(scoreRef.current).then((r) =>
      setRecords({ personal: r.personal_record, family: r.family_record }),
    )
  }, [onGameOver])

  // Die Loop plant sich über eine Ref neu ein (react-compiler: keine
  // Selbstreferenz im useCallback) und ersetzt Arrays statt zu mutieren.
  const frameRef = useRef<(now: number) => void>(() => {})

  const frame = useCallback(
    (now: number) => {
      if (!runningRef.current) return
      const elapsed = now - startAtRef.current

      if (elapsed >= ROUND_MS) {
        draw(now)
        endRound()
        return
      }

      // Nachschub: anfangs gemütlich, hinten raus flotter.
      const spawnEvery = 900 - 450 * (elapsed / ROUND_MS)
      const spawned =
        now - lastSpawnRef.current > spawnEvery ? [makeBalloon(elapsed)] : []
      if (spawned.length > 0) lastSpawnRef.current = now

      balloonsRef.current = [...balloonsRef.current, ...spawned]
        .map((b) => ({ ...b, y: b.y - b.vy }))
        .filter((b) => b.y > -b.r - 26)
      floatiesRef.current = floatiesRef.current
        .map((f) => ({ ...f, y: f.y - 0.8, ttl: f.ttl - 16 }))
        .filter((f) => f.ttl > 0)

      setTimeLeft(Math.ceil((ROUND_MS - elapsed) / 1000))
      draw(now)
      rafRef.current = requestAnimationFrame((t) => frameRef.current(t))
    },
    [draw, endRound],
  )

  useEffect(() => {
    frameRef.current = frame
  }, [frame])

  function start() {
    balloonsRef.current = []
    floatiesRef.current = []
    scoreRef.current = 0
    setScore(0)
    setTimeLeft(ROUND_MS / 1000)
    setRecords(null)
    setOver(false)
    setStarted(true)
    runningRef.current = true
    startAtRef.current = performance.now()
    lastSpawnRef.current = 0
    rafRef.current = requestAnimationFrame((t) => frameRef.current(t))
  }

  // Beim Verlassen der Seite die Loop stoppen.
  useEffect(
    () => () => {
      runningRef.current = false
      cancelAnimationFrame(rafRef.current)
    },
    [],
  )

  function onPointerDown(e: React.PointerEvent<HTMLCanvasElement>) {
    if (!runningRef.current) return
    const rect = e.currentTarget.getBoundingClientRect()
    const x = ((e.clientX - rect.left) / rect.width) * W
    const y = ((e.clientY - rect.top) / rect.height) * H

    // Oberste zuerst treffen; +8px Toleranz für Finger.
    for (let i = balloonsRef.current.length - 1; i >= 0; i--) {
      const b = balloonsRef.current[i]
      if (Math.hypot(b.x - x, b.y - y) <= b.r + 8) {
        const pts = pointsFor(b)
        scoreRef.current = Math.max(0, scoreRef.current + pts)
        setScore(scoreRef.current)
        floatiesRef.current.push({
          x: b.x,
          y: b.y,
          text: pts > 0 ? `+${pts}` : `${pts}`,
          ttl: 700,
          good: pts > 0,
        })
        balloonsRef.current.splice(i, 1)
        return
      }
    }
  }

  return (
    <div className="flex flex-col items-center gap-3">
      <p className="flex gap-4 text-sm font-semibold text-text">
        <span>
          Punkte: <span className="text-primary">{score}</span>
        </span>
        <span className="text-muted">⏱ {timeLeft}s</span>
      </p>

      <div className="relative w-full max-w-[540px]">
        <canvas
          ref={canvasRef}
          width={W}
          height={H}
          onPointerDown={onPointerDown}
          className="w-full rounded-2xl shadow"
          style={{ touchAction: 'none' }}
          aria-label="Ballon-Knallerei – Spielfeld"
        />

        {(!started || over) && (
          <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 rounded-2xl bg-black/50 p-4 text-center text-white">
            {over ? (
              records?.family ? (
                <>
                  <PartyPopper className="h-8 w-8" />
                  <p className="font-bold">Neuer Familienrekord: {score} Punkte! 🎉</p>
                </>
              ) : records?.personal ? (
                <p className="font-bold">Persönlicher Rekord: {score} Punkte!</p>
              ) : (
                <p className="font-bold">{score} Punkte – alle Ballons verpufft.</p>
              )
            ) : (
              <p className="text-sm">
                Tippe die Ballons, bevor sie entwischen! Kleine bringen mehr, die goldene
                Laterne +5 – und lass bloß die Wespe in Ruhe (−3). 60 Sekunden. Los!
              </p>
            )}
            <button
              onClick={start}
              className="rounded-lg bg-primary px-5 py-2 font-semibold text-white hover:bg-primary-hover"
            >
              {over ? 'Nochmal!' : 'Los geht’s'}
            </button>
          </div>
        )}
      </div>
    </div>
  )
}
