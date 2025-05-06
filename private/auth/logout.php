<?php
session_start();
// Alle Sitzungsvariablen löschen
$_SESSION = [];     
// Setzt das gesamte $_SESSION-Array zurück, also alle Sitzungsvariablen werden entfernt.

session_destroy();  
// Session beenden
// Zerstört die aktuelle Sitzung und entfernt die zugehörige Sitzungsdatei.


// Sitzungscookie löschen, um sicherzustellen, dass der Benutzer bei der nächsten Anfrage keine Sitzung mehr hat.
setcookie(session_name(), '', time() - 3600, '/'); 
// Löscht das Sitzungscookie, indem die Ablaufzeit auf eine Stunde in der Vergangenheit gesetzt wird.
// session_name() gibt den Namen des Sitzungscookies zurück (normalerweise "PHPSESSID").
// time() - 3600 setzt den Ablaufzeitpunkt auf eine Stunde in der Vergangenheit, damit das Cookie abläuft.
// '/' sorgt dafür, dass das Cookie auf der gesamten Domain gelöscht wird.
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -60%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }

        @keyframes loadBar {
            from { width: 0%; }
            to { width: 100%; }
        }

        .fadeInBox {
            animation: fadeIn 0.8s ease-in-out;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #406f8f, #fdfbf2);
            color: #406f8f;
            padding: 20px 30px;
            border-radius: 12px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15);
            min-width: 300px;
        }

        .progress-container {
            margin-top: 15px;
            height: 6px;
            width: 100%;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #406f8f;
            animation: loadBar 2s ease-in-out forwards;
        }

        .fadeInBox span {
            font-size: 32px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = '/files/Do-IT/public/index.php?message=logout';
        }, 2000);
    </script>
</head>
<body>
    <div class='fadeInBox'>
        <span>✔️</span>
        Du wurdest erfolgreich ausgeloggt!
        <div class='progress-container'>
            <div class='progress-bar'></div>
        </div>
    </div>
</body>
</html>";

exit();
?>
