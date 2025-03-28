<?php
require_once __DIR__ . '/../private/auth/login-handler.php';
?>

/*
Sicherheitshinweis:
        - Der Zugriff auf PHP-Dateien im Verzeichnis 'private/auth/' ist durch eine .htaccess-Datei geschützt
        - Diese .htaccess-Datei verhindert den direkten Zugriff auf PHP-Dateien außer bestimmten erlaubten Dateien
        - Dies dient dazu, sensible Skripte vor unbefugtem Zugriff zu schützen

        Einbindung des Login-Handlers
        - Die Datei 'login-handler.php' leitet zur Login.php weiter. Diese enthält die Logig für die  
        - zur Benutzeranmeldung.
    */