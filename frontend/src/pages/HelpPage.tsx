import { CircleHelp, Mail } from '../lib/icons'
import { useAuth } from '../store/auth'

/**
 * Hilfe & Support (Beta-Feedback 2026-07-18): Kurzanleitung je App plus die
 * ehrliche Antwort auf "wo melde ich Probleme?". Native <details>-Elemente
 * statt Accordion-Lib – leichtgewichtig und barrierefrei.
 */

const TOPICS: { title: string; emoji: string; lines: string[] }[] = [
  {
    title: 'Erste Schritte & Dashboard',
    emoji: '🏠',
    lines: [
      'Auf dem Dashboard wählst du über „App hinzufügen", welche Bereiche DU nutzen willst – jedes Familienmitglied stellt sich sein eigenes Dashboard zusammen.',
      'Die Kacheln (Widgets) zeigen dir das Wichtigste jeder App auf einen Blick; Antippen öffnet die App.',
      'Tipp fürs Handy: Im Browser-Menü „Zum Startbildschirm hinzufügen" wählen – dann startet Nidula wie eine echte App.',
    ],
  },
  {
    title: 'Einkaufsliste',
    emoji: '🛒',
    lines: [
      'Artikel eintippen, optional Menge und Laden zuordnen – die Liste sieht die ganze Familie live.',
      'Abhaken beim Einkaufen; „PDF" erzeugt eine druckbare Liste.',
    ],
  },
  {
    title: 'Kalender',
    emoji: '📅',
    lines: [
      'Jede Person hat ihre eigene Farbe; in der Legende kannst du Personen ein- und ausblenden.',
      'Termin anlegen: freien Bereich antippen (am Handy kurz gedrückt halten). Serien (z. B. „alle 2 Wochen Gelber Sack") über das Wiederholungs-Feld.',
      'Kinder-Konten können nur eigene Termine anlegen und nur selbst angelegte ändern.',
      'Premium: „Kalender-Abos" holt Schul-/Vereins-/Abfallkalender (iCal) als eigene Ebene dazu; „Teilen" bringt eure Termine in den Google-/Apple-Kalender aufs Handy.',
    ],
  },
  {
    title: 'ToDos',
    emoji: '✅',
    lines: [
      'Gemeinsame Aufgabenliste für die ganze Familie – eintragen, abhaken, fertig.',
      'Ein Belohnungssystem ist in Planung (euer Feedback!). 😉',
    ],
  },
  {
    title: 'Galerie',
    emoji: '🖼️',
    lines: [
      'Fotos einzeln oder als Stapel hochladen (auch per Ziehen & Ablegen). Ortsdaten (GPS) werden beim Hochladen automatisch entfernt.',
      'Gelöschte Bilder liegen 30 Tage im Papierkorb und lassen sich wiederherstellen.',
      'Free: 30 Bilder je Familie – Premium hebt das Limit auf.',
    ],
  },
  {
    title: 'Adressbuch',
    emoji: '📇',
    lines: [
      'Wichtige Kontakte der Familie: Kinderarzt, Schule, Oma … mit Foto, Telefon und Notizen.',
      'Ändern/Löschen darf, wer den Kontakt angelegt hat – und Verwalter.',
    ],
  },
  {
    title: 'Fun Area & Spritpreise',
    emoji: '🎮',
    lines: [
      '„Hungrige Raupe": am Handy wischen, am PC Pfeiltasten/WASD. Die Familien-Bestenliste zählt automatisch mit.',
      'Spritpreise (Premium) zeigt aktuelle Preise rund um euren Familienort (Daten: Tankerkönig.de).',
    ],
  },
  {
    title: 'Familie, Rollen & Einladungen',
    emoji: '👨‍👩‍👧',
    lines: [
      'Verwalter dürfen alles; Kinder-Konten haben eingeschränkte Rechte (z. B. nur eigene Termine).',
      'Einladen (nur Verwalter): Familienseite → E-Mail eintragen → Rolle wählen → bei „Offene Einladungen" den Link kopieren und persönlich verschicken.',
      'Wichtig: Die Einladung funktioniert nur mit genau der eingeladenen E-Mail-Adresse.',
    ],
  },
  {
    title: 'Premium',
    emoji: '👑',
    lines: [
      'Ein Abo gilt für die GANZE Familie: unbegrenzte Galerie, Kalender-Abos, Kalender-Freigabe, Spritpreise.',
      'Die Kernfunktionen bleiben für immer gratis – Premium finanziert die werbefreie Weiterentwicklung.',
    ],
  },
]

export default function HelpPage() {
  const isGuardian = useAuth((s) => s.user?.role === 'guardian')

  return (
    <div className="mx-auto max-w-2xl space-y-5">
      <h1 className="flex items-center gap-2 text-2xl font-bold text-primary">
        <CircleHelp className="h-6 w-6" /> Hilfe & Support
      </h1>

      <div className="space-y-2">
        {TOPICS.map((t) => (
          <details key={t.title} className="group rounded-2xl bg-surface shadow">
            <summary className="flex cursor-pointer items-center gap-2 rounded-2xl p-4 font-semibold text-text marker:content-none group-open:pb-2">
              <span aria-hidden="true">{t.emoji}</span> {t.title}
            </summary>
            <ul className="space-y-1.5 px-4 pb-4 pl-11 text-sm text-muted">
              {t.lines.map((line) => (
                <li key={line} className="list-disc">
                  {line}
                </li>
              ))}
            </ul>
          </details>
        ))}
      </div>

      <div className="rounded-2xl bg-surface p-5 shadow">
        <h2 className="flex items-center gap-2 font-semibold text-text">
          <Mail className="h-4 w-4 text-primary" /> Problem oder Idee? So erreichst du uns
        </h2>
        <p className="mt-2 text-sm text-muted">
          Nidula ist in der <span className="font-semibold text-text">Familien-Beta</span> –
          Support läuft deshalb noch auf dem kurzen Dienstweg:{' '}
          {isGuardian
            ? 'Fehler und Wünsche einfach direkt an die Entwicklung melden (du weißt, wo Timo wohnt 😄).'
            : 'sag einfach eurem Familien-Verwalter Bescheid, der leitet es an die Entwicklung weiter.'}{' '}
          Ein eingebautes Feedback-Formular und E-Mail-Support kommen mit dem offiziellen Start.
        </p>
        <p className="mt-2 text-xs text-muted">
          Bei jedem Fehler hilft uns: Was hast du gemacht, was hast du erwartet, was ist
          stattdessen passiert – am besten mit Screenshot.
        </p>
      </div>
    </div>
  )
}
