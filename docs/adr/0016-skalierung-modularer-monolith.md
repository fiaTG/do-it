# ADR-0016: Skalierungsstrategie – modularer Monolith, kein verfrühtes Microservices/CQRS/Graph-DB

- **Status:** Akzeptiert
- **Datum:** 2026-06-13
- **Betrifft:** Gesamtarchitektur, Skalierung, Betrieb

## Kontext

Es kursieren Architektur-Blaupausen für „hochskalierbare Social-Media-Plattformen"
(Microservices, CQRS, Event-Sourcing/Kafka, Graph-Datenbanken, Feature-Flags mit
Canary-Releases, Edge/CDN). Diese Muster lösen die Probleme von Tech-Giganten mit
**Milliarden** Interaktionen und großen Teams.

Heimathafen ist (heute und auf absehbare Zeit) ein **modularer Familien-Organizer**
mit einer überschaubaren Nutzerzahl pro Familie und einem klar relationalen
Datenmodell (Familien, Nutzer, Termine, Listen, Bilder). Es gilt zu entscheiden,
wie viel dieser „Scale"-Muster jetzt sinnvoll ist.

## Entscheidung

**Wir bleiben beim modularen Monolithen** (eine Laravel-API mit modularen
Apps/Widgets + React-SPA, ADR-0001) und **schieben** die schweren Skalierungs-
muster bewusst auf, bis konkreter Bedarf besteht:

- **Microservices, CQRS, Event-Sourcing, Message-Broker (Kafka):** vorerst
  **nicht**. Ein gut geschnittener Monolith mit klaren Modulen reicht; diese
  Muster bringen bei kleiner Last v. a. Komplexität und Betriebsaufwand.
- **Graph-Datenbank:** **nein** – das Datenmodell ist relational, es gibt kein
  Kern-Feature „soziales Netzwerk" (Freundes-von-Freundes-Empfehlungen, riesige
  Beziehungsabfragen). MySQL bleibt.
- **Full Canary / komplexe Deployment-Pipelines:** vorerst nicht nötig.

**Was wir aber jetzt mitnehmen** (billig, hoher Nutzen):

- **Optimistic UI** im Frontend, wo es die UX spürbar verbessert (z. B. Abhaken
  von ToDos/Artikeln, Likes) – mit sauberem Fehler-Rollback.
- **Leichte, config-basierte Feature-Flags** – existieren faktisch bereits über
  Entitlements/`config/features.php` (ADR-0013); ausbaubar zu einfachen
  An/Aus-Schaltern, ohne Canary-Infrastruktur.
- **CDN & async Bildverarbeitung** sind bereits beschlossen (ADR-0014/0015).

## Auslöse-Kriterien (wann neu bewerten?)

- Ein einzelner API-/DB-Server trägt die Last dauerhaft nicht mehr (Messung, nicht
  Bauchgefühl) → gezielt einzelne Teile herauslösen (z. B. Bild-Worker ist schon
  separat).
- Ein echtes Social-/Graph-Feature wird Kernanforderung → dann Graph-DB prüfen.
- Team/Release-Frequenz wächst stark → dann Trunk-Based/Canary/Feature-Flag-Service.

## Konsequenzen

**Positiv**
- Geringe Komplexität, schnelle Entwicklung, einfacher Betrieb – passend zur
  Projektgröße und zum Solo-/Kleinteam-Kontext.
- Klare Module erlauben späteres, **gezieltes** Herauslösen (Strangler), falls nötig.

**Negativ / Risiken**
- Bei plötzlichem Hyperscale wäre eine Re-Architektur nötig – bewusst akzeptiert,
  weil verfrühte Verteilung teurer wäre als ein späterer, datengetriebener Umbau.

## Alternativen

- **Sofort Microservices/CQRS/Graph-DB** (Tech-Giganten-Blueprint) – massiver
  Komplexitäts- und DevOps-Overhead, Eventual-Consistency-Fallstricke, ohne realen
  Lastbedarf. Klassisches Over-Engineering → verworfen.
