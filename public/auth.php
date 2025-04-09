<?php



echo "<!DOCTYPE html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Logout</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Syncopate&display=swap');

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #406f8f, #968d86);
            font-family: 'Syncopate', sans-serif;
            color: #fdfbf2;
        }
    </style>
</head>
<body>
<p> </p>
</body>
</html>";
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