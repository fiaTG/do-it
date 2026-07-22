import LegalLayout from './LegalLayout'

// Inhalt = Entwurf, Platzhalter in [eckigen Klammern]. Vorlage: docs/legal/impressum.md
export default function ImpressumPage() {
  return (
    <LegalLayout title="Impressum">
      <h2 className="font-semibold text-text">Angaben gemäß § 5 DDG</h2>
      <p>
        [VOLLER NAME]
        <br />
        [STRASSE HAUSNUMMER]
        <br />
        [PLZ ORT]
        <br />
        Deutschland
      </p>

      <h2 className="font-semibold text-text">Kontakt</h2>
      <p>E-Mail: [KONTAKT-E-MAIL]</p>

      <h2 className="font-semibold text-text">Verantwortlich für den Inhalt (§ 18 Abs. 2 MStV)</h2>
      <p>[VOLLER NAME], Anschrift wie oben.</p>

      <h2 className="font-semibold text-text">Verbraucherstreitbeilegung</h2>
      <p>
        Wir sind nicht bereit und nicht verpflichtet, an Streitbeilegungsverfahren vor einer
        Verbraucherschlichtungsstelle teilzunehmen.
      </p>

      <h2 className="font-semibold text-text">Haftung für Inhalte</h2>
      <p>
        Inhalte dieses Dienstes werden mit Sorgfalt erstellt; für Richtigkeit, Vollständigkeit und
        Aktualität wird keine Gewähr übernommen. Für Inhalte, die Nutzer selbst einstellen (z. B.
        Fotos, Termine, Kontakte), ist der jeweilige Nutzer verantwortlich.
      </p>
    </LegalLayout>
  )
}
