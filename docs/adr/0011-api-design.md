# ADR-0011: API-Design & Vertrag zwischen Backend und Frontend

- **Status:** Akzeptiert
- **Datum:** 2026-06-12
- **Betrifft:** Backend-API, Frontend-Integration

## Kontext

Mit der Trennung in Laravel-API und React-SPA (ADR-0001) wird die **API zur
zentralen Schnittstelle** für alle Clients (Web, Mobile, Desktop). Sie muss
stabil, vorhersehbar und versionierbar sein, damit native Apps, die seltener
aktualisiert werden, nicht durch Backend-Änderungen brechen. Heute gibt es
keinen API-Vertrag – die alten „Endpunkte" geben mal JSON, mal HTML, mal
Redirects zurück.

## Entscheidung

- **Stil:** RESTful, ressourcenorientiert, ausschließlich **JSON**.
- **Versionierung:** Präfix **`/api/v1/...`**. Breaking Changes → neue Version.
- **Antwortform:** konsistente Struktur über **Laravel API Resources**
  (einheitliche Felder, `data`-Wrapper, klar definierte Fehlerobjekte mit
  HTTP-Statuscodes; Validierungsfehler im Laravel-`422`-Format).
- **Auth:** Sanctum (ADR-0004) – Cookie fürs Web-SPA, Bearer-Token für native.
- **Autorisierung:** Policies stellen Familien-/Eigentums-Grenzen sicher.
- **CORS** sauber konfiguriert für die erlaubten Frontend-Ursprünge.
- **Dokumentation:** API wird dokumentiert (z. B. OpenAPI/Scribe), damit der
  Vertrag explizit und für das Frontend nachschlagbar ist.
- **Keine** Localhost-/Pfad-Literale mehr – Clients kennen nur die `API_BASE_URL`
  (behebt B3 grundlegend).

## Konsequenzen

**Positiv**

- Ein stabiler, dokumentierter Vertrag für alle Clients; native Apps brechen
  nicht bei Backend-Updates (dank Versionierung).
- Einheitliche Fehler-/Datenformate vereinfachen das Frontend erheblich.

**Negativ / Kosten**

- Mehr Disziplin/Boilerplate (Resources, Requests, Doku) als bei „schnell mal
  JSON ausgeben".
- API-Doku muss gepflegt werden.

## Alternativen

- **GraphQL** – mächtig bei vielen, variablen Abfragen, aber für diese
  überschaubare Domäne Overkill und zusätzliche Lernkurve; verworfen.
- **Ad-hoc-JSON ohne Resources/Versionierung** – schnell, aber genau die heutige
  Inkonsistenz; bricht native Clients bei Änderungen. Verworfen.
