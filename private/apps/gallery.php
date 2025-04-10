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
            <li><a href="/files/Do-IT/private/dashboard/family_members.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-users"></i> <span>Familienmitglieder</span></a></li>
            </ul>
        <ul class="sidebar-bottom">
        <li><a href="/files/Do-IT/private/dashboard/setup.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-cog"></i> <span>Einstellungen</span></a></li>
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
    <div class="uploadRow">
    <div class="uploadContainer">
        <form action="upload_image.php" method="POST" enctype="multipart/form-data" class="uploadArea">
        <label for="image" class="button-like" title="Datei auswählen">
    <i class="fas fa-folder-open"></i> <span>Browse</span></i>
</label>
<input type="file" name="image" id="image" accept="image/jpeg, image/png, image/gif" required hidden>


            <label for="titel">Titel:</label>
            <input type="text" name="titel" id="titel" placeholder="Titel des Bildes" required>


            <button class="button-like" type="submit" name="submit">
    <i class="fas fa-plus"></i> <span>Upload</span>
</button>


            <br>
        </form>
        </div>


        <div class="latestImageWrapper">
    <div class="latestLabel">
        <h3>Latest Upload:</h3>
    </div>
    <div class="latestImageBox">
        <img src="data:image/jpeg;base64,<?= base64_encode($latestBild['bild']) ?>" 
             alt="<?= htmlspecialchars($latestBild['titel']) ?>">
        <p><?= htmlspecialchars($latestBild['titel']) ?></p>
    </div>
</div>


</div>
    

    <br>

    <div class="row">
        <?php foreach ($bilder as $bild): ?>
            <div class="column">
                <img src="data:image/jpeg;base64,<?= base64_encode($bild['bild']) ?>"
                    onclick="openModal('<?= base64_encode($bild['bild']) ?>', '<?= addslashes($bild['titel']) ?>')"
                    class="hover-shadow">
                  
                    <form method="POST" class="delete-form" data-bilderid="<?= $bild['bilderID'] ?>">
    <button type="button" class="delete-btnG" title="Bild löschen">
        <i class="fas fa-trash-alt"></i>
    </button>
</form>
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

<?php if (isset($_GET['deleted'])): ?>
    <style>
        .fadeInBox {
            animation: fadeIn 0.8s ease-in-out;
        }

        .fadeInBox,
        .progress-container {
            font-family: 'Syncopate', sans-serif;
        }

        .fadeInBox {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #406f8f, #fdfbf2);
            color: #fdfbf2;
            padding: 20px 30px;
            border-radius: 12px;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15);
            min-width: 280px;
            z-index: 9999;
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
            background: #fdfbf2;
            animation: loadBar 2s ease-in-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -60%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }

        @keyframes loadBar {
            from { width: 0%; }
            to { width: 100%; }
        }
    </style>

    <div class="fadeInBox">
        <span style="font-size: 30px;">🗑️</span><br>
        Bild erfolgreich gelöscht!
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
    </div>

    <script>
setTimeout(() => {
    const box = document.querySelector('.fadeInBox');
    if (box) {
        box.style.transition = 'opacity 0.5s ease';
        box.style.opacity = '0';
        setTimeout(() => {
            box.style.display = 'none';
            const url = new URL(window.location);
            url.searchParams.delete("deleted");
            window.history.replaceState({}, document.title, url.toString());
        }, 500);
    }
}, 2000);

    </script>
<?php endif; ?>

<!-- Modal: Bestätigung -->
<div id="confirmModal" class="modalG" style="display:none;">
    <div class="modal-contentG" style="text-align:center; padding: 20px;">
        <p>Bild wirklich löschen?</p>
        <button id="confirmYes" class="button-like" style="margin: 10px;">Ja</button>
        <button id="confirmNo" class="button-like" style="margin: 10px;">Abbrechen</button>
    </div>
</div>

<script>
    let currentForm = null;

    document.querySelectorAll('.delete-btnG').forEach(button => {
        button.addEventListener('click', function () {
            currentForm = this.closest('form');
            document.getElementById('confirmModal').style.display = 'block';
        });
    });

    document.getElementById('confirmYes').addEventListener('click', function () {
        const bilderID = currentForm.dataset.bilderid;

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'bilderID';
        hiddenInput.value = bilderID;
        currentForm.appendChild(hiddenInput);

        currentForm.action = 'delete_image.php';
        currentForm.submit();
    });

    document.getElementById('confirmNo').addEventListener('click', function () {
        document.getElementById('confirmModal').style.display = 'none';
        currentForm = null;
    });
</script>


</body>


</html>