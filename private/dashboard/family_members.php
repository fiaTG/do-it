<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

$famID = $_GET['famID'] ?? null;
$userID = $_GET['userID'] ?? null;

// Abrufen der Daten des aktuellen Nutzers
$stmt = $pdo->prepare("
    SELECT vorname, nachname, email, profilbild, 
           facebook, instagram, linkedin, birthdate, gender, famID
    FROM User
    WHERE userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "<p style='color:red;'>Benutzer nicht gefunden oder keine Familieninformationen vorhanden.</p>";
    exit();
}

// Abrufen der Familienmitglieder
$stmt = $pdo->prepare("
    SELECT vorname, nachname, profilbild 
    FROM User
    WHERE famID = :famID AND userID != :userID
");
$stmt->execute(['famID' => $row['famID'], 'userID' => $_SESSION['userID']]);
$familyMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Familienmitglieder</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../public/stylesdashb.css">
  <style>
    body, html {
      overflow: visible;
      overflow-x: hidden !important;
      scrollbar-width: thin;
      scrollbar-color: var(--primary-color) var(--light-bg-apps);
    }
    /* Gemeinsamer Grid-Container für alle Profile */
    .profile-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 13%;
      margin-top: 20px;
    }
    /* Jede "Karte" im Grid – gleiche Breite und vertikale Anordnung */
    .profile-card {
      flex: 0 1 calc(33.33% - 20px); /* max. 3 Karten pro Reihe (abhängig von der Bildschirmbreite) */
      max-width: 150px;
      box-sizing: border-box;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* Name unter dem Bild */
    .profile-name {
      margin-top: 10px;
      margin-left: 50%;
      font-size: 16px;
      font-weight: bold;
      text-align: center;
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
  <li><a href="/files/Do-IT/private/dashboard/setup.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-cog"></i> <span>Einstellungen</span></a></li>
  <li><a href="../../private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
  </ul>
</nav>

<!-- Header mit Familieninfo -->
<header>
  <div class="family-info">
    <p><br>Familymembers von <?php echo !empty($row['vorname']) ? htmlspecialchars($row['vorname']) : 'Noch keine Familie'; ?></p>
  </div>
</header>

<!-- Gemeinsamer Grid-Container für alle Profile -->
<div class="profile-grid">
  <!-- Karte für den angemeldeten Nutzer -->
  <div class="profile-card">
    <div class="profile-pic">
      <?php
      if (!empty($row['profilbild'])) {
        $base64 = base64_encode($row['profilbild']);
        echo "<img src='data:image/jpeg;base64,{$base64}' alt='Profilbild'>";
      } else {
        echo "<img src='/files/Do-IT/public/img/defaultUserPic.png' alt='Profilbild'>";
      }
      ?>
    </div>
    <div class="profile-name">
      <p><strong><?php echo htmlspecialchars($row['vorname']) . ' ' . htmlspecialchars($row['nachname']); ?></strong></p>
    </div>
  </div>

  <!-- Karten für die Familienmitglieder -->
  <?php if ($familyMembers): ?>
    <?php foreach ($familyMembers as $member): ?>
      <div class="profile-card">
        <div class="profile-pic">
          <?php
          if (!empty($member['profilbild'])) {
            $base64 = base64_encode($member['profilbild']);
            echo "<img src='data:image/jpeg;base64,{$base64}' alt='Profilbild'>";
          } else {
            echo "<img src='/files/Do-IT/public/img/defaultUserPic.png' alt='Profilbild'>";
          }
          ?>
        </div>
        <div class="profile-name">
          <p><strong><?= htmlspecialchars($member['vorname'] . ' ' . $member['nachname']) ?></strong></p>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>Keine weiteren Familienmitglieder gefunden.</p>
  <?php endif; ?>
</div>

</body>
</html>
