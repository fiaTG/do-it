import type { ReactNode } from 'react'
import LegalLayout from './LegalLayout'

// Inhalt = fachlicher Entwurf auf Basis der echten Technik. Vorlage +
// vollständige Fassung: docs/legal/datenschutzerklaerung.md
function Section({ title, children }: { title: string; children: ReactNode }) {
  return (
    <section className="space-y-2">
      <h2 className="font-semibold text-text">{title}</h2>
      {children}
    </section>
  )
}

export default function DatenschutzPage() {
  return (
    <LegalLayout title="Datenschutzerklärung">
      <Section title="1. Verantwortlicher">
        <p>[VOLLER NAME], [ANSCHRIFT], E-Mail: [KONTAKT-E-MAIL] (siehe Impressum).</p>
      </Section>

      <Section title="2. Was Nidula NICHT tut">
        <ul className="list-disc space-y-1 pl-5">
          <li>Keine Werbung, kein Verkauf oder Weitergabe von Daten zu Werbezwecken.</li>
          <li>Kein Tracking, keine Analyse-Tools, keine Werbe-/Social-Media-SDKs.</li>
          <li>Keine Tracking-Cookies – nur ein technisch notwendiges Sitzungs-Cookie.</li>
          <li>Schriftarten werden selbst ausgeliefert (kein Google-Fonts-Abruf).</li>
        </ul>
      </Section>

      <Section title="3. Welche Daten wir verarbeiten">
        <p>
          Kontodaten (Name, E-Mail, Passwort nur als Hash, Rolle); freiwillige Profilangaben
          (Bild, Geburtsdatum, Geschlecht, Social-Links, Farbe); Familiendaten (Name, Heimatort);
          eingestellte Inhalte (Termine, Einkaufslisten, ToDos, Kontakte, Fotos); Spiel-Punkte;
          Abo-Status (Kauf simuliert – keine Zahlungsdaten); Einladungs-E-Mail-Adressen;
          Server-Protokolle (ohne Passwörter/Tokens). Aus Fotos werden Standort-/Kameradaten
          (EXIF/GPS) beim Hochladen automatisch entfernt.
        </p>
      </Section>

      <Section title="4. Zwecke und Rechtsgrundlagen">
        <p>
          Bereitstellung der App und Speichern eurer Inhalte: Vertragserfüllung (Art. 6 Abs. 1
          lit. b DSGVO). Sicherheit (Rate-Limits, Logs, Backups): berechtigtes Interesse (lit. f).
          Freiwillige Profilangaben: Einwilligung (lit. a), jederzeit widerrufbar.
        </p>
      </Section>

      <Section title="5. Hosting">
        <p>
          Betrieb bei der Hetzner Online GmbH (Rechenzentrum in Deutschland) als
          Auftragsverarbeiter (Art. 28 DSGVO). Übertragung ausschließlich verschlüsselt (HTTPS);
          Datenbank und Zwischenspeicher sind nicht öffentlich erreichbar.
        </p>
      </Section>

      <Section title="6. Externe Dienste (beide in der EU)">
        <ul className="list-disc space-y-1 pl-5">
          <li>
            <strong>Wetter (Open-Meteo):</strong> Abruf direkt aus deinem Browser; übertragen
            werden Ortskoordinaten bzw. der Suchbegriff. Keine Cookies, kein Account.
          </li>
          <li>
            <strong>Spritpreise (Tankerkönig/MTS-K, nur Premium):</strong> Abruf durch unseren
            Server mit gerundeten Koordinaten + Radius, ohne personenbezogene Kennungen.
          </li>
        </ul>
      </Section>

      <Section title="7. Cookies">
        <p>
          Web: ein technisch notwendiges Sitzungs-Cookie (Login/CSRF). Keine Tracking- oder
          Marketing-Cookies, daher kein Cookie-Banner nötig.
        </p>
      </Section>

      <Section title="8. Speicherdauer">
        <p>
          Aktive Konten/Inhalte bis zur Löschung durch die Nutzer; Galerie-Papierkorb 30 Tage;
          native Anmelde-Token 90 Tage; Backups bis zu ~14 Tage (rotierend).
        </p>
      </Section>

      <Section title="9. Deine Rechte">
        <p>
          Auskunft, Berichtigung, Löschung, Einschränkung, Datenübertragbarkeit und Widerspruch
          (Art. 15–21 DSGVO). Vieles direkt in der App (Profil/Familie bearbeiten, Daten
          exportieren, Inhalte löschen). Ansonsten: Nachricht an [KONTAKT-E-MAIL]. Es besteht ein
          Beschwerderecht bei einer Datenschutz-Aufsichtsbehörde.
        </p>
      </Section>

      <Section title="10. Datensicherheit">
        <p>
          HTTPS/TLS, Passwörter nur als Hash, Brute-Force-Schutz, Entfernen von Standortdaten aus
          Fotos, privater Medienspeicher mit signierten Zugriffen, Registrierung nur auf
          persönliche Einladung.
        </p>
      </Section>

      <Section title="11. Kinder">
        <p>
          Nidula richtet sich an Familien; Konten für Kinder werden durch die
          erziehungsberechtigten Verwalter angelegt und verwaltet.
        </p>
      </Section>
    </LegalLayout>
  )
}
