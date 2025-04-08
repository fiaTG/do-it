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
 
</style>

<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <br>
        <h2>Hey, <?php echo htmlspecialchars($row["vorname"]); ?>!</h2>
        <ul class="sidebar-menu">
            <li><a href="/files/Do-IT/public/dashboard.php"><i class="fas fa-home"></i> <span>Startseite</span></a></li>
            <li><a href="/files/Do-IT/private/dashboard/profile.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-user"></i> <span>Profil</span></a></li>
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

    <div class="legend">
    <h3>Legende:</h3>
    <div class="legend-item">
        <span class="legend-color" style="background-color:rgba(64, 111, 143, 0.73) " ></span> Arbeit
        <span class="legend-color" style="background-color:  #968d86 "></span> Familie
        <span class="legend-color" style="background-color: #F39C12" ></span> Freizeit
        <span class="legend-color" style="background-color:  #BDC3C7"></span> Sonstiges
        <span class="legend-icon">    <div class="car-container">
      <div class="car" id="car">
        <div class="window"></div>
        <div class="window middle"></div> <!-- Drittes Fenster -->
        <div class="window right"></div>
        <div class="wheel left">
          <div class="wheel-center"></div> <!-- Weißer Punkt in der Mitte des Rades -->
        </div>
        <div class="wheel right">
          <div class="wheel-center"></div> <!-- Weißer Punkt in der Mitte des Rades -->
        </div>
        <div class="dust left"></div> <!-- Staubwolke links -->
        <!-- Spiegel -->
        <div class="mirror"></div> <!-- Spiegel -->
        <!-- Antenne -->
        <div class="antenna"></div> <!-- Antenne -->
      </div>
    </div></span> Auto reserviert
    </div>




    </div>

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
        eventOverlap: false, // Verhindern, dass sich Events überlappen
        eventContent: function(arg) {
    let carIcon = (arg.event.extendedProps.carReserved === "1" || arg.event.extendedProps.carReserved === 1) 
        ? ' 🚗'
        : '';

    return { 
        html: `<div class="event-content">${arg.event.title}${carIcon}</div>` 
    };
},


        select: function(info) {
    // Modal-Fenster erstellen
    let modal = document.createElement("div");
    modal.innerHTML = `
        <div class="modal-content">
            <h3>Neues Event erstellen</h3>
            <label for="eventTitle">Titel:</label>
            <input type="text" id="eventTitle" placeholder="Event-Titel" required>

            <label for="eventCategory">Kategorie:</label>
            <select id="eventCategory">
                <option value="Arbeit">Arbeit</option>
                <option value="Familie">Familie</option>
                <option value="Freizeit">Freizeit</option>
                <option value="Sonstiges">Sonstiges</option>
            </select>

            <label>
                <input type="checkbox" id="eventCarReserved"> Auto reservieren 🚗
            </label>

            <div class="modal-buttons">
                <button id="saveEvent">Speichern</button>
                <button id="closeModal">Abbrechen</button>
            </div>
        </div>
    `;
    modal.classList.add("modal");
    document.body.appendChild(modal);
    modal.style.display = "flex";

    // Event speichern
    document.getElementById("saveEvent").addEventListener("click", function() {
    let title = document.getElementById("eventTitle").value;
    let category = document.getElementById("eventCategory").value;
    let carReserved = document.getElementById("eventCarReserved").checked ? 1 : 0;  // carReserved wird korrekt gesetzt, auch wenn es nicht aktiviert ist

    if (!title) {
        alert("Bitte einen Titel eingeben!");
        return;
    }

    let eventData = {
        title: title,
        start: info.startStr,
        end: info.endStr,
        allDay: info.allDay,
        carReserved: carReserved,  // carReserved wird hier korrekt übergeben
        category: category
    };

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
                extendedProps: { 
                    carReserved: eventData.carReserved,  // carReserved wird korrekt weitergegeben
                    category: eventData.category
                }
            });
        } else {
            alert('Fehler beim Hinzufügen des Termins');
        }
    })
    .catch(error => console.error('Fehler:', error));

    modal.remove();
});

    // Modal schließen
    document.getElementById("closeModal").addEventListener("click", function() {
        modal.remove();
    });
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

        eventDidMount: function(info) {
    let category = info.event.extendedProps.category;

    let colors = {
    "Arbeit": "rgba(64, 111, 143, 0.73)",  // Primärfarbe: Blau-Grün-Ton
    "Familie": "#968d86", // Sekundärfarbe: Graubraun-Ton
    "Freizeit": "#F39C12", // Ein lebendiges, sonniges Gelb für Freizeit
    "Sonstiges": "#BDC3C7" // Ein dezentes, elegantes Silber-Grau für Sonstiges
};

    let color = colors[category] || colors["Sonstiges"];
    info.el.style.backgroundColor = color;
},


eventChange: function(info) {
    const updatedEvent = {
        eventID: info.event.id,
        title: info.event.title,
        start: info.event.startStr,
        end: info.event.endStr,
        allDay: info.event.allDay,
        carReserved: info.event.extendedProps.carReserved,
        category: info.event.extendedProps.category // Kategorie hinzufügen
    };

    fetch('/files/Do-IT/private/apps/events.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updatedEvent) // Alle Event-Daten senden
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