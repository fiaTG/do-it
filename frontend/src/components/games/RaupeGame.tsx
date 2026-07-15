import { useCallback, useEffect, useRef, useState } from 'react'
import { ChevronLeft, ChevronRight, PartyPopper } from '../../lib/icons'

// „Hungrige Raupe" – Snake-Mechanik (frei) im eigenen Nidula-Naturthema:
// eine Raupe frisst Blätter. Eigener Name + eigene Grafik, keine fremden Marken.

const GRID = 20
const CELL = 20
const SIZE = GRID * CELL
const START_DELAY = 170
const MIN_DELAY = 70

type Point = { x: number; y: number }
type Dir = Point

const START_SNAKE: Point[] = [
  { x: 5, y: 10 },
  { x: 4, y: 10 },
  { x: 3, y: 10 },
]

function randomFreeCell(occupied: Point[]): Point {
  while (true) {
    const cell = { x: Math.floor(Math.random() * GRID), y: Math.floor(Math.random() * GRID) }
    if (!occupied.some((p) => p.x === cell.x && p.y === cell.y)) return cell
  }
}

/** Theme-Farben aus den CSS-Variablen (Dark Mode inklusive), mit Fallbacks. */
function themeColor(name: string, fallback: string): string {
  if (typeof window === 'undefined') return fallback
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
  return value || fallback
}

interface Props {
  /** Wird einmal pro Game Over gerufen; liefert die Rekord-Flags fürs Feiern. */
  onGameOver: (score: number) => Promise<{ personal_record: boolean; family_record: boolean }>
}

