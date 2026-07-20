import { useEffect, useMemo, useReducer, useState } from 'react'
import { PartyPopper } from '../../lib/icons'
import {
  createGame,
  finalScore,
  isOver,
  place,
  previewPlacement,
  type GameState,
  type Plant,
} from '../../lib/bluetenbeet'

// „Nidulas Blütenbeet" (ADR-0028) – rundenbasiertes Garten-Puzzle. UI ist
// bewusst ein DOM-Grid aus echten Buttons (barrierearm + testbar); die
// Spiellogik liegt komplett in lib/bluetenbeet.ts. Animationen laufen nur
// via motion-safe (respektiert prefers-reduced-motion).

const PLANT_VIEW: Record<Plant, { emoji: string; label: string; hint: string; bg: string }> = {
  flower: { emoji: '🌸', label: 'Blume', hint: 'mag verschiedene Nachbarn', bg: 'bg-[#E58A72]/15' },
  herb: { emoji: '🌿', label: 'Kraut', hint: 'mag gleiche Nachbarn', bg: 'bg-[#8FCBB8]/20' },
  shrub: { emoji: '🫐', label: 'Strauch', hint: 'mag den Beetrand', bg: 'bg-[#9B6FB0]/15' },
  tree: { emoji: '🌳', label: 'Baum', hint: 'braucht Abstand', bg: 'bg-[#5BA88A]/20' },
}

function freshSeed(): number {
  return (Date.now() ^ Math.floor(Math.random() * 0x7fffffff)) | 0
}

interface UiState {
  game: GameState
  selected: number
  finished: boolean
}

type Action =
  | { type: 'select'; index: number }
  | { type: 'place'; cell: number }
  | { type: 'restart'; seed: number }

function reducer(state: UiState, action: Action): UiState {
  switch (action.type) {
    case 'select':
      return { ...state, selected: Math.max(0, Math.min(action.index, state.game.offers.length - 1)) }
    case 'place': {
      if (state.finished || state.game.board[action.cell] !== null) return state
      const game = place(state.game, state.selected, action.cell)
      return { game, selected: 0, finished: isOver(game) }
    }
    case 'restart':
      return { game: createGame(action.seed), selected: 0, finished: false }
  }
}

interface Props {
  onGameOver: (score: number) => Promise<{ personal_record: boolean; family_record: boolean }>
}

