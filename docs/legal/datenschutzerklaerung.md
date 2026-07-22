# Datenschutzerklärung (Entwurf)

> **Status: fachlich fundierter Entwurf auf Basis der tatsächlichen Technik –
> vor Stufe 2 juristisch prüfen lassen und Platzhalter füllen.** Beschreibt die
> reale Datenverarbeitung von Nidula (Stand 2026-07-22). Keine Rechtsberatung.

## 1. Verantwortlicher

Verantwortlich im Sinne der DSGVO:

**[VOLLER NAME]**, [ANSCHRIFT], E-Mail: [KONTAKT-E-MAIL] (siehe Impressum).

## 2. Überblick: Was Nidula NICHT tut

- **Keine Werbung, kein Verkauf von Daten, keine Weitergabe zu Werbezwecken.**
- **Kein Tracking, keine Analyse-Tools, keine Werbe-/Social-Media-SDKs.**
- Keine Cookies zu Tracking-Zwecken (nur ein technisch notwendiges
  Sitzungs-Cookie für den Login).
- Schriftarten werden selbst ausgeliefert (kein Abruf von Google Fonts o. Ä.).

## 3. Welche Daten wir verarbeiten

Zur Bereitstellung der Familien-Organisations-App:

- **Kontodaten:** Vor-/Nachname, E-Mail-Adresse, Passwort (nur als
  nicht umkehrbarer Hash gespeichert), Rolle in der Familie.
- **Profil (freiwillig):** Profilbild, Geburtsdatum, Geschlecht, Links zu
  sozialen Netzwerken, persönliche Farbe.
- **Familiendaten:** Familienname, selbst gewählter Heimatort (Ortsname +
  Koordinaten, für das Wetter-Widget).
- **Von Nutzern eingestellte Inhalte:** Termine, Einkaufslisten, ToDos,
  Adressbuch-Kontakte, Fotos.
- **Nutzungsdaten der Spiele:** Highscores und „Nest-Blätter"-Punkte.
- **Abo-Status:** Plan, Status, Ablaufdatum. In der aktuellen Version wird der
  Kauf **simuliert – es werden keine Zahlungsdaten erhoben.**
- **Einladungen:** die von einem Verwalter eingeladene E-Mail-Adresse.
- **Technisch beim Betrieb:** Server-Protokolle (ohne Passwörter, Tokens,
  Cookies), IP-Adresse im Rahmen der Verbindung; kurzlebige Sitzungs-/API-Token.

**Fotos:** Standort- und Kameradaten (EXIF/GPS) werden **beim Hochladen
automatisch entfernt**. Der Zugriff erfolgt nur über kurzlebige, signierte
Adressen; die Dateien liegen nicht öffentlich.

## 4. Zwecke und Rechtsgrundlagen

- **Bereitstellung der App, Konto- und Familienverwaltung, Speichern eurer
  Inhalte:** Erfüllung des Nutzungsvertrags, Art. 6 Abs. 1 lit. b DSGVO.
- **Sicherheit** (Rate-Limits, Missbrauchs-/Angriffsabwehr, Server-Logs,
  Backups): berechtigtes Interesse an einem sicheren Betrieb, Art. 6 Abs. 1
  lit. f DSGVO.
- **Freiwillige Profilangaben:** Einwilligung durch Ausfüllen, Art. 6 Abs. 1
  lit. a DSGVO (jederzeit im Profil änder-/löschbar).
- **Premium-Abrechnung** (künftig, über App-Stores/Zahlungsdienstleister):
  Vertragserfüllung, Art. 6 Abs. 1 lit. b DSGVO.

## 5. Hosting

Die App und alle Daten werden bei **Hetzner Online GmbH** (Rechenzentrum in
Deutschland) betrieben. Hetzner verarbeitet die Daten als Auftragsverarbeiter
nach unseren Weisungen; ein Vertrag zur Auftragsverarbeitung (Art. 28 DSGVO)
wird geschlossen. Datenbank und Zwischenspeicher sind nicht öffentlich
erreichbar; die Übertragung erfolgt ausschließlich verschlüsselt (HTTPS/TLS).

