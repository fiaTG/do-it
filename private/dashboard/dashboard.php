<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php'; // Datenbankverbindung sicherstellen
require_once __DIR__ . '/../../vendor/autoload.php'; // PHPMailer laden

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

// Daten des eingeloggten Nutzers inkl. Familiennamen abrufen
$stmt = $pdo->prepare("
    SELECT User.vorname, User.famID, Family.famName 
    FROM User 
    LEFT JOIN Family ON User.famID = Family.famID 
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="./../public/stylesdashb.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
    <h2><br>Hey, <?php echo htmlspecialchars($row["vorname"]); ?>!</h2>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Startseite</span></a></li>
        <li><a href="#"><i class="fas fa-user"></i> <span>Profil</span></a></li>
        <li><a href="#"><i class="fas fa-users"></i> <span>Familienmitglieder</span></a></li>
    </ul>
    <ul class="sidebar-bottom">
        <li><a href="#"><i class="fas fa-cog"></i> <span>Einstellungen</span></a></li>
        <li><a href="../private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
    </ul>
</nav>

    <!-- Header mit Familienbild und Namen -->
    <header class="dashboard-header">
       
        <div class="family-info">
            <p> Familie <?php echo !empty($row['famName']) ? htmlspecialchars($row['famName']) : 'Noch keine Familie'; ?></p>
        </div>
    </header>

    <main class="dashboard-container">
       

         <!-- Linke Spalte mit Apps -->
         <div class="apps-container">
            <button class="app"><i class=" fa-solid fa-square-plus"></i></button>
           
        </div>


        <div class="dashboard-rightSection">
        <!-- Aktivitäten Bereich -->
        <section class="activity-board">
            <h3>Neueste Familienaktivitäten</h3>
            <ul>
                <li>Max hat ein neues Rezept hinzugefügt.</li>
                <li>Lisa hat ein Familienfoto hochgeladen.</li>
                <li>Jonas hat eine Einkaufsliste erstellt.</li>
            </ul>
        </section>

            <!-- Kalender Widget -->
    <div class="calendar-widget">
        <h3>Familienkalender</h3>
        <iframe src="eigener Kalender Link" 
                style="border: 0" width="100%" height="300" frameborder="0" scrolling="no"></iframe>
    </div>

        <?php
// Prüfen, ob der User einer Familie angehört
if (!empty($row['famID'])) { 
    echo ' <div class="inviteButtonArea"><h3>Familienmitglieder einladen</h3>
          <form method="POST">
              <input type="email" name="inviteEmail" placeholder="E-Mail-Adresse" required>
              <button class="invite-button" type="submit" name="sendInvite">Einladen</button>
          </div></form>';
} else {
    echo '<h3>Erstelle eine neue Familie</h3>
          <form method="POST">
              <input type="text" name="famName" placeholder="Familienname" required>
              <button type="submit" name="createFamily">Familie erstellen</button>
          </form>';
}

// Familie erstellen
if (isset($_POST['createFamily'])) {
    $famName = trim($_POST['famName']);

    if (!empty($famName)) {
        $stmt = $pdo->prepare("INSERT INTO Family (famName) VALUES (:famName)");
        $stmt->execute(['famName' => $famName]);
        $famID = $pdo->lastInsertId();

        $stmt = $pdo->prepare("UPDATE User SET famID = :famID WHERE userID = :userID");
        $stmt->execute(['famID' => $famID, 'userID' => $_SESSION['userID']]);

        header("Location: dashboard.php");
        exit();
    } else {
        echo "<p style='color:red;'>Bitte einen Familiennamen eingeben!</p>";
    }
}

// Einladung senden mit PHPMailer
if (isset($_POST['sendInvite'])) {
    $inviteEmail = $_POST['inviteEmail'];
    $token = bin2hex(random_bytes(16)); 
    $famID = $row['famID'];

    // Einladung speichern
    $sql = "INSERT INTO Invites (famID, email, token) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$famID, $inviteEmail, $token]);

    // Mailer einrichten
    $mail = new PHPMailer(true);

    try {
        // SMTP Konfiguration
        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '1d41e7cdc90efd';
        $mail->Password = '85aa793cbea65d';
        $mail->SMTPSecure = ''; // Keine Verschlüsselung für Port 2525
        $mail->Port = 2525;

        // Absender & Empfänger
        $mail->setFrom('noreply@example.com', 'Familienportal');
        $mail->addAddress($inviteEmail);

        // E-Mail-Inhalt
        $mail->isHTML(true);
        $mail->Subject = "Familieneinladung";
        $inviteLink = "http://localhost/files/Do-IT/public/register_public.php?token=$token";

        $mail->Body = "<p>Du wurdest in die Familie eingeladen!<br>Registriere dich hier: <a href='$inviteLink'>$inviteLink</a></p>";

        $mail->send();
        echo "<p>Einladung gesendet an $inviteEmail!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Fehler beim Senden der E-Mail: {$mail->ErrorInfo}</p>";
    }
}
?>
</div>
 </main>
<!-- Modales Fenster für App-Auswahl -->
<div id="appModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Wähle deine Apps</h3>
        <br>
        <div class="modal-apps">
    <button class="add-app" data-app="Einkaufsliste" data-icon="fas fa-shopping-cart"> 
        <span class="app-title">Einkaufsliste</span>
        <i class="fas fa-shopping-cart"></i>
    </button>
    
    <button class="add-app" data-app="Bildergalerie" data-icon="fas fa-images"> 
        <span class="app-title">Bildergalerie</span>
        <i class="fas fa-images"></i>
    </button>

    <button class="add-app" data-app="To-Do Liste" data-icon="fas fa-list-check"> 
        <span class="app-title">To-Do Liste</span>
        <i class="fas fa-list-check"></i>
    </button>

    <button class="add-app" data-app="Kalender" data-icon="fas fa-calendar-alt"> 
        <span class="app-title">Kalender</span>
        <i class="fas fa-calendar-alt"></i>
    </button>
</div>


    </div>
</div>

 <script src="..\public\js\dashboard.js" defer></script>

</body>
</html>
