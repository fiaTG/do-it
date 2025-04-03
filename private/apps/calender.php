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
    <link rel="stylesheet" href="../../public/stylesdashb.css">
</head>
<style>
    body,
    html {
        font-family: 'Montserrat', sans-serif;
        overflow: visible;

        background-color: #e3eef5;
    }

    .calenderWrapper {
        max-width: 1200px;
        width: calc(100% - 260px);
        /* Zieht die Sidebar-Breite (z. B. 260px) ab */
        margin-left: 260px;
        /* Abstand zur Sidebar */
        padding-top: 80px;
        /* Abstand zum Header */
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    /* Kalender richtig platzieren */
    #calendar {
        max-width: 1200px;
        /* Begrenzt die maximale Breite */
        width: 100%;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        opacity: 1;
        /* Stellt sicher, dass der Kalender sichtbar ist */
        transform: translateY(0);
        /* Kalender soll nicht außerhalb des sichtbaren Bereichs sein */
    }

    .fc-toolbar-title {
        font-size: 1.5rem;
        color: #406f8f;
        font-weight: bold;
    }

    .fc-button {
        background-color: #406f8f !important;
        color: #fff !important;
        border: none !important;
        padding: 5px 10px !important;
        border-radius: 5px !important;
    }

    .fc-button:hover {
        background-color: #2c4d63 !important;
    }

    .fc-col-header-cell {
        background-color: #2c4d63;
        color: #ffffff;
        padding: 10px;
        font-weight: bold;
    }

    .fc-daygrid-day {
        background-color: #e3eef5;
        border: 1px solid #c0d6e4;
    }

    .fc-day-today {
        background-color: #406f8f !important;
        color: white !important;
        font-weight: bold;
        border-radius: 5px;
    }

    .fc-event {
        background-color: #f4a261 !important;
        border: none !important;
        color: #fff !important;
        border-radius: 5px;
        padding: 5px;
    }

    .fc-event:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease-in-out;
    }

    .fc-daygrid-day:hover {
        background-color: #8fbfdc !important;
        transition: background-color 0.3s ease-in-out;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 300px;
        text-align: center;
        border-radius: 10px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    .delete-btn {
        background-color: #ff4c4c;
        color: white;
        border: none;
        padding: 10px;
        margin-top: 10px;
        cursor: pointer;
        border-radius: 5px;
    }

    .delete-btn:hover {
        background-color: #c0392b;
    }
</style>

<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <br>
        <h2>Hey, <?php echo htmlspecialchars($row["vorname"]); ?>!</h2>
        <ul class="sidebar-menu">
            <li><a href="/files/Do-IT/public/dashboard.php"><i class="fas fa-home"></i> <span>Startseite</span></a></li>
            <li><a href="#"><i class="fas fa-user"></i> <span>Profil</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Familienmitglieder</span></a></li>
        </ul>
        <ul class="sidebar-bottom">
            <li><a href="#"><i class="fas fa-cog"></i> <span>Einstellungen</span></a></li>
            <li><a href="/files/Do-IT/private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </nav>

    <!-- Header mit Familienbild und Namen -->
    <header>
        <div class="family-info">
            <p>Kalender Familie <?php echo !empty($row['famName']) ? htmlspecialchars($row['famName']) : 'Noch keine Familie'; ?></p>
        </div>
    </header>
    
    <div class="calenderWrapper">
        <div id="calendar"></div>
    </div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        firstDay: 1,  // Wochenstart auf Montag setzen
        timeZone: 'Europe/Berlin',
        headerToolbar: {
            center: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek,listDay'
        },
        events: '/files/Do-IT/private/apps/events.php',
        selectable: true,
        editable: true,
        
        eventContent: function(arg) {
            // Falls Auto reserviert ist, füge ein Icon hinzu
            let carIcon = arg.event.extendedProps.carReserved ? ' 🚗' : '';
            
            return { 
                html: `<span>${arg.event.title}${carIcon}</span>` 
            };
        },

        select: function(info) {
            let title = prompt("Titel des Termins:");
            if (!title) return;

            let isAllDay = info.allDay;
            let start = info.startStr;
            let end = info.endStr;
            
            // Event-Daten vorbereiten
            const eventData = {
                title: title,
                start: start,
                end: end,
                allDay: isAllDay,
                carReserved: confirm("Soll ein Auto reserviert werden?") ? 1 : 0
            };

            // Event an Server senden
            fetch('/files/Do-IT/private/apps/events.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(eventData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.addEvent({
                        id: data.eventID,
                        title: eventData.title,
                        start: eventData.start,
                        end: eventData.end,
                        allDay: eventData.allDay,
                        extendedProps: { carReserved: eventData.carReserved }
                    });
                } else {
                    alert('Fehler beim Hinzufügen des Termins');
                }
            })
            .catch(error => console.error('Fehler:', error));
        },

        eventClick: function(info) {
            if (confirm(`"${info.event.title}" löschen?`)) {
                fetch('/files/Do-IT/private/apps/events.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ eventID: info.event.id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        info.event.remove();
                    } else {
                        alert('Fehler beim Löschen');
                    }
                })
                .catch(error => console.error('Fehler:', error));
            }
        },

        eventChange: function(info) {
            const updatedEvent = {
                eventID: info.event.id,
                title: info.event.title,
                start: info.event.startStr,
                end: info.event.endStr,
                allDay: info.event.allDay,
                carReserved: info.event.extendedProps.carReserved
            };

            fetch('/files/Do-IT/private/apps/events.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedEvent)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Fehler beim Aktualisieren');
                    info.revert();
                }
            })
            .catch(error => console.error('Fehler:', error));
        }
    });

    calendar.render();
});
</script>
</body>

</html>