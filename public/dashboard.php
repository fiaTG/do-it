<?php
/*
    Startet die Session, um Benutzerdaten zu verwalten.
    Sessions ermöglichen es, Benutzer über mehrere Seiten hinweg identifiziert zu halten.
*/
session_start();

/*
    Überprüft, ob der Benutzer eingeloggt ist, indem geprüft wird, ob eine userID in der Session existiert.
    Falls keine userID gesetzt ist, bedeutet das, dass der Benutzer nicht authentifiziert ist.
*/
if (!isset($_SESSION["userID"])) {
    /*
        Falls der Benutzer nicht eingeloggt ist, wird er zur Login-Seite weitergeleitet.
        Der "header"-Befehl sorgt dafür, dass die Seite sofort umgeleitet wird.
    */
    header("Location: login.php");
    exit(); // Beendet das Skript, um sicherzustellen, dass keine weiteren Befehle ausgeführt werden.
}

/*
    Falls der Benutzer eingeloggt ist, wird das Dashboard aus dem privaten Bereich geladen.
    "require_once" stellt sicher, dass die Datei nur einmal eingebunden wird, um doppelte Definitionen zu vermeiden.
    __DIR__ gibt das aktuelle Verzeichnis der Datei zurück, um eine sichere Pfadangabe zu gewährleisten.
*/
require_once __DIR__ . '/../private/dashboard/dashboard.php';
?>
