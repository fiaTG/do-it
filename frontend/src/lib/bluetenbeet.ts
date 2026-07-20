/**
 * Nidulas Blütenbeet – reine Spiel-Engine (ADR-0028), bewusst getrennt von
 * React/UI. Rundenbasiertes 5×5-Garten-Puzzle: pro Zug eine von drei Pflanzen
 * wählen und aufs Beet setzen; jede Art hat eine einfache Nachbarschaftsregel.
 * Gewertet wird dynamisch das GESAMTE Beet (nie ein gecachter Wert), die
 * Vorschau eines Zugs ist der ehrliche Delta auf die Gesamtwertung.
 *
 * Determinismus: seedbarer PRNG im State -> reproduzierbare Tests + Balance-
 * Simulation. Immutabilität: place() gibt einen NEUEN State zurück.
 */

export type Plant = 'flower' | 'herb' | 'shrub' | 'tree'
export type Cell = Plant | null
export type Board = readonly Cell[]

export const ALL_PLANTS: readonly Plant[] = ['flower', 'herb', 'shrub', 'tree']

/** Startwerte – bewusst konfigurierbar (nach Balance-Test final justiert). */
export interface Rules {
  size: number
  moves: number
  flowerPerDiff: number
  herbPerSame: number
  shrubBase: number
  shrubEdgeBonus: number
  treeBase: number
  treePerAdjTree: number
}

export const DEFAULT_RULES: Rules = {
  size: 5,
  moves: 20,
  flowerPerDiff: 2,
  herbPerSame: 3,
  shrubBase: 1,
  shrubEdgeBonus: 3,
  treeBase: 7,
  treePerAdjTree: 4,
}

export interface GameState {
  readonly board: Board
  /** Genau drei VERSCHIEDENE Pflanzen (pro Zug fehlt genau eine der vier). */
  readonly offers: readonly Plant[]
  readonly movesLeft: number
  readonly rng: number
  readonly rules: Rules
}

// --- Seedbarer PRNG (mulberry32-Schritt), rein durchgereicht -----------------

function nextUint(state: number): number {
  let s = state | 0
  s = (s + 0x6d2b79f5) | 0
  let t = Math.imul(s ^ (s >>> 15), 1 | s)
  t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t
  return (t ^ (t >>> 14)) >>> 0
}

function rngInt(state: number, n: number): { value: number; state: number } {
  const s = nextUint(state)
  return { value: s % n, state: s }
}

// --- Geometrie ---------------------------------------------------------------

function neighbors(index: number, size: number): number[] {
  const r = Math.floor(index / size)
  const c = index % size
  const res: number[] = []
  if (r > 0) res.push(index - size)
  if (r < size - 1) res.push(index + size)
  if (c > 0) res.push(index - 1)
  if (c < size - 1) res.push(index + 1)
  return res
}

function isEdge(index: number, size: number): boolean {
  const r = Math.floor(index / size)
  const c = index % size
  return r === 0 || r === size - 1 || c === 0 || c === size - 1
}

// --- Wertung (rein, immer aus dem Brett) -------------------------------------

/** Punkte einer einzelnen Zelle. Nie negativ (Baum ist bei 0 gedeckelt). */
export function scoreCell(board: Board, index: number, rules: Rules): number {
  const plant = board[index]
  if (!plant) return 0
  const near = neighbors(index, rules.size)

  switch (plant) {
    case 'flower':
      // mag Vielfalt: je andersartigem (nicht leerem, nicht-Blume) Nachbarn
      return rules.flowerPerDiff * near.filter((n) => board[n] && board[n] !== 'flower').length
    case 'herb':
      // mag Gesellschaft: je gleichartigem (Kraut-)Nachbarn
      return rules.herbPerSame * near.filter((n) => board[n] === 'herb').length
    case 'shrub':
      // mag den Rand
      return rules.shrubBase + (isEdge(index, rules.size) ? rules.shrubEdgeBonus : 0)
    case 'tree': {
      // braucht Abstand – aber die Pflanze selbst wird nicht negativ
      const adjTrees = near.filter((n) => board[n] === 'tree').length
      return Math.max(0, rules.treeBase - rules.treePerAdjTree * adjTrees)
    }
  }
}

export function scoreBoard(board: Board, rules: Rules): number {
  let sum = 0
  for (let i = 0; i < board.length; i++) sum += scoreCell(board, i, rules)
  return sum
}

// --- Angebote (drei verschiedene, pro Zug fehlt genau eine Art) --------------

function dealOffers(state: number): { offers: Plant[]; state: number } {
  // Eine der vier Arten auslassen ...
  const omit = rngInt(state, ALL_PLANTS.length)
  const remaining = ALL_PLANTS.filter((_, i) => i !== omit.value)
  // ... und die restlichen drei seed-deterministisch mischen (Fisher-Yates).
  const offers = [...remaining]
  let s = omit.state
  for (let i = offers.length - 1; i > 0; i--) {
    const r = rngInt(s, i + 1)
    s = r.state
    ;[offers[i], offers[r.value]] = [offers[r.value], offers[i]]
  }
  return { offers, state: s }
}

// --- Spielzustand & Züge -----------------------------------------------------

export function createGame(seed: number, rules: Rules = DEFAULT_RULES): GameState {
  const board: Cell[] = Array(rules.size * rules.size).fill(null)
  const dealt = dealOffers(seed | 0)
  return {
    board,
    offers: dealt.offers,
    movesLeft: rules.moves,
    rng: dealt.state,
    rules,
  }
}

export function isOver(state: GameState): boolean {
  return state.movesLeft <= 0
}

/** Freie Felder (es gibt immer welche: 20 Züge < 25 Felder). */
export function emptyCells(board: Board): number[] {
  const res: number[] = []
  for (let i = 0; i < board.length; i++) if (board[i] === null) res.push(i)
  return res
}

function assertValid(state: GameState, offerIndex: number, cell: number): void {
  if (isOver(state)) throw new Error('Spiel ist beendet.')
  if (offerIndex < 0 || offerIndex >= state.offers.length) throw new Error('Ungültige Pflanzenwahl.')
  if (cell < 0 || cell >= state.board.length) throw new Error('Feld außerhalb des Beets.')
  if (state.board[cell] !== null) throw new Error('Feld ist bereits belegt.')
}

/** Ehrlicher Punkte-Delta dieses Zugs auf die Gesamtwertung (inkl. Nachbarn). */
export function previewPlacement(state: GameState, offerIndex: number, cell: number): number {
  assertValid(state, offerIndex, cell)
  const before = scoreBoard(state.board, state.rules)
  const next = [...state.board]
  next[cell] = state.offers[offerIndex]
  return scoreBoard(next, state.rules) - before
}

/** Setzt eine Pflanze; gibt einen NEUEN State zurück (Original unverändert). */
export function place(state: GameState, offerIndex: number, cell: number): GameState {
  assertValid(state, offerIndex, cell)
  const board = [...state.board]
  board[cell] = state.offers[offerIndex]
  const movesLeft = state.movesLeft - 1
  const dealt = movesLeft > 0 ? dealOffers(state.rng) : { offers: [], state: state.rng }
  return {
    board,
    offers: dealt.offers,
    movesLeft,
    rng: dealt.state,
    rules: state.rules,
  }
}

/** Endwertung = dynamisch aus dem Brett. */
export function finalScore(state: GameState): number {
  return scoreBoard(state.board, state.rules)
}
