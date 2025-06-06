<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php'; // Datenbankverbindung sicherstellen


// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

$famID = $_GET['famID'] ?? null; // Wenn kein Parameter vorhanden ist, wird null gesetzt
$userID = $_GET['userID'] ?? null;

// Daten des eingeloggten Nutzers inkl. Familiennamen abrufen
$stmt = $pdo->prepare("
    SELECT User.vorname, User.famID, Family.famName 
    FROM User 
    LEFT JOIN Family ON User.famID = Family.famID 
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "<p style='color:red;'>Benutzer nicht gefunden oder keine Familieninformationen vorhanden.</p>";
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/stylesApps.css">
   


</head>
<style>
    
        body {
            font-family: 'Montserrat', sans-serif;
            font-size: 10px;
        }
        #calendar {
            max-width: 100%;
            margin: auto;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 8px;
        }
        .fc-daygrid-event {
            background-color: #4CAF50; /* Grüner Hintergrund für die Events */
            color: white; /* Weißer Text */
            border-radius: 5px;
            padding: 5px;
            margin: 2px 0;
        }
        .fc-daygrid-event:hover {
            background-color: #45a049; /* Dunklerer Grünton beim Hover */
        }
        .calenderWrapper {
    max-width: unset;
    width: 100%; 
     margin-left: 0px ; 
     padding-top: 0px ; 
    display: flex
;
    justify-content: center;
    align-items: flex-start;
    }
    </style>

<body>
    <div class="calenderWrapper">
        <div id="calendar"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'listDay',  // ❗ Nur Tagesansicht
                headerToolbar: false,    // ❗ Menü ausblenden
                events: '/files/Do-IT/private/apps/events.php', // ❗ Deine bestehende Event-Quelle nutzen
                eventContent: function(arg) {
    const carReserved = arg.event.extendedProps.carReserved;
    const carIcon = (carReserved === "1" || carReserved === 1) ? ' 🚗' : '';

    return {
        html: `<span>${arg.event.title}${carIcon}</span>`
    };
}

            });

            calendar.render();
        });
    </script>
</body>

</html>