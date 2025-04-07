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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../../public/stylesdashb.css">
    <link rel="stylesheet" href="../../public/stylesApps.css">
</head>

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
            <li><a href="/files/Do-IT/private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span></a></li>
        </ul>
    </nav>

    <!-- Header mit Familienbild und Namen -->
    <header class="dashboard-header">

        <div class="family-info">
            <p>Gallerie Familie
                <?php echo !empty($row['famName']) ? htmlspecialchars($row['famName']) : 'Noch keine Familie'; ?></p>
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
                    alt="<?= htmlspecialchars($latestBild['titel']) ?>" style="max-width: 100%; border-radius: 8px;">
                <p><?= htmlspecialchars($latestBild['titel']) ?></p>
            <?php else: ?>
                <p>Keine Bilder vorhanden.</p>
            <?php endif; ?>
        </div>
    </div>

    <br>

    <div class="row">
        <?php foreach ($bilder as $bild): ?>
            <div class="column">
                <img src="data:image/jpeg;base64,<?= base64_encode($bild['bild']) ?>"
                    onclick="openModal('<?= base64_encode($bild['bild']) ?>', '<?= addslashes($bild['titel']) ?>')"
                    class="hover-shadow">
            </div>
        <?php endforeach; ?>
    </div>


<!-- Modal für das Bild -->
<div id="myModal" class="modalG">
    <span class="close cursor" onclick="closeModal()">&times;</span>
    <div class="modal-contentG">
        <img src="" id="modalImage" style="width:100%">
        <div class="caption-container">
            <p id="caption"></p>
        </div>
    </div>
</div>

    <script>
// Funktion zum Öffnen des Modals
function openModal(bildData, titel) {
    document.getElementById("myModal").style.display = "block";

    // Setze das Bild im Modal
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