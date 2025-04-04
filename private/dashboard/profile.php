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
    SELECT User.vorname, User.nachname, User.email, User.famID, Family.famName 
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
    <title>Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/stylesdashb.css">
    <style>
        /* Profil Container */
.profile-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 80%;
    margin: 40px auto;
    max-width: 1000px;
}

/* Profil Bild */
.profile-pic {
    margin-bottom: 20px;
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.profile-pic img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-pic form {
    position: absolute;
    bottom: 10px;
    right: 10px;
}

.profile-pic input[type="file"] {
    display: none;
}

.profile-pic button {
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 50%;
    padding: 6px;
    cursor: pointer;
    font-size: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.profile-pic button:hover {
    background-color: #2980b9;
}

/* Profil Info Styling */
.profile-info {
    text-align: center;
    margin-top: 20px;
}

.profile-info p {
    font-size: 16px;
    line-height: 1.5;
    margin: 8px 0;
}

.profile-info strong {
    color: #333;
    font-weight: bold;
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
        <li><a href="../private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
    </ul>
</nav>

    <!-- Header mit Familienbild und Namen -->
    <header>
        <div class="family-info">
            <p> Profil von <?php echo !empty($row['vorname']) ? htmlspecialchars($row['vorname']) : 'Noch keine Familie'; ?></p>
        </div>
    </header>
    <div class="profile-container">
    <div class="profile-pic">
        <img src="/files/Do-IT/public/img/defaultUserPic.png" alt="Profilbild"style="width: 150px; height: 150px;">
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <input type="file" name="profilbild" accept="image/*">
            <button type="submit">Ändern</button>
        </form>
    </div>
    <div class="profile-info">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($row['vorname'] . ' ' . $row['nachname']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
    </div>
</div>

</body>
</html>
