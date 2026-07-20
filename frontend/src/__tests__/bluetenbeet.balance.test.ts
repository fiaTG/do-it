import { describe, expect, it } from 'vitest'
import {
  createGame,
  emptyCells,
  finalScore,
  isOver,
  place,
  previewPlacement,
  type GameState,
} from '../lib/bluetenbeet'

// Balance-Test (ADR-0028, ChatGPTs Vorgabe): mehrere Strategien über feste
// Seeds. Wenn "immer höchster Delta" oder "immer Strauch" fast durchgehend
// gewinnt, sind die Regeln zu flach und müssen justiert werden.

type Strategy = (state: GameState) => { offer: number; cell: number }

const randomPlay: Strategy = (state) => {
  // Deterministisch aus dem State-RNG abgeleitet (reproduzierbar).
  const empties = emptyCells(state.board)
  const o = state.rng % state.offers.length
  const c = empties[(state.rng >>> 3) % empties.length]
  return { offer: o < 0 ? 0 : o, cell: c }
}

const greedy: Strategy = (state) => bestByDelta(state)

const shrubOnly: Strategy = (state) => {
  const shrub = state.offers.indexOf('shrub')
  if (shrub >= 0) return bestByDelta(state, shrub)
  return bestByDelta(state)
}

function bestByDelta(state: GameState, fixedOffer?: number): { offer: number; cell: number } {
  let best = { delta: -Infinity, offer: fixedOffer ?? 0, cell: emptyCells(state.board)[0] }
  const offers = fixedOffer === undefined ? state.offers.map((_, i) => i) : [fixedOffer]
  for (const o of offers) {
    for (const c of emptyCells(state.board)) {
      const delta = previewPlacement(state, o, c)
      if (delta > best.delta) best = { delta, offer: o, cell: c }
    }
  }
  return best
}

function playOut(seed: number, strategy: Strategy): number {
  let state = createGame(seed)
  while (!isOver(state)) {
    const move = strategy(state)
    state = place(state, move.offer, move.cell)
  }
  return finalScore(state)
}

function stats(seeds: number[], strategy: Strategy) {
  const scores = seeds.map((s) => playOut(s, strategy))
  const mean = scores.reduce((a, b) => a + b, 0) / scores.length
  const sd = Math.sqrt(scores.reduce((a, b) => a + (b - mean) ** 2, 0) / scores.length)
  return { mean, sd, min: Math.min(...scores), max: Math.max(...scores) }
}

describe('Blütenbeet – Balance', () => {
  // Regression-Wächter: bricht, falls jemand die Regel-Konstanten so ändert,
  // dass eine triviale Strategie dominiert. Lookahead (1-Ply) wurde lokal
  // separat geprüft (≈ greedy, Planung hilft leicht) – zu teuer fürs CI.
  it('keine triviale Strategie dominiert', () => {
    const seeds = Array.from({ length: 100 }, (_, i) => i * 7 + 1)
    const r = stats(seeds, randomPlay)
    const g = stats(seeds, greedy)
    const s = stats(seeds, shrubOnly)

    const fmt = (n: { mean: number; sd: number; min: number; max: number }) =>
      `mean=${n.mean.toFixed(1)} sd=${n.sd.toFixed(1)} min=${n.min} max=${n.max}`
    console.log(
      `\n[Balance ${seeds.length} Seeds]\n` +
        `  random    : ${fmt(r)}\n` +
        `  greedy    : ${fmt(g)}\n` +
        `  shrubOnly : ${fmt(s)}\n` +
        `  greedy/shrubOnly=${(g.mean / s.mean).toFixed(2)}\n`,
    )

    // Nachdenken schlägt Zufall deutlich.
    expect(g.mean).toBeGreaterThan(r.mean * 1.2)
    // Mono-Strategie "immer Strauch" darf NICHT so gut wie flexibles Greedy sein.
    expect(s.mean).toBeLessThan(g.mean * 0.9)
    // Spiele unterscheiden sich (Streuung vorhanden, keine Einheitsergebnisse).
    expect(g.sd).toBeGreaterThan(3)
  })
})
