# ADR-0025: Produktionsbetrieb – Sicherheitsbasis für den Web-Release

- **Status:** Akzeptiert (Grundlagen im Code umgesetzt 2026-07-17; Server-Teil offen)
- **Datum:** 2026-07-17
- **Betrifft:** Hosting (ergänzt ADR-0014), Sicherheit, Datenschutz, Web-Beta
- **Ersetzt teilweise:** den Cloudflare-Teil aus ADR-0014 (CDN/WAF vertagt)

## Kontext

Timo hat einen Hetzner-Account mit Guthaben angelegt – der Web-Release rückt
näher. Ausgangspunkt dieses ADRs war ein extern erstellter Entwurf (ChatGPT,
als „ADR-0024" nummeriert), der triagiert wurde. Der Entwurf enthielt solide
Standard-Anforderungen, aber auch Kontext-Fehler und Overkill:

- Falsche Nummer (0024 = Kalender-Freigabe), Verweis auf den nicht mehr
  existierenden Branch `modernize/phase-0-foundation`, IONOS als Option
  (ADR-0014 hat Hetzner entschieden, Account existiert bereits).
- Forderungen, die Nidula **schon erfüllt**: signierte Medien-URLs statt
  öffentlicher Buckets (ADR-0015), EXIF-Strip fail-closed, zufällige Object
  Keys (hashName), Policies mit fail-closed Familien-Scope, gehashte
  Passwörter, Token-Ablauf + Widerruf bei Passwortwechsel (Review 2026-07-16).
- Maßnahmen für Organisationen, nicht für ein Ein-Personen-Projekt in der
  Familien-Beta (SBOM je Release, Tabletop-Übungen, Vier-Augen-Adminprozesse,
  Malware-Scanner, Feldverschlüsselung) → bewusst vertagt, siehe unten.

Nidula verarbeitet sensible Daten (Familienfotos, Kinder, Standort). Ziel ist
ein ehrlicher, risikobasierter Stand der Technik in Stufen – kein
Enterprise-Theater vor dem ersten echten Nutzer.

## Entscheidung

### Stufenmodell statt einem einzigen Go-live-Gate

**Stufe 1 – Private Beta (nur Timos Familie, invite-only):** darf starten,
sobald die technische Basis steht (siehe Gates unten). Datenschutz-Papierkram
im familiären Rahmen noch nicht erforderlich. **Zugangs-Garantie technisch
erzwungen (Timos Bedingung 2026-07-17):** `NIDULA_REGISTRATION=invite`
schaltet die Registrierung komplett auf „nur mit persönlicher, E-Mail-
gebundener Einladung" – ohne Einladung kann niemand ein Konto oder eine
Familie anlegen (403). Zusätzlich legt das Deploy-Kit optional einen
Basic-Auth-„Bauzaun" vor die gesamte Seite, solange die Beta läuft –
doppelter Zaun gegen noch unbekannte Lücken.

**Stufe 2 – Freundes-Beta (fremde Familien, weiter invite-only):** zusätzlich
DSGVO-Basispaket: Datenschutzerklärung, Impressum, AV-Vertrag mit Hetzner
(im Cloud-Account abschließbar), dokumentierte Löschfristen, Prozess für
Auskunft/Löschung. DSFA-Erfordernis wird geprüft und das Ergebnis dokumentiert
(Familienfotos + Kinderdaten sprechen dafür, es ernst zu nehmen).

**Stufe 3 – Öffentliche Registrierung:** zusätzlich externer Security-Test
(mind. gezielter Pentest auf Auth/IDOR/Upload), getestete Restore-Übung,
Monitoring mit Alarmierung, Kill-Switch für Registrierung/Uploads.

### Infrastruktur (konkretisiert ADR-0014)

- **Ein Hetzner CX23** (Nachfolger des CX22 aus ADR-0014, gleiche Eckdaten;
  Falkenstein/Nürnberg = DE) mit Docker Compose:
  Caddy (Reverse Proxy, Auto-TLS), App-Container, Worker, MySQL, Redis.
  **DB/Redis bekommen KEINE Port-Mappings nach außen** – nur das interne
  Docker-Netz. Öffentlich erreichbar: nur 80/443 (Caddy) und SSH.
- **SSH nur mit Key** (ed25519), Passwort- und Root-Login deaktiviert,
  Hetzner-Firewall zusätzlich zur Host-Firewall (ufw). MFA im Hetzner-Konto.
- **Medien:** Hetzner Object Storage (privater Bucket) wie ADR-0014, Zugriff
  ausschließlich über die signierten Proxy-URLs (ADR-0015). Bucket-Keys nur
  für diesen Bucket.
- **Kein CDN/WAF zum Start** (Änderung gegenüber ADR-0014): Cloudflare vor
  privaten Familienmedien wäre ein US-Subprozessor mit Transferprüfung – für
  Familien-Beta unnötig. Caddy liefert TLS + Security-Header direkt. CDN wird
  neu bewertet, wenn echte Last da ist (ADR-0016: erst messen).
- **Backups:** Hetzner-Server-Backups (täglich, 7 Versionen) + separater
  verschlüsselter DB-Dump nach extern (3-2-1 light). Restore wird VOR Stufe 2
  einmal komplett geprobt und dokumentiert.
- **Scheduler:** cron mit `schedule:run` (Papierkorb-Purge ADR-0020 braucht
  ihn in Produktion).
- **Getrennte Umgebungen:** Prod-Server enthält keine Demo-Accounts/Seeds;
  lokale Entwicklung bekommt niemals Produktionsdaten.

### Anwendung (heute umgesetzt)

- **Rate Limits** zusätzlich zu Login (S4) und Kalender-Feed: Registrierung
  5/min je IP, Einladungen 5/min + 20/h je Nutzer (Mail-Spam), Uploads 60/min
  je Nutzer (Worker-Last, mit Luft für Galerie-Batches).
- **CORS:** Klartext-`http://localhost` ist in Produktion nicht mehr erlaubt
  (nur noch Dev); produktiv gilt exakt `FRONTEND_URL` + Capacitor-Origins.
- **`.env.production.example`:** gehärtetes Template (APP_DEBUG=false,
  Secure/SameSite-Cookies, exakte SESSION_DOMAIN/SANCTUM_STATEFUL_DOMAINS,
  LOG_LEVEL=warning, privater S3, Redis-Queue) – echte Werte nie im Repo.
- **Dependencies:** guzzle/psr7 auf abgesicherte Versionen aktualisiert,
  `composer audit` ist sauber; Lockfiles bleiben versioniert, Audit läuft ab
  jetzt bei Dependency-Runden mit.
- Security-Header (CSP, nosniff, Referrer-Policy, Permissions-Policy, Frame-
  Schutz) setzt der **Caddy-Reverse-Proxy** – Teil des Deploy-Kits (nächster
  Schritt), nicht der Anwendung.

### Bewusst vertagt (mit Begründung, Wiedervorlage in Klammern)

- **Externer Pentest** (Stufe 3): vor öffentlicher Registrierung, nicht vor
  der Familien-Beta.
- **SBOM je Release, SAST-/Container-Scan-Pipeline:** composer/npm audit +
  Dependabot decken die Beta ab (Stufe 3 / erste zahlende Fremdkunden).
- **Malware-Scan für Uploads:** Bilder werden ohnehin neu kodiert und nie
  ausführbar ausgeliefert; SVG/HTML sind nicht zugelassen (bei Datei-Anhängen
  jenseits von Bildern neu bewerten).
- **Feldverschlüsselung auf App-Ebene, separates Migrations-DB-Konto,
  manipulationsgeschützte Audit-Logs, Tabletop-Übungen:** Organisation-Scale;
  Volumes/Backups sind verschlüsselt, mehr erst bei echtem Wachstum.
- **RevenueCat/Payments:** unverändert erst zum Store-Release (ADR-0022).
- **Native Keychain-Storage:** unverändert Teil der Native-Runde – Web-Beta
  nutzt Cookie-Auth, kein Gate für den Web-Start (deckt sich mit dem Entwurf).

## Konsequenzen

- Der Web-Release kann als Stufe-1-Beta zügig starten; Sicherheit wächst in
  ehrlichen, dokumentierten Stufen mit dem Nutzerkreis.
- Ein Server, ein Compose-File: wenig Betriebsaufwand, klarer Upgrade-Pfad
  (Objekt-Storage getrennt → Serverwechsel bleibt einfach, ADR-0014).
- Ohne CDN trägt der CX23 die Asset-Auslieferung selbst – für Familien-Beta
  unkritisch, wird bei Last neu bewertet.
- Vor Stufe 2 entsteht Papierkram (Datenschutzerklärung & Co.) – eingeplant
  in docs/aufgaben.md.

## Alternativen (verworfen)

- **Ein-Klick-PaaS (Forge/Ploi/Coolify):** nimmt Arbeit ab, verschleiert aber
  genau die Betriebsgrundlagen, die dieses Projekt (auch als Portfolio) zeigen
  soll – und kostet monatlich.
- **Alles hinter Cloudflare ab Tag 1:** s. o., Transferprüfung ohne Not.
- **Warten auf den großen Sicherheits-Vollausbau:** verhindert Lernen in der
  Beta ohne realen Sicherheitsgewinn für eine geschlossene Nutzergruppe.

## Referenzen

- OWASP ASVS als Prüfleitfaden: <https://owasp.org/www-project-application-security-verification-standard/>
- BSI IT-Grundschutz APP.3.1 (Webanwendungen) als Orientierung
- DSGVO Art. 25/28/32/33/35 – relevant ab Stufe 2

## Korrektur / Nachtrag (2026-07-22, nach externem Review)

- **Server ist live** (Stufe-1-Beta hinter Bauzaun). Der Status oben („Server-
  Teil offen") bezog sich auf den Zeitpunkt der Entscheidung.
- **Verschlüsselung at rest präzisiert:** Die pauschale Aussage „alle Volumes/
  Backups werden serverseitig verschlüsselt" trifft für den aktuellen Aufbau
  NICHT automatisch zu – Hetzner-Cloud-Volumes sind standardmäßig nicht
  at-rest-verschlüsselt, und Hetzner Object Storage verschlüsselt nur per SSE-C
  (kundenseitiger Schlüssel). Die reale Verschlüsselung-at-rest **und** das
  externe, verschlüsselte Offsite-Backup (DB + Medien) sind der offene
  **Beta-Blocker Nr. 1** und werden mit der Storage-/Backup-Runde umgesetzt
  (docs/aufgaben.md; eigene Entscheidung dort zu Schlüssel-Verwahrung).
