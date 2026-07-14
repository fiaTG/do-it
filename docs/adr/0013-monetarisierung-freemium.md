# ADR-0013: Monetarisierung – werbefreies Freemium statt In-App-Werbung

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Geschäftsmodell, Produktstrategie, Datenschutz

## Kontext

Die App (geplante Umbenennung von „Family Board" zu **„Heimathafen"**, da es mit
*FamilyWall* u. a. bereits sehr ähnliche Markennamen gibt) wird als modularer,
datenschutzfreundlicher Familienorganizer (Dashboard mit Widgets) eigenständig
neu entwickelt. Der Markt ist umkämpft: etablierte Apps wie *FamilyWall* legen
viele Kernfunktionen hinter eine Paywall, andere Mitbewerber finanzieren sich
über Werbung.

Das Projekt soll langfristig die **Haupteinnahmequelle** werden. Zur Entscheidung
stand, ob die App über **Werbebanner** (mit Kinderschutzfiltern und einer
„werbefrei"-Kaufoption) oder über ein **werbefreies, faires Freemium-Modell**
finanziert wird.

## Entscheidung

**Gegen In-App-Werbung, für ein ehrliches, werbefreies Freemium-Modell.**

- Die **Kernfunktionen** für den Familienalltag (Kalender, To-Do-Listen,
  Basis-Dashboard) bleiben **dauerhaft kostenlos und zu 100 % werbefrei**.
- Monetarisiert wird **ausschließlich** über fortgeschrittene Komfort-Features
  und Zusatz-Widgets – z. B. unbegrenzter Cloud-Speicher für Medien, Synchro mit
  externen Kalendern u. a. – per **In-App-Kauf** oder **fairem Familien-Abo**
  (Richtwert ~2,99 €/Monat).

Die bereits zentrale **modulare Widget-Architektur** (App-Auswahl pro Nutzer)
ist die natürliche Grundlage: Premium-Widgets reihen sich nahtlos in den
bestehenden Mechanismus ein.

## Begründung

- **Marktvorteil & Vertrauen:** Das Versprechen „100 % werbefrei und kindersicher
  ab Werk" hebt Heimathafen sofort positiv von der Konkurrenz ab und stärkt das
  emotionale Markenimage (Sicherheit, Vertrauen, Familie).
- **Geringeres rechtliches Risiko:** Ohne Werbenetzwerke (z. B. Google AdMob)
  entfällt das Risiko, Kindern unpassende Werbung auszuspielen; der Aufwand für
  DSGVO-/COPPA-konforme Tracking-Sperren sinkt drastisch.
- **Höheres Umsatzpotenzial:** Ein einziges faires Abo bringt verlässlicheren
  Umsatz als tausende Klicks auf niedrig bezahlte, kindersichere Banner.

## Konsequenzen

**Positiv**

- Klares, vertrauensbildendes Markenversprechen als Verkaufsargument.
- Weniger Datenschutz-/Jugendschutz-Risiko und -Aufwand.
- Planbarer, wiederkehrender Umsatz (Abo) statt schwankender Werbeerlöse.

**Negativ / Kosten**

- **Höherer Fokus auf Produktqualität:** Da niemand durch Werbung „zum Zahlen
  genervt" wird, müssen die Premium-Features aus eigenem Antrieb begehrenswert
  sein.
- **Verzögerter Cashflow:** Einnahmen fließen nicht ab Tag 1, sondern erst, wenn
  eine treue, intensiv nutzende Basis aufgebaut ist.
- **Infrastruktur/Implementierung:** Ein sicheres Bezahlsystem (In-App-Purchases
  über Apple App Store und Google Play) muss im Code vorbereitet werden – inkl.
  **serverseitiger Freischaltungsprüfung** (Entitlements) in der API, damit
  Premium-Features nicht clientseitig umgangen werden. Das knüpft an
  [ADR-0012](0012-multi-client-packaging.md) (native Pakete) und die Token-Auth
  aus [ADR-0004](0004-auth-und-session-sicherheit.md) an.

## Alternativen

- **Werbebanner + „werbefrei"-Kaufoption** – sofortiger Cashflow, aber
  rechtliches/Jugendschutz-Risiko, schwächeres Markenimage und geringerer Umsatz
  je Nutzer. Verworfen.
- **Reine Paywall** (Kernfunktionen kostenpflichtig, wie FamilyWall) – hohe
  Einstiegshürde, schlechtere Verbreitung/Mundpropaganda. Verworfen.
- **Komplett kostenlos / spendenbasiert** – kein tragfähiges, planbares
  Geschäftsmodell für die angestrebte Haupteinnahmequelle. Verworfen.
