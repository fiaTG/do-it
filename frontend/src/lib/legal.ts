/**
 * Schalter für die Rechtstexte (Impressum/Datenschutz).
 *
 * FALSE, solange die Entwürfe Platzhalter enthalten und nicht juristisch
 * geprüft sind: Die Seiten existieren (Routen `/impressum`, `/datenschutz`),
 * werden aber NICHT im Footer verlinkt und zeigen einen Entwurfs-Hinweis.
 *
 * Auf TRUE stellen, sobald Timo die Platzhalter in den Seiten gefüllt und die
 * Texte geprüft hat (docs/legal/ ist die Vorlage). Dann erscheinen die
 * Footer-Links und der Entwurfs-Banner verschwindet.
 */
export const LEGAL_PUBLISHED: boolean = false