export default function BluetenbeetGame({ onGameOver }: Props) {
  const [state, dispatch] = useReducer(reducer, undefined, () => ({
    game: createGame(freshSeed()),
    selected: 0,
    finished: false,
  }))
  const { game, selected, finished } = state
  const [records, setRecords] = useState<{ personal: boolean; family: boolean } | null>(null)

  const score = useMemo(() => finalScore(game), [game])

  // Punkte-Vorschau je freiem Feld für die aktuell gewählte Pflanze.
  const previews = useMemo(() => {
    const map = new Map<number, number>()
    if (finished) return map
    for (let c = 0; c < game.board.length; c++) {
      if (game.board[c] === null) map.set(c, previewPlacement(game, selected, c))
    }
    return map
  }, [game, selected, finished])

  // Genau einmal je Partie werten (Score speichern -> Rekord-Flags).
  useEffect(() => {
    if (!finished) return
    let active = true
    void onGameOver(finalScore(game)).then((r) => {
      if (active) setRecords({ personal: r.personal_record, family: r.family_record })
    })
    return () => {
      active = false
    }
  }, [finished]) // eslint-disable-line react-hooks/exhaustive-deps

  function restart() {
    setRecords(null)
    dispatch({ type: 'restart', seed: freshSeed() })
  }

  return (
    <div className="mx-auto flex max-w-md flex-col items-center gap-4">
      <div className="flex w-full items-center justify-between text-sm font-semibold text-text">
        <span>
          Punkte: <span className="text-primary">{score}</span>
        </span>
        <span className="text-muted">
          {finished ? 'Beet fertig' : `Noch ${game.movesLeft} ${game.movesLeft === 1 ? 'Zug' : 'Züge'}`}
        </span>
      </div>

      {/* Angebot: drei verschiedene Pflanzen, 1–3 wählt per Tastatur */}
      {!finished && (
        <div className="flex w-full gap-2">
          {game.offers.map((p, i) => {
            const active = i === selected
            const v = PLANT_VIEW[p]
            return (
              <button
                key={i}
                type="button"
                onClick={() => dispatch({ type: 'select', index: i })}
                aria-pressed={active}
                aria-label={`${v.label} – ${v.hint} (Taste ${i + 1})`}
                className={`flex flex-1 flex-col items-center rounded-xl border p-2 transition ${
                  active
                    ? 'border-primary bg-primary/5 ring-1 ring-primary'
                    : 'border-border hover:bg-surface-2'
                }`}
              >
                <span className="text-2xl" aria-hidden="true">
                  {v.emoji}
                </span>
                <span className="text-xs font-semibold text-text">{v.label}</span>
                <span className="text-[10px] leading-tight text-muted">{v.hint}</span>
              </button>
            )
          })}
        </div>
      )}

      {/* Beet: 5×5 DOM-Grid aus echten Buttons */}
      <div
        className="relative grid w-full grid-cols-5 gap-1.5"
        onKeyDown={(e) => {
          if (e.key >= '1' && e.key <= String(game.offers.length)) {
            dispatch({ type: 'select', index: Number(e.key) - 1 })
          }
        }}
      >
        {game.board.map((cell, i) => {
          if (cell) {
            const v = PLANT_VIEW[cell]
            return (
              <div
                key={i}
                className={`flex aspect-square items-center justify-center rounded-lg ${v.bg} motion-safe:animate-[pop_0.2s_ease-out]`}
                title={v.label}
              >
                <span className="text-2xl sm:text-3xl" aria-label={v.label}>
                  {v.emoji}
                </span>
              </div>
            )
          }
          const delta = previews.get(i) ?? 0
          return (
            <button
              key={i}
              type="button"
              disabled={finished}
              onClick={() => dispatch({ type: 'place', cell: i })}
              aria-label={`Leeres Feld – ${PLANT_VIEW[game.offers[selected]]?.label} bringt ${delta >= 0 ? '+' : ''}${delta} Punkte`}
              className="flex aspect-square items-center justify-center rounded-lg border border-dashed border-border text-xs font-semibold text-muted hover:border-primary hover:bg-primary/5 disabled:opacity-50"
            >
              {!finished && (
                <span className={delta > 0 ? 'text-primary' : delta < 0 ? 'text-red-500' : 'text-muted'}>
                  {delta >= 0 ? '+' : ''}
                  {delta}
                </span>
              )}
            </button>
          )
        })}

        {finished && (
          <div className="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-xl bg-black/55 p-4 text-center text-white">
            <PartyPopper className="h-8 w-8 motion-safe:animate-[pop_0.3s_ease-out]" />
            <p className="font-bold">
              {records?.family
                ? `Prächtigstes Beet der Familie: ${score}! 🎉`
                : records?.personal
                  ? `Dein bestes Beet: ${score}!`
                  : `Dein Beet ist erblüht – ${score} Punkte.`}
            </p>
            <button
              onClick={restart}
              className="rounded-lg bg-primary px-5 py-2 font-semibold text-white hover:bg-primary-hover"
            >
              Neues Beet
            </button>
          </div>
        )}
      </div>

      <p className="text-center text-xs text-muted">
        Wähle eine Pflanze und setze sie aufs Beet – die Zahlen zeigen, wie viele Punkte der Zug
        gerade bringt. 🌸 mag Vielfalt, 🌿 mag Gesellschaft, 🫐 mag den Rand, 🌳 braucht Abstand.
      </p>
    </div>
  )
}
