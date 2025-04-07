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

$stmt = $pdo->prepare("
    SELECT User.vorname, User.nachname, User.email, User.profilbild, 
           User.facebook, User.instagram, User.linkedin, User.birthdate, User.gender, User.famID
    FROM User
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "<p style='color:red;'>Benutzer nicht gefunden oder keine Familieninformationen vorhanden.</p>";
}

// Wenn das Formular abgesendet wurde (Daten aktualisieren und Bild hochladen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facebook = $_POST['facebook'] ?? '';
    $instagram = $_POST['instagram'] ?? '';
    $linkedin = $_POST['linkedin'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';

    // Datenbank aktualisieren (Social Media Links und andere Angaben)
    $stmt = $pdo->prepare("
        UPDATE User
        SET facebook = :facebook, instagram = :instagram, linkedin = :linkedin, 
            birthdate = :birthdate, gender = :gender
        WHERE userID = :userID
    ");
    $stmt->execute([
        'facebook' => $facebook,
        'instagram' => $instagram,
        'linkedin' => $linkedin,
        'birthdate' => $birthdate,
        'gender' => $gender,
        'userID' => $_SESSION['userID']
    ]);

    // Wenn ein Bild hochgeladen wurde, speichern
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];

        // Überprüfen, ob es ein Bild ist
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($image['type'], $allowedTypes)) {
            // Bild hochladen
            $imageContent = file_get_contents($image['tmp_name']);
            
            // Bild in der Datenbank speichern
            $stmt = $pdo->prepare("UPDATE User SET profilbild = :profilbild WHERE userID = :userID");
            $stmt->execute([
                'profilbild' => $imageContent,
                'userID' => $_SESSION['userID']
            ]);
        } else {
            echo "<p style='color:red;'>Nur Bilder im JPEG, PNG oder GIF Format sind erlaubt!</p>";
        }
    }

    // Erfolgreiche Rückmeldung
    header("Location: profile.php?userID={$_SESSION['userID']}&success=Profil erfolgreich aktualisiert");
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/stylesdashb.css">
    <style>
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            background-color: #f7f7f7;
        }
        .profile-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-right: 20px;
        }
        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-info {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            width: 100%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .profile-info p {
            font-size: 16px;
            margin: 10px 0;
        }
        .form-group {
            margin: 10px 0;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-group button {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            margin-top: 10px;
        }
        .form-group button:hover {
            background-color: #2980b9;
        }
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .social-icons a {
            font-size: 24px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <br>
    <h2>Hey, <?php echo htmlspecialchars($row["vorname"]); ?>!</h2>
    <ul class="sidebar-menu">
        <li><a href="/files/Do-IT/public/dashboard.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-home"></i> <span>Startseite</span></a></li>
        <li><a href="/files/Do-IT/private/dashboard/profile.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-user"></i> <span>Profil</span></a></li>
        <li><a href="#"><i class="fas fa-users"></i> <span>Familienmitglieder</span></a></li>
    </ul>
    <ul class="sidebar-bottom">
        <li><a href="#"><i class="fas fa-cog"></i> <span>Einstellungen</span></a></li>
        <li><a href="../../private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
    </ul>
</nav>

    <!-- Header mit Familienbild und Namen -->
    <header>
        <div class="family-info">
            <p> Profil von <?php echo !empty($row['vorname']) ? htmlspecialchars($row['vorname']) : 'Noch keine Familie'; ?></p>
        </div>
    </header>
 <!-- Profilcontainer -->
 <div class="profile-container">
 <div class="profile-header">
        <div class="profile-pic">
            <?php
            // Profilbild anzeigen
            if (!empty($row['profilbild'])) {
                $base64 = base64_encode($row['profilbild']);
                echo "<img src='data:image/jpeg;base64,{$base64}' alt='Profilbild' id='profileImage'>";
            } else {
                echo "<img src='/files/Do-IT/public/img/defaultUserPic.png' alt='Profilbild' id='profileImage'>";
            }
            ?>
        </div>
    </div>

        <div class="profile-info">
            <h3>Persönliche Angaben</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($row['vorname'] . ' ' . $row['nachname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>

            <!-- Social Media Links -->
            <div class="social-icons">
                <?php if (!empty($row['facebook'])): ?>
                    <a href="<?php echo htmlspecialchars($row['facebook']); ?>" target="_blank" title="Facebook"><i class="fab fa-facebook"></i></a>
                <?php endif; ?>
                <?php if (!empty($row['instagram'])): ?>
                    <a href="<?php echo htmlspecialchars($row['instagram']); ?>" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php if (!empty($row['linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($row['linkedin']); ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                <?php endif; ?>
            </div>
        </div>

        <form action="profile.php?userID=<?= $userID ?>" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="facebook">Facebook:</label>
        <input type="url" name="facebook" id="facebook" value="<?= htmlspecialchars($row['facebook'] ?? '') ?>" placeholder="https://www.facebook.com/username">
    </div>
    <div class="form-group">
        <label for="instagram">Instagram:</label>
        <input type="url" name="instagram" id="instagram" value="<?= htmlspecialchars($row['instagram'] ?? '') ?>" placeholder="https://www.instagram.com/username">
    </div>
    <div class="form-group">
        <label for="linkedin">LinkedIn:</label>
        <input type="url" name="linkedin" id="linkedin" value="<?= htmlspecialchars($row['linkedin'] ?? '') ?>" placeholder="https://www.linkedin.com/in/username">
    </div>
    <div class="form-group">
        <label for="birthdate">Geburtsdatum:</label>
        <input type="date" name="birthdate" id="birthdate" value="<?= htmlspecialchars($row['birthdate'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="gender">Geschlecht:</label>
        <select name="gender" id="gender">
            <option value="m" <?= ($row['gender'] == 'm') ? 'selected' : ''; ?>>Männlich</option>
            <option value="w" <?= ($row['gender'] == 'w') ? 'selected' : ''; ?>>Weiblich</option>
            <option value="other" <?= ($row['gender'] == 'other') ? 'selected' : ''; ?>>Andere</option>
        </select>
    </div>
    <div class="form-group">
        <label for="profileImageUpload">Profilbild hochladen:</label>
        <input type="file" name="image" id="profileImageUpload" accept="image/*" onchange="previewImage(event)">
    </div>
    <div class="form-group">
        <button type="submit">Speichern</button>
    </div>
</form>



    <script>
document.addEventListener('DOMContentLoaded', function () {
    // Zeigt das ausgewählte Bild vorübergehend an
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profileImage');
            output.src = reader.result; // Setzt das ausgewählte Bild als Profilbild
        };
        reader.readAsDataURL(event.target.files[0]);
        document.getElementById('submitButton').style.display = 'block'; // Button sichtbar machen
    }

    // Sicherstellen, dass fileInput vorhanden ist, wenn du die Datei vorab anzeigen willst
    const fileInput = document.getElementById('profileImageUpload');
    if (fileInput) {
        fileInput.addEventListener('change', previewImage);
    }
});
</script>

</body>
</html>
