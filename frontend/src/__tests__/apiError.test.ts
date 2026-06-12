import { describe, expect, it } from 'vitest'
import { apiError } from '../api'

describe('apiError', () => {
  it('extracts the message from an axios error response', () => {
    const error = { response: { data: { message: 'Falsche Zugangsdaten.' } } }
    expect(apiError(error)).toBe('Falsche Zugangsdaten.')
  })

  it('uses the fallback when no message is present', () => {
    expect(apiError(new Error('boom'), 'Standardfehler')).toBe('Standardfehler')
  })

  it('uses the fallback for non-object input', () => {
    expect(apiError('nope', 'Standardfehler')).toBe('Standardfehler')
  })
})
