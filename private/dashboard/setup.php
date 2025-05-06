<?php
session_start();

require_once __DIR__ . '/../config/db.php'; // Datenbankverbindung


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





$error = '';
$success = '';

// Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingaben (mit trim() um unnötige Leerzeichen zu entfernen)
    $currentPassword = trim($_POST['currentPassword'] ?? '');
    $newPassword     = trim($_POST['newPassword'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');

    // Basis-Validierungen
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "Bitte alle Felder ausfüllen.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Neues Passwort und Bestätigung stimmen nicht überein.";
    } else {
        // Aktuelles Passwort aus der Datenbank abrufen
        $stmt = $pdo->prepare("SELECT password FROM User WHERE userID = :userID");
        $stmt->execute(['userID' => $_SESSION['userID']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Benutzer nicht gefunden.";
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = "Aktuelles Passwort ist falsch.";
        } else {
            // Neues Passwort hashen und in der Datenbank aktualisieren
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE User SET password = :password WHERE userID = :userID");
            if ($stmt->execute(['password' => $newHash, 'userID' => $_SESSION['userID']])) {
                $success = "Passwort erfolgreich geändert.";
            } else {
                $error = "Fehler beim Aktualisieren des Passworts.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einstellungen - Passwort ändern</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/stylesdashb.css">
    <style>
        .settings-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="password"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>


    <!-- Sidebar Navigation -->
    <nav class="sidebar">

        <br>
    <h2>Hey, <?php echo htmlspecialchars($row["vorname"]); ?>!</h2>
    <ul class="sidebar-menu">
    <li><a href="/files/Do-IT/public/dashboard.php"><i class="fas fa-home"></i> <span>Startseite</span></a></li>        <li><a href="/files/Do-IT/private/dashboard/profile.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-user"></i> <span>Profil</span></a></li>
        <li><a href="/files/Do-IT/private/dashboard/family_members.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-users"></i> <span>Familienmitglieder</span></a></li>
    </ul>
    <ul class="sidebar-bottom">
        <li><a href="/files/Do-IT/private/dashboard/setup.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-cog"></i> <span>Einstellungen</span></a></li>
        <li><a href="../private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
    </ul>
</nav>

    <!-- Header mit Familienbild und Namen -->
    <header>
        <div class="family-info">
            <p> Familie <?php echo !empty($row['famName']) ? htmlspecialchars($row['famName']) : 'Noch keine Familie'; ?></p>
        </div>
    </header>

    <div class="settings-container">
        <h2>Passwort ändern</h2>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form action="setup.php" method="POST">
            <div class="form-group">
                <label for="currentPassword">Aktuelles Passwort:</label>
                <input type="password" name="currentPassword" id="currentPassword" required>
            </div>
            <div class="form-group">
                <label for="newPassword">Neues Passwort:</label>
                <input type="password" name="newPassword" id="newPassword" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Neues Passwort bestätigen:</label>
                <input type="password" name="confirmPassword" id="confirmPassword" required>
            </div>
            <button type="submit" class="btn">Passwort ändern</button>
        </form>
    </div>

</body>
</html>
