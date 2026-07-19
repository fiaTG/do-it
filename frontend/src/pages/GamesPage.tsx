import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { familyApi, gamesApi } from '../api'
import MemberAvatar from '../components/MemberAvatar'
import BallonGame from '../components/games/BallonGame'
import RaupeGame from '../components/games/RaupeGame'
import { ChevronLeft, Crown, Gamepad2, Trophy } from '../lib/icons'
import { useAuth } from '../store/auth'
import type { GameScores, User } from '../types'

type GameSlug = 'raupe' | 'ballons'

const GAME_META: Record<GameSlug, { name: string; emoji: string }> = {
  raupe: { name: 'Hungrige Raupe', emoji: '🐛' },
  ballons: { name: 'Ballon-Knallerei', emoji: '🎈' },
}

// Kommende Spiele (Premium, ADR-0022-Teaser-Muster: sichtbar, ehrlich "Bald").
const UPCOMING = [
  { name: 'Block-Garten', emoji: '🌱', hint: 'Stapel-Puzzle mit Nidula-Pflanzenformen.' },
]

export default function GamesPage() {
  const me = useAuth((s) => s.user)
  const isPremium = me?.family?.is_premium ?? false
  const [view, setView] = useState<'list' | GameSlug>('list')
  const [scores, setScores] = useState<Partial<Record<GameSlug, GameScores>>>({})
  const [members, setMembers] = useState<User[]>([])

  const loadScores = useCallback(async () => {
    try {
      const [raupe, ballons, mem] = await Promise.all([
        gamesApi.scores('raupe'),
        gamesApi.scores('ballons'),
        familyApi.members(),
      ])
      setScores({ raupe, ballons })
      setMembers(mem)
    } catch {
      // Bestenliste ist Beiwerk – Spielen geht auch ohne.
    }
  }, [])

  useEffect(() => {
    void loadScores()
  }, [loadScores])

  // Nach jeder Runde: Score speichern, Rekord-Flags zurück ans Spiel, Liste aktualisieren.
  const makeGameOver = useCallback(
    (game: GameSlug) => async (score: number) => {
      const result = await gamesApi.submit(game, score)
      await loadScores()
      return result
    },
    [loadScores],
  )

  const memberById = (id: number): User | undefined => members.find((m) => m.id === id)

  const board = (game: GameSlug, unit: string) => {
    const s = scores[game]
    return (
      <div className="rounded-2xl bg-surface p-5 shadow">
        <h2 className="mb-3 flex items-center gap-2 font-semibold text-text">
          <Trophy className="h-4 w-4 text-primary" /> Bestenliste · {GAME_META[game].emoji}{' '}
          {GAME_META[game].name}
        </h2>
        {!s || s.top.length === 0 ? (
          <p className="text-sm text-muted">Noch keine Runde gespielt – leg vor!</p>
        ) : (
          <ol className="space-y-2">
            {s.top.map((entry, i) => {
              const member = memberById(entry.user_id)
              return (
                <li key={entry.user_id} className="flex items-center gap-2.5 text-sm">
                  <span className="w-5 text-right font-bold text-muted">{i + 1}.</span>
                  {member && <MemberAvatar member={member} size="sm" />}
                  <span className="min-w-0 flex-1 truncate text-text">
                    {member?.first_name ?? 'Ehemaliges Mitglied'}
                    {entry.user_id === me?.id && ' (ich)'}
                  </span>
                  <span className="font-bold text-primary">{entry.score}</span>
                </li>
              )
            })}
          </ol>
        )}
        {s?.my_best !== null && s?.my_best !== undefined && (
          <p className="mt-3 text-xs text-muted">
            Dein Bestwert: {s.my_best} {unit}
          </p>
        )}
      </div>
    )
  }

  if (view !== 'list') {
    return (
      <div className="mx-auto max-w-2xl space-y-4">
        <button
          onClick={() => setView('list')}
          className="flex items-center gap-1 text-sm text-muted hover:text-primary"
        >
          <ChevronLeft className="h-4 w-4" /> Zurück zur Fun Area
        </button>
        <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
          {GAME_META[view].emoji} {GAME_META[view].name}
        </h1>
        {view === 'raupe' ? (
          <RaupeGame onGameOver={makeGameOver('raupe')} />
        ) : (
          <BallonGame onGameOver={makeGameOver('ballons')} />
        )}
        {board(view, view === 'raupe' ? 'Blätter' : 'Punkte')}
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <Gamepad2 className="h-6 w-6" /> Fun Area
      </h1>
      <p className="text-sm text-muted">
        Kleine Spiele, große Familien-Rivalität – wer holt den Highscore? 🏆
      </p>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <button
          onClick={() => setView('raupe')}
          className="group rounded-2xl bg-surface p-5 text-left shadow transition hover:shadow-lg"
        >
          <div className="text-4xl">🐛</div>
          <p className="mt-2 font-bold text-text group-hover:text-primary">Hungrige Raupe</p>
          <p className="mt-1 text-sm text-muted">
            Friss Blätter, wachse – und beiß dir nicht in den Schwanz.
          </p>
          <span className="mt-3 inline-block rounded-lg bg-primary px-4 py-1.5 text-sm font-semibold text-white group-hover:bg-primary-hover">
            Spielen
          </span>
        </button>

        {/* Erstes Premium-Spiel (Timos Linie: Fun Area frei, NEUE Spiele Premium). */}
        {isPremium ? (
          <button
            onClick={() => setView('ballons')}
            className="group rounded-2xl bg-surface p-5 text-left shadow transition hover:shadow-lg"
          >
            <div className="text-4xl">🎈</div>
            <p className="mt-2 flex items-center gap-2 font-bold text-text group-hover:text-primary">
              Ballon-Knallerei
              <Crown className="h-4 w-4 text-primary" aria-label="Premium-Spiel" />
            </p>
            <p className="mt-1 text-sm text-muted">
              Zerplatze Ballons, schnapp die goldene Laterne – Achtung, Wespe!
            </p>
            <span className="mt-3 inline-block rounded-lg bg-primary px-4 py-1.5 text-sm font-semibold text-white group-hover:bg-primary-hover">
              Spielen
            </span>
          </button>
        ) : (
          <div className="rounded-2xl bg-surface p-5 shadow">
            <div className="text-4xl">🎈</div>
            <p className="mt-2 flex items-center gap-2 font-bold text-text">
              Ballon-Knallerei
              <span className="inline-flex items-center gap-1 rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                <Crown className="h-3 w-3" /> Premium
              </span>
            </p>
            <p className="mt-1 text-sm text-muted">
              Zerplatze Ballons, schnapp die goldene Laterne – Achtung, Wespe!
            </p>
            <Link
              to="/premium"
              className="mt-3 inline-block text-sm font-semibold text-primary hover:underline"
            >
              Mehr zu Premium
            </Link>
          </div>
        )}

        {UPCOMING.map((game) => (
          <div key={game.name} className="rounded-2xl bg-surface p-5 shadow">
            <div className="text-4xl">{game.emoji}</div>
            <p className="mt-2 flex items-center gap-2 font-bold text-text">
              {game.name}
              <span className="inline-flex items-center gap-1 rounded-full bg-surface-2 px-2 py-0.5 text-[10px] font-semibold text-muted">
                <Crown className="h-3 w-3" /> Bald · Premium
              </span>
            </p>
            <p className="mt-1 text-sm text-muted">{game.hint}</p>
            <Link
              to="/premium"
              className="mt-3 inline-block text-sm font-semibold text-primary hover:underline"
            >
              Mehr zu Premium
            </Link>
          </div>
        ))}
      </div>

      {board('raupe', 'Blätter')}
      {isPremium && board('ballons', 'Punkte')}
    </div>
  )
}
