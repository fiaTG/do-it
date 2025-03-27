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

// Abrufen der Benutzerinformationen einschließlich Familienname
$stmt = $pdo->prepare("
    SELECT User.vorname, User.famID, Family.famName 
    FROM User 
    LEFT JOIN Family ON User.famID = Family.famID 
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Abrufen der Bilder aus der Datenbank
$stmt = $pdo->prepare("SELECT bilderID, titel, bild FROM Bilder WHERE famID = :famID ORDER BY uploaded DESC");
$stmt->execute(['famID' => $row['famID']]);
$bilder = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Abrufen des zuletzt hochgeladenen Bildes
$stmtLatest = $pdo->prepare("SELECT titel, bild FROM Bilder WHERE famID = :famID ORDER BY uploaded DESC LIMIT 1");
$stmtLatest->execute(['famID' => $row['famID']]);
$latestBild = $stmtLatest->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie</title>
    <style>
/* Galerie Layout */
    body, html{
        overflow: visible !important;
        overflow-x: hidden !important;
        max-width: 100%;
        }

        
.family-info p{
    position: relative !important;
    left: 45% !important;
}
.row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px; 
    justify-content: center;
    align-items: center;
    margin-left: 250px; /* Abstand zur Sidebar erhöhen */
    margin-right: auto;
    
}

        .column {
            width: calc(25% - 16px);
            max-width: 300px;
            box-sizing: border-box;
            height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .column img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .column img:hover {
            transform: scale(1.02); /* Weniger Skalierung für dezenteren Effekt */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Optionaler Effekt */
        }

        /* Modal Styles */
        .modalG {
    display: none; /* Standardmäßig versteckt */
    position: fixed;
    z-index: 1000;
    left: 50%;
    top: 50%;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    transform: translate(-50%, -50%); /* Exakte Zentrierung */
}

.modal-contentG {
    position: absolute;
    left: 50%;
    top: 50%;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 90%;
    max-height: 90vh;
    text-align: center;
    transform: translate(-50%, -50%); /* Exakte Zentrierung */
}


        #modalImage {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 80vh;
        }

        .close {
            color: white;
            position: absolute;
            top: 10px;
            right: 25px;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #999;
            text-decoration: none;
        }

/* Upload-Bereich */


.uploadArea label {
    font-size: 1.2rem;
    font-weight: bold;
}

.uploadArea input, 
.uploadArea button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.uploadArea button {
    background: #2980b9;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s;
}

.uploadArea button:hover {
    background:rgb(52, 158, 230);
}


.latestImageArea img {
    width: 100%;
    max-width: 300px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
.latestImageArea p {
    font-size: 1rem;
    text-align: center;
}

.flex-container {
    display: flex;
    gap: 100px; /* Abstand zwischen den beiden Bereichen */
    justify-content: flex-start; /* Beide Bereiche nebeneinander ausrichten */
    align-items: stretch; /* Beide Bereiche gleichmäßig ausrichten */
    margin: 20px auto; /* Zentrierung des Containers */
    max-width: 800px; /* Begrenzte Gesamtbreite */
}

.uploadArea, .latestImageArea {
    background: #fff; /* Einheitlicher Hintergrund */
    padding: 20px; /* Gleicher Innenabstand */
    border-radius: 10px; /* Abgerundete Ecken */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Einheitliche Schatten */
    width: 50%; /* Beide Bereiche nehmen gleiche Breite ein */
    display: flex;
    flex-direction: column;
    justify-content: center; /* Inhalt vertikal zentrieren */
    box-sizing: border-box; /* Sicherstellen, dass Padding berücksichtigt wird */
    height: auto; /* Höhe gleich für beide */
}
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../../public/stylesdashb.css">
</head>
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
    <header class="dashboard-header">
       
        <div class="family-info">
            <p>Gallerie Familie <?php echo !empty($row['famName']) ? htmlspecialchars($row['famName']) : 'Noch keine Familie'; ?></p>
        </div>
    </header>

    <!-- Formular zum Hochladen eines Bildes -->
    <div class="flex-container">
    <!-- Upload-Bereich -->
    <form action="upload_image.php" method="POST" enctype="multipart/form-data" class="uploadArea">
        <label for="image">Bild auswählen:</label>
        <input type="file" name="image" id="image" accept="image/jpeg, image/png, image/gif" required>
        
        <label for="titel">Titel des Bildes:</label>
        <input type="text" name="titel" id="titel" placeholder="Titel des Bildes" required>
        
        <button type="submit" name="submit">Bild hochladen</button>
        <br>
    </form>

    <!-- Fenster für das zuletzt hinzugefügte Bild -->
    <div class="latestImageArea">
        <?php if ($latestBild): ?>
            <h3>Letztes hochgeladenes Bild:</h3>
            <img src="data:image/jpeg;base64,<?= base64_encode($latestBild['bild']) ?>" 
                 alt="<?= htmlspecialchars($latestBild['titel']) ?>" 
                 style="max-width: 100%; border-radius: 8px;">
            <p><?= htmlspecialchars($latestBild['titel']) ?></p>
        <?php else: ?>
            <p>Keine Bilder vorhanden.</p>
        <?php endif; ?>
    </div>
</div>


    <br>
    


    <!-- Bilder anzeigen -->
<!-- Bilder anzeigen -->
<div class="row">
    <?php foreach ($bilder as $bild): ?>
        <div class="column">
            <img src="data:image/jpeg;base64,<?= base64_encode($bild['bild']) ?>" 
                 onclick="openModal('<?= base64_encode($bild['bild']) ?>', '<?= addslashes($bild['titel']) ?>')" class="hover-shadow">
        </div>
    <?php endforeach; ?>
</div>


    <!-- Modal für das Bild -->
    <div id="myModal" class="modalG">
        <span class="close cursor" onclick="closeModal()">&times;</span>
        <div class="modal-contentG">
            <div class="mySlides">
                <div class="numbertext">1 / <?= count($bilder) ?></div>
                <img src="" id="modalImage" style="width:100%">
            </div>
            <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
            <a class="next" onclick="plusSlides(1)">&#10095;</a>
            <div class="caption-container">
                <p id="caption"></p>
            </div>
        </div>
    </div>

    <script>
        // Funktion zum Öffnen des Modals
        function openModal(bildData, titel) {
    console.log("Bild Data: ", bildData); // Überprüfe die Bilddaten
    console.log("Titel: ", titel); // Überprüfe den Titel
    
    document.getElementById("myModal").style.display = "block";
    
    var modalImage = document.getElementById("modalImage");
    modalImage.src = "data:image/jpeg;base64," + bildData;  // Bilddaten direkt verwenden
    document.getElementById("caption").innerHTML = titel;  // Titel im Modal setzen
}


        // Funktion zum Schließen des Modals
        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }
    </script>
</body>


</html>
