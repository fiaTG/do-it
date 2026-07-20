import { describe, expect, it } from 'vitest'
import {
  ALL_PLANTS,
  createGame,
  DEFAULT_RULES,
  finalScore,
  isOver,
  place,
  previewPlacement,
  scoreBoard,
  scoreCell,
  type Board,
  type Plant,
} from '../lib/bluetenbeet'

const R = DEFAULT_RULES
const S = R.size

// 5×5-Brett aus einer Kurzschreibweise bauen (Zeile r*5+c).
function board(cells: Record<number, Plant>): Board {
  const b = Array(S * S).fill(null)
  for (const [i, p] of Object.entries(cells)) b[Number(i)] = p
  return b
}

describe('Blütenbeet – Wertung je Pflanzenregel', () => {
  it('Blume mag Vielfalt: Punkte je andersartigem Nachbarn', () => {
    // Blume in der Mitte (12), Nachbarn: Kraut, Strauch, Blume, leer.
    const b = board({ 12: 'flower', 7: 'herb', 11: 'shrub', 13: 'flower' })
    // 7 (herb) + 11 (shrub) = 2 andersartige; 13 (flower) zählt nicht.
    expect(scoreCell(b, 12, R)).toBe(2 * R.flowerPerDiff)
  })

  it('Kraut mag Gesellschaft: Punkte je Kraut-Nachbarn (beidseitig)', () => {
    const b = board({ 12: 'herb', 7: 'herb', 13: 'flower' })
    expect(scoreCell(b, 12, R)).toBe(1 * R.herbPerSame)
    // Gegenseitig: das obere Kraut (7) sieht 12 ebenfalls.
    expect(scoreCell(b, 7, R)).toBe(1 * R.herbPerSame)
  })

  it('Strauch mag den Rand: Bonus nur auf Randzellen', () => {
    const edge = board({ 0: 'shrub' }) // Ecke = Rand
    const inner = board({ 12: 'shrub' }) // Mitte = kein Rand
    expect(scoreCell(edge, 0, R)).toBe(R.shrubBase + R.shrubEdgeBonus)
    expect(scoreCell(inner, 12, R)).toBe(R.shrubBase)
  })

  it('Baum: Grundwert isoliert, sinkt je Nachbarbaum, aber nie unter 0', () => {
    expect(scoreCell(board({ 12: 'tree' }), 12, R)).toBe(R.treeBase)
    // 12 mit einem Baum-Nachbarn (7)
    const one = board({ 12: 'tree', 7: 'tree' })
    expect(scoreCell(one, 12, R)).toBe(Math.max(0, R.treeBase - R.treePerAdjTree))
    // 12 von vier Bäumen umgeben -> gedeckelt bei 0
    const four = board({ 12: 'tree', 7: 'tree', 17: 'tree', 11: 'tree', 13: 'tree' })
    expect(scoreCell(four, 12, R)).toBe(0)
  })
})

describe('Blütenbeet – Zug-Delta', () => {
  it('Vorschau ist der ehrliche Gesamt-Delta inklusive Nachbarn', () => {
    // Kraut (7) liegt schon; ein zweites Kraut daneben (12) hebt BEIDE.
    const state = {
      board: board({ 7: 'herb' }),
      offers: ['herb'] as Plant[],
      movesLeft: 5,
      rng: 1,
      rules: R,
    }
    // Neues Kraut: +herbPerSame; bestehendes Kraut: +herbPerSame.
    expect(previewPlacement(state, 0, 12)).toBe(2 * R.herbPerSame)
  })

  it('Zug-Delta darf negativ sein, obwohl die Pflanze selbst nie negativ ist', () => {
    // Zwei isolierte Bäume (7, 17), Baum dazwischen (12) grenzt an beide.
    const state = {
      board: board({ 7: 'tree', 17: 'tree' }),
      offers: ['tree'] as Plant[],
      movesLeft: 5,
      rng: 1,
      rules: R,
    }
    // neuer Baum: max(0, base - 2*perAdj); die zwei alten verlieren je perAdj.
    const newTree = Math.max(0, R.treeBase - 2 * R.treePerAdjTree)
    const expected = newTree - 2 * R.treePerAdjTree
    expect(previewPlacement(state, 0, 12)).toBe(expected)
    expect(expected).toBeLessThan(0)
  })
})

describe('Blütenbeet – Angebote', () => {
  it('bietet immer genau drei verschiedene Pflanzen', () => {
    for (let seed = 0; seed < 200; seed++) {
      let state = createGame(seed)
      while (!isOver(state)) {
        expect(state.offers).toHaveLength(3)
        expect(new Set(state.offers).size).toBe(3)
        expect(state.offers.every((p) => ALL_PLANTS.includes(p))).toBe(true)
        state = place(state, 0, emptyFirst(state.board))
      }
    }
  })
})

describe('Blütenbeet – Determinismus & Immutabilität', () => {
  it('gleicher Seed + gleiche Züge -> identisches Spiel', () => {
    const a = playGreedy(createGame(1234))
    const b = playGreedy(createGame(1234))
    expect(a).toBe(b)
  })

  it('place() verändert den Ausgangs-State nicht', () => {
    const s0 = createGame(42)
    const before = [...s0.board]
    const s1 = place(s0, 0, 0)
    expect(s0.board).toEqual(before)
    expect(s1).not.toBe(s0)
    expect(s1.board[0]).toBe(s0.offers[0])
  })
})

describe('Blütenbeet – Spielende & ungültige Züge', () => {
  it('endet nach der konfigurierten Zugzahl', () => {
    let state = createGame(7)
    let moves = 0
    while (!isOver(state)) {
      state = place(state, 0, emptyFirst(state.board))
      moves++
    }
    expect(moves).toBe(R.moves)
    expect(finalScore(state)).toBe(scoreBoard(state.board, R))
  })

  it('lehnt ungültige Züge ab', () => {
    const s = createGame(7)
    expect(() => place(s, 0, 999)).toThrow()
    expect(() => place(s, 9, 0)).toThrow()
    const occupied = place(s, 0, 0)
    expect(() => place(occupied, 0, 0)).toThrow()
  })

  it('lehnt Züge nach Spielende ab', () => {
    let state = createGame(7)
    while (!isOver(state)) state = place(state, 0, emptyFirst(state.board))
    expect(() => place(state, 0, emptyFirst(state.board))).toThrow()
  })
})

// --- Helfer ------------------------------------------------------------------

function emptyFirst(b: Board): number {
  return b.findIndex((c) => c === null)
}

function playGreedy(start: ReturnType<typeof createGame>): number {
  let state = start
  while (!isOver(state)) {
    let best = { delta: -Infinity, offer: 0, cell: 0 }
    for (let o = 0; o < state.offers.length; o++) {
      for (let c = 0; c < state.board.length; c++) {
        if (state.board[c] !== null) continue
        const delta = previewPlacement(state, o, c)
        if (delta > best.delta) best = { delta, offer: o, cell: c }
      }
    }
    state = place(state, best.offer, best.cell)
  }
  return finalScore(state)
}
