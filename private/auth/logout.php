<?php
session_start();
// Alle Sitzungsvariablen löschen
$_SESSION = [];
// Session beenden
session_destroy();
//Session cookies löschen
setcookie(session_name(), '', time() - 3600, '/');

echo "<!DOCTYPE html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Logout</title>
    <script>
        setTimeout(function() {
            window.location.href = '/files/Do-IT/public/index.php?message=logout';
        }, 2000); // 2 Sekunden Verzögerung
    </script>
</head>
<body>
    <h2 style='color: green; text-align: center;'>Du wurdest erfolgreich ausgeloggt!</h2>
    <p style='text-align: center;'>Du wirst in 2 Sekunden zur Startseite weitergeleitet...</p>
</body>
</html>";

// Zur Login-Seite oder Startseite umleiten

exit();
?>
