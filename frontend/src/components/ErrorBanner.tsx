/** Nicht-blockierendes Fehler-Banner (z. B. für Rollback-Meldungen bei
 *  Optimistic UI). Ersetzt das frühere ganzseitige Ausblenden bei Fehlern. */
export default function ErrorBanner({
  message,
  onDismiss,
}: {
  message: string
  onDismiss?: () => void
}) {
  if (!message) return null
  return (
    <div
      role="alert"
      className="flex items-start justify-between gap-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700"
    >
      <span>{message}</span>
      {onDismiss && (
        <button
          onClick={onDismiss}
          aria-label="Meldung schließen"
          className="text-red-400 hover:text-red-600"
        >
          ✕
        </button>
      )}
    </div>
  )
}
