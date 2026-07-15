import { useEffect, useState } from 'react'
import { familyApi, gamesApi } from '../../api'
import { APP_ICONS, Trophy } from '../../lib/icons'
import type { GameScores, User } from '../../types'
import WidgetCard from './WidgetCard'

export default function GamesWidget({ onRemove }: { onRemove?: () => void }) {
  const [scores, setScores] = useState<GameScores | null>(null)
  const [members, setMembers] = useState<User[]>([])

  useEffect(() => {
    Promise.all([gamesApi.scores('raupe'), familyApi.members()])
      .then(([sc, mem]) => {
        setScores(sc)
        setMembers(mem)
      })
      .catch(() => {})
  }, [])

  const top = scores?.top.slice(0, 3) ?? []

  return (
    <WidgetCard title="Fun Area" icon={APP_ICONS.games} to="/games" onRemove={onRemove}>
      {top.length === 0 ? (
        <p className="text-sm text-muted">🐛 Noch kein Highscore – spiel die erste Runde!</p>
      ) : (
        <ol className="space-y-1.5">
          {top.map((entry, i) => {
            const member = members.find((m) => m.id === entry.user_id)
            return (
              <li key={entry.user_id} className="flex items-center gap-2 text-sm">
                {i === 0 ? (
                  <Trophy className="h-4 w-4 shrink-0 text-primary" />
                ) : (
                  <span className="w-4 text-center text-xs font-bold text-muted">{i + 1}.</span>
                )}
                <span className="min-w-0 flex-1 truncate text-text">
                  {member?.first_name ?? '–'}
                </span>
                <span className="font-bold text-primary">{entry.score}</span>
              </li>
            )
          })}
        </ol>
      )}
    </WidgetCard>
  )
}
