import js from '@eslint/js'
import globals from 'globals'
import reactHooks from 'eslint-plugin-react-hooks'
import reactRefresh from 'eslint-plugin-react-refresh'
import tseslint from 'typescript-eslint'
import { defineConfig, globalIgnores } from 'eslint/config'

export default defineConfig([
  globalIgnores(['dist', 'android', 'ios']),
  {
    files: ['**/*.{ts,tsx}'],
    extends: [
      js.configs.recommended,
      tseslint.configs.recommended,
      reactHooks.configs.flat.recommended,
      reactRefresh.configs.vite,
    ],
    languageOptions: {
      globals: globals.browser,
    },
    rules: {
      // Wir laden Daten bewusst beim Mount per Effect (fetch -> setState nach
      // await). Der saubere Langfrist-Weg dafür ist TanStack Query (ADR-0008),
      // bis dahin ist dieses Muster gewollt.
      'react-hooks/set-state-in-effect': 'off',
    },
  },
])
