import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { familyApi, gamesApi } from '../api'
import MemberAvatar from '../components/MemberAvatar'
import RaupeGame from '../components/games/RaupeGame'
import { ChevronLeft, Crown, Gamepad2, Trophy } from '../lib/icons'
import { useAuth } from '../store/auth'
import type { GameScores, User } from '../types'

// Kommende Spiele (Premium, ADR-0022-Teaser-Muster: sichtbar, ehrlich "Bald").
const UPCOMING = [
  { name: 'Ballon-Knallerei', emoji: '🎈', hint: 'Zerplatze fliegende Ballons, bevor die Zeit abläuft.' },
  { name: 'Block-Garten', emoji: '🌱', hint: 'Stapel-Puzzle mit Nidula-Pflanzenformen.' },
]

export default function GamesPage() {
  const me = useAuth((s) => s.user)
  const [view, setView] = useState<'list' | 'raupe'>('list')
  const [scores, setScores] = useState<GameScores | null>(null)
  const [members, setMembers] = useState<User[]>([])

  const loadScores = useCallback(async () => {
    try {
      const [sc, mem] = await Promise.all([gamesApi.scores('raupe'), familyApi.members()])
      setScores(sc)
      setMembers(mem)
    } catch {
      // Bestenliste ist Beiwerk – Spielen geht auch ohne.
    }
  }, [])

  useEffect(() => {
    void loadScores()
  }, [loadScores])

  // Nach jeder Runde: Score speichern, Rekord-Flags zurück ans Spiel, Liste aktualisieren.
  const handleGameOver = useCallback(
    async (score: number) => {
      const result = await gamesApi.submit('raupe', score)
      await loadScores()
      return result
    },
    [loadScores],
  )

  const memberById = (id: number): User | undefined => members.find((m) => m.id === id)

  const board = (
    <div className="rounded-2xl bg-surface p-5 shadow">
      <h2 className="mb-3 flex items-center gap-2 font-semibold text-text">
        <Trophy className="h-4 w-4 text-primary" /> Familien-Bestenliste
      </h2>
      {!scores || scores.top.length === 0 ? (
        <p className="text-sm text-muted">Noch keine Runde gespielt – leg vor!</p>
      ) : (
        <ol className="space-y-2">
          {scores.top.map((entry, i) => {
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
      {scores?.my_best !== null && scores?.my_best !== undefined && (
        <p className="mt-3 text-xs text-muted">Dein Bestwert: {scores.my_best} Blätter</p>
      )}
    </div>
  )

  if (view === 'raupe') {
    return (
      <div className="mx-auto max-w-2xl space-y-4">
        <button
          onClick={() => setView('list')}
          className="flex items-center gap-1 text-sm text-muted hover:text-primary"
        >
          <ChevronLeft className="h-4 w-4" /> Zurück zur Fun Area
        </button>
        <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
          🐛 Hungrige Raupe
        </h1>
        <RaupeGame onGameOver={handleGameOver} />
        {board}
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

      {board}
    </div>
  )
}