## 6. Empfänger / externe Dienste

Nidula bindet nur zwei externe Dienste ein, beide in der EU:

- **Wetter (Open-Meteo):** Zeigt das Wetter für den Familienort an. Der Abruf
  erfolgt **direkt aus deinem Browser** bei Open-Meteo (open-meteo.com, Betrieb
  in Deutschland); dabei werden die Ortskoordinaten bzw. der Suchbegriff der
  Ortssuche übertragen. Open-Meteo setzt keine Cookies und benötigt keinen
  Account. Rechtsgrundlage: berechtigtes Interesse an der Wetteranzeige
  (Art. 6 Abs. 1 lit. f DSGVO).
- **Spritpreise (Tankerkönig / MTS-K), nur mit Premium:** Beim Öffnen der
  Spritpreis-Ansicht fragt **unser Server** (nicht dein Browser) gerundete
  Koordinaten des Familienorts + Radius bei Tankerkönig ab. Es werden **keine
  personenbezogenen Kennungen** übertragen. Datenquelle „MTS-K", Lizenz CC BY
  4.0.

Darüber hinaus keine Weitergabe an Dritte, außer wenn gesetzlich erforderlich.

## 7. Cookies & lokale Speicherung

- **Web:** ein technisch notwendiges Sitzungs-Cookie (Login/CSRF-Schutz). Keine
  Tracking- oder Marketing-Cookies, daher kein Cookie-Banner erforderlich.
- **Native App (künftig):** Anmelde-Token wird lokal auf dem Gerät gespeichert.

## 8. Speicherdauer & Löschung

Details im internen Löschkonzept. Kurz:

- Aktive Konten/Inhalte: bis zur Löschung durch die Nutzer.
- Galerie-Papierkorb: 30 Tage, dann endgültige Löschung.
- Native Anmelde-Token: 90 Tage.
- Backups: bis zu ~14 Tage (rotierend); gelöschte Daten verschwinden dort
  spätestens mit der Rotation.

## 9. Deine Rechte

Du hast das Recht auf Auskunft (Art. 15), Berichtigung (Art. 16), Löschung
(Art. 17), Einschränkung (Art. 18), Datenübertragbarkeit (Art. 20) und
Widerspruch (Art. 21). Vieles kannst du direkt in der App erledigen
(Profil/Familie bearbeiten, Inhalte und – künftig per Selbstbedienung – dein
Konto löschen). Ansonsten genügt eine Nachricht an [KONTAKT-E-MAIL].

Zudem besteht ein **Beschwerderecht bei einer Datenschutz-Aufsichtsbehörde**
(z. B. der/die Landesbeauftragte für Datenschutz [BUNDESLAND]).

## 10. Datensicherheit

HTTPS/TLS-Verschlüsselung, Passwörter nur als Hash, Schutz vor Brute-Force
(Rate-Limits), Entfernen von Standortdaten aus Fotos, privater Medienspeicher
mit signierten Zugriffen, Registrierung nur auf persönliche Einladung.

## 11. Kinder

Nidula richtet sich an Familien. Konten für Kinder werden durch die
erziehungsberechtigten Verwalter angelegt und verwaltet; diese sind für die
Angaben ihrer Kinder verantwortlich.

## 12. Datenschutzbeauftragter

Ein Datenschutzbeauftragter ist für einen Betrieb dieser Größe voraussichtlich
nicht verpflichtend; dies wird vor einem öffentlichen Start geprüft und hier
ggf. ergänzt.

---

> **Vor Veröffentlichung:** Platzhalter füllen, Hosting-/Empfänger-Angaben
> bestätigen, und den Text mit einem seriösen Generator oder anwaltlich
> gegenprüfen. Bei Änderungen an Funktionen/Dienstleistern aktualisieren.
