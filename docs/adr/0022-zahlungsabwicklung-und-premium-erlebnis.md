# ADR-0022: Zahlungsabwicklung & Premium-Erlebnis

- **Status:** Akzeptiert (Grundlagen umgesetzt 2026-07-15; Store-Anbindung offen)
- **Datum:** 2026-07-15
- **Betrifft:** Monetarisierung (ADR-0013), Abo-Modell, Premium-UX, künftige Store-Releases

## Kontext

Der Premium-Kauf ist bisher simuliert (`provider: manual`). Für den Store-Release
braucht es echte Zahlungen: In-App-Käufe (Apple/Google) und perspektivisch
Web-Zahlungen (Stripe). Ausgangspunkt war ein extern erstellter ADR-Entwurf
(„Premium-Abonnement, Feature-Gating & UX-Framework"), der für Nidula in drei
Punkten korrigiert werden musste:

1. Er nahm **gesperrte Premium-Module** an – Nidula monetarisiert per ADR-0013
   aber ausschließlich über Komfort-Features/Limits, nie über gesperrte
   Kern-Apps.
2. Er dachte Abos **pro Benutzer** (`is_premium` am User) – bei Nidula kauft
   ein Mitglied für die **ganze Familie** (`subscriptions.family_id`), auch
   plattformübergreifend (Kauf auf iOS schaltet die Android-Oma frei).
3. Sein UX-Teil (Fake-Social-Proof, Premium-Farbthemes, Shimmer/Glow) kollidiert
   mit dem Markenversprechen „werbefrei & fair" (ADR-0013) und dem
   Ein-Marken-Design (ADR-0017/0018).

## Entscheidung

**Zahlungs-Infrastruktur: RevenueCat als Store-Abstraktion.**

- RevenueCat kapselt Apple/Google-IAP (später Stripe Web) inklusive Belegprüfung,
  Verlängerungen und Kündigungen; kostenlos bis ~2.500 $ Monatsumsatz.
- **AppUserID = `user-{id}`** des Käufers. Der RevenueCat-**Webhook** meldet
  Statusänderungen an unser Backend, das den Käufer auf seine **Familie** mappt
  und die bestehende `subscriptions`-Row aktualisiert (`provider: revenuecat`,
  `provider_ref`).
- Die **`subscriptions`-Tabelle bleibt Source of Truth** fürs Gating; kein
  `is_premium`-Boolean-Cache am User (Drift-Gefahr, falsche Ebene). Bei unserer
  Größe (ADR-0016) ist `family->isPremium()` performant genug.

**Pläne:** Monatlich **2,99 €** und jährlich **24,99 €** (≈ 2,08 €/Monat,
~30 % Ersparnis) – Preise final beim Store-Setup. Die simulierte Kauf-Logik
bildet beide Pläne bereits ab (`plan: monthly|yearly`).

**Gating:** Feature-**Limits** (z. B. Galerie-Kontingent) bleiben Controller-
Sache. Für künftige **reine Premium-Endpunkte** (z. B. Kalender-Sync) gibt es
die Middleware-Alias **`premium`** (403 mit deutscher Meldung).

**Premium-Erlebnis (markentreu statt Conversion-Theater):**

- **Ehrlichkeit als Prinzip:** kein erfundener Social Proof, keine
  Fake-„Bester Deal"-Badges, keine künstliche Verknappung. Die Plan-Wahl zeigt
  transparente Preise samt rechnerischer Ersparnis.
- **Ein Markendesign** (ADR-0018): keine Premium-Farbthemes, kein Avatar-Glow,
  keine Shimmer-Buttons. Premium wird durch das Crown-Badge und die
  freigeschalteten Funktionen erlebbar, nicht durch eine zweite Optik.
- **Freude-Moment:** Nach der Aktivierung gibt es einmalig Konfetti in den
  Nidula-Farben („Willkommen bei Nidula Premium!") – Belohnung ja,
  Dauerbeschallung nein.
- **Teaser-Muster für künftige Premium-Widgets:** sichtbar lassen (nicht
  ausgrauen, kein Blur), Crown-Badge + ein Satz Nutzen + Link zur Premium-Seite
  – wie heute schon beim Galerie-Limit.

## Konsequenzen

**Positiv**

- Store-fähige Zahlungsarchitektur ohne eigene Belegprüfung; neue Zahlwege
  (Stripe) später ohne Umbau, weil das Backend nur den Webhook konsumiert.
- Familien-Entitlement bleibt ein Nidula-Alleinstellungsmerkmal und funktioniert
  plattformübergreifend.
- UX bleibt konsistent mit der Marke; kein rechtliches Risiko durch
  irreführende Kaufanreize.

**Negativ / Kosten**

- Abhängigkeit von RevenueCat (Vendor); ab Umsatzschwelle kostenpflichtig.
  Gemildert: unsere DB bleibt Source of Truth, Austausch = Webhook-Adapter.
- Webhook-Endpunkt braucht Signaturprüfung und Idempotenz (bei Umsetzung).
- Apple/Google-Entwicklerkonten und Produkteinrichtung stehen noch aus.

## Alternativen

- **Eigenbau-Belegprüfung** (StoreKit/Play Billing direkt + Stripe) – volle
  Kontrolle, aber erheblicher Dauerpflege-Aufwand (Beleg-Formate,
  Verlängerungs-Edgecases) für ein Ein-Personen-Projekt unverhältnismäßig.
- **Laravel Cashier** (Stripe/Paddle) – elegant für Web-Only, deckt aber keine
  In-App-Käufe ab; als reine Web-Schiene später ggf. ergänzend über RevenueCat
  ohnehin abgedeckt.
- **UX-Vorlage des Entwurfs 1:1** – verworfen, siehe Kontext (Markenkonflikt,
  Fake-Social-Proof wäre irreführend).

## Offene Punkte

- RevenueCat-Konto, Apple-/Google-Produkte, Webhook-Endpunkt (Signatur +
  Idempotenz) – Umsetzung beim Store-Release.
- Stripe-Web-Schiene: erst bei Bedarf.
- Preisgestaltung final validieren (Steuer/Store-Gebühren).