export default function RaupeGame({ onGameOver }: Props) {
  const canvasRef = useRef<HTMLCanvasElement>(null)
  const snakeRef = useRef<Point[]>([...START_SNAKE])
  const dirRef = useRef<Dir>({ x: 1, y: 0 })
  const nextDirRef = useRef<Dir>({ x: 1, y: 0 })
  const leafRef = useRef<Point>({ x: 12, y: 10 })
  const delayRef = useRef(START_DELAY)
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  // Die Loop ruft sich selbst über eine Ref auf (kein Self-Reference im useCallback).
  const tickRef = useRef<() => void>(() => {})
  const runningRef = useRef(false)
  const touchStartRef = useRef<Point | null>(null)

  const [score, setScore] = useState(0)
  const [over, setOver] = useState(false)
  const [started, setStarted] = useState(false)
  const [records, setRecords] = useState<{ personal: boolean; family: boolean } | null>(null)

  const draw = useCallback(() => {
    const ctx = canvasRef.current?.getContext('2d')
    if (!ctx) return
    const bg = themeColor('--surface-2', '#f1ece3')
    const grid = themeColor('--border', '#e2dacc')
    const leafColor = themeColor('--primary', '#3e6b4f')
    const body = '#E58A72'
    const head = '#D08770'

    ctx.clearRect(0, 0, SIZE, SIZE)
    ctx.fillStyle = bg
    ctx.fillRect(0, 0, SIZE, SIZE)

    // Dezentes Raster
    ctx.strokeStyle = grid
    ctx.lineWidth = 0.5
    for (let i = 1; i < GRID; i++) {
      ctx.beginPath()
      ctx.moveTo(i * CELL, 0)
      ctx.lineTo(i * CELL, SIZE)
      ctx.moveTo(0, i * CELL)
      ctx.lineTo(SIZE, i * CELL)
      ctx.stroke()
    }

    // Blatt (Ellipse mit Stiel)
    const leaf = leafRef.current
    const lx = leaf.x * CELL + CELL / 2
    const ly = leaf.y * CELL + CELL / 2
    ctx.fillStyle = leafColor
    ctx.beginPath()
    ctx.ellipse(lx, ly + 1, 7, 5, -0.6, 0, Math.PI * 2)
    ctx.fill()
    ctx.strokeStyle = leafColor
    ctx.lineWidth = 1.5
    ctx.beginPath()
    ctx.moveTo(lx + 4, ly - 4)
    ctx.lineTo(lx + 7, ly - 8)
    ctx.stroke()

    // Raupe: runde Segmente, Kopf mit Augen
    const snake = snakeRef.current
    snake.forEach((seg, i) => {
      const cx = seg.x * CELL + CELL / 2
      const cy = seg.y * CELL + CELL / 2
      ctx.fillStyle = i === 0 ? head : body
      ctx.beginPath()
      ctx.arc(cx, cy, i === 0 ? 9 : 8, 0, Math.PI * 2)
      ctx.fill()
      if (i === 0) {
        const d = dirRef.current
        ctx.fillStyle = '#ffffff'
        ctx.beginPath()
        ctx.arc(cx + d.x * 3 - d.y * 3, cy + d.y * 3 - d.x * 3, 2.2, 0, Math.PI * 2)
        ctx.arc(cx + d.x * 3 + d.y * 3, cy + d.y * 3 + d.x * 3, 2.2, 0, Math.PI * 2)
        ctx.fill()
        ctx.fillStyle = '#333333'
        ctx.beginPath()
        ctx.arc(cx + d.x * 4 - d.y * 3, cy + d.y * 4 - d.x * 3, 1.1, 0, Math.PI * 2)
        ctx.arc(cx + d.x * 4 + d.y * 3, cy + d.y * 4 + d.x * 3, 1.1, 0, Math.PI * 2)
        ctx.fill()
      }
    })
  }, [])

  const stopLoop = useCallback(() => {
    runningRef.current = false
    if (timerRef.current) clearTimeout(timerRef.current)
  }, [])

  const gameOver = useCallback(async () => {
    stopLoop()
    setOver(true)
    const finalScore = snakeRef.current.length - START_SNAKE.length
    try {
      const result = await onGameOver(finalScore)
      setRecords({ personal: result.personal_record, family: result.family_record })
    } catch {
      setRecords(null) // Offline o. ä. – Spiel bleibt spielbar.
    }
  }, [onGameOver, stopLoop])

  const tick = useCallback(() => {
    const snake = snakeRef.current
    dirRef.current = nextDirRef.current
    const head = snake[0]
    const next = { x: head.x + dirRef.current.x, y: head.y + dirRef.current.y }

    const hitsWall = next.x < 0 || next.y < 0 || next.x >= GRID || next.y >= GRID
    const hitsSelf = snake.some((p) => p.x === next.x && p.y === next.y)
    if (hitsWall || hitsSelf) {
      void gameOver()
      return
    }

    snake.unshift(next)
    if (next.x === leafRef.current.x && next.y === leafRef.current.y) {
      leafRef.current = randomFreeCell(snake)
      setScore((s) => s + 1)
      delayRef.current = Math.max(MIN_DELAY, delayRef.current - 5)
    } else {
      snake.pop()
    }

    draw()
    if (runningRef.current) {
      timerRef.current = setTimeout(() => tickRef.current(), delayRef.current)
    }
  }, [draw, gameOver])

  useEffect(() => {
    tickRef.current = tick
  }, [tick])

  const start = useCallback(() => {
    stopLoop()
    snakeRef.current = [...START_SNAKE]
    dirRef.current = { x: 1, y: 0 }
    nextDirRef.current = { x: 1, y: 0 }
    delayRef.current = START_DELAY
    leafRef.current = randomFreeCell(snakeRef.current)
    setScore(0)
    setOver(false)
    setRecords(null)
    setStarted(true)
    runningRef.current = true
    draw()
    timerRef.current = setTimeout(() => tickRef.current(), delayRef.current)
  }, [draw, stopLoop])

  const turn = useCallback((dir: Dir) => {
    // Kein 180°-Wenden in die eigene Körperrichtung.
    if (dir.x === -dirRef.current.x && dir.y === -dirRef.current.y) return
    nextDirRef.current = dir
  }, [])

  // Tastatur (Pfeile + WASD); Pfeiltasten sollen nicht scrollen.
  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      const map: Record<string, Dir> = {
        ArrowUp: { x: 0, y: -1 },
        ArrowDown: { x: 0, y: 1 },
        ArrowLeft: { x: -1, y: 0 },
        ArrowRight: { x: 1, y: 0 },
        w: { x: 0, y: -1 },
        s: { x: 0, y: 1 },
        a: { x: -1, y: 0 },
        d: { x: 1, y: 0 },
      }
      const dir = map[e.key]
      if (dir) {
        e.preventDefault()
        turn(dir)
      }
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [turn])

  // Aufräumen beim Verlassen der Seite.
  useEffect(() => stopLoop, [stopLoop])

  useEffect(() => {
    draw()
  }, [draw])

  function onTouchStart(e: React.TouchEvent) {
    const t = e.touches[0]
    touchStartRef.current = { x: t.clientX, y: t.clientY }
  }

  function onTouchEnd(e: React.TouchEvent) {
    const start = touchStartRef.current
    if (!start) return
    const t = e.changedTouches[0]
    const dx = t.clientX - start.x
    const dy = t.clientY - start.y
    if (Math.abs(dx) < 20 && Math.abs(dy) < 20) return
    if (Math.abs(dx) > Math.abs(dy)) turn({ x: dx > 0 ? 1 : -1, y: 0 })
    else turn({ x: 0, y: dy > 0 ? 1 : -1 })
    touchStartRef.current = null
  }

  return (
    <div className="flex flex-col items-center gap-3">
      <p className="text-sm font-semibold text-text">
        Blätter gefressen: <span className="text-primary">{score}</span>
      </p>

      <div className="relative w-full max-w-[420px]">
        <canvas
          ref={canvasRef}
          width={SIZE}
          height={SIZE}
          onTouchStart={onTouchStart}
          onTouchEnd={onTouchEnd}
          className="w-full rounded-2xl shadow"
          style={{ touchAction: 'none' }}
          aria-label="Hungrige Raupe – Spielfeld"
        />

        {(!started || over) && (
          <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 rounded-2xl bg-black/50 p-4 text-center text-white">
            {over ? (
              <>
                {records?.family ? (
                  <>
                    <PartyPopper className="h-8 w-8" />
                    <p className="font-bold">Neuer Familienrekord: {score} Blätter! 🎉</p>
                  </>
                ) : records?.personal ? (
                  <p className="font-bold">Persönlicher Rekord: {score} Blätter!</p>
                ) : (
                  <p className="font-bold">{score} Blätter – die Raupe ist satt.</p>
                )}
              </>
            ) : (
              <p className="text-sm">
                Steuere die Raupe mit Pfeiltasten/WASD, Wischen oder den Knöpfen – friss
                Blätter, aber beiß dir nicht in den Schwanz!
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

      {/* Steuerkreuz für Mobile */}
      <div className="grid grid-cols-3 gap-1 sm:hidden" aria-hidden="true">
        <span />
        <button onClick={() => turn({ x: 0, y: -1 })} className="rounded-lg bg-surface-2 p-3">
          <ChevronLeft className="h-5 w-5 rotate-90" />
        </button>
        <span />
        <button onClick={() => turn({ x: -1, y: 0 })} className="rounded-lg bg-surface-2 p-3">
          <ChevronLeft className="h-5 w-5" />
        </button>
        <span />
        <button onClick={() => turn({ x: 1, y: 0 })} className="rounded-lg bg-surface-2 p-3">
          <ChevronRight className="h-5 w-5" />
        </button>
        <span />
        <button onClick={() => turn({ x: 0, y: 1 })} className="rounded-lg bg-surface-2 p-3">
          <ChevronRight className="h-5 w-5 rotate-90" />
        </button>
        <span />
      </div>
    </div>
  )
}
