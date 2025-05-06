<?php
session_start();
error_log("Session Inhalt: " . print_r($_SESSION, true));

require_once __DIR__ . '/../config/db.php'; // Datenbankverbindung sicherstellen

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

// Benutzerinformationen abrufen
$stmt = $pdo->prepare("
    SELECT User.vorname, User.famID, Family.famName 
    FROM User 
    LEFT JOIN Family ON User.famID = Family.famID 
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// `famID` aus der Datenbank setzen, falls noch nicht in der Session
if (!isset($_SESSION['famID']) && isset($row['famID'])) {
    $_SESSION['famID'] = $row['famID'];
}

if (!isset($_SESSION['famID'])) {
    header('Content-Type: application/json');
    die(json_encode(["status" => "error", "message" => "Fehler: Keine Familien-ID vorhanden!"]));
}

$userID = $_SESSION['userID'];
$famID = $_SESSION['famID'];

try {
  // Prüfen, ob ein Bild per POST hochgeladen wurde
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
        $image = $_FILES['image'];

 // Fehler beim Hochladen prüfen
        if ($image['error'] !== UPLOAD_ERR_OK) {
            header('Content-Type: application/json');
            die(json_encode(["status" => "error", "message" => "Fehler beim Hochladen: Code " . $image['error']]));
        }

           // Maximale Dateigröße von 10 MB prüfen
        if ($image['size'] > 10 * 1024 * 1024) {
            header('Content-Type: application/json');
            die(json_encode(["status" => "error", "message" => "Bild zu groß! Max: 10 MB"]));
        }

         // MIME-Typ ermitteln und prüfen, ob es sich um ein erlaubtes Bildformat handelt
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $image['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            header('Content-Type: application/json');
            die(json_encode(["status" => "error", "message" => "Ungültiger Dateityp! Erlaubt: JPG, PNG, GIF"]));
        }

  // Bildinhalt aus der temporären Datei lesen
        $imageData = file_get_contents($image['tmp_name']);
          // Titel aus dem Formular auslesen, Standardwert falls nicht vorhanden
        $titel = isset($_POST['titel']) ? $_POST['titel'] : 'Kein Titel';

      // Bild zusammen mit Metadaten in die Datenbank schreiben
        $stmt = $pdo->prepare("INSERT INTO Bilder (titel, bild, famID, userID) VALUES (:titel, :bild, :famID, :userID)");
        $stmt->bindParam(':titel', $titel);
        $stmt->bindParam(':bild', $imageData, PDO::PARAM_LOB);
        $stmt->bindParam(':famID', $famID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
       
        echo "<style>

        
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #406f8f, #968d86);
            font-family: 'Syncopate', sans-serif;
            color: #fdfbf2;
        }
        @keyframes fadeIn {
          from { opacity: 0; transform: translate(-50%, -60%); }
          to { opacity: 1; transform: translate(-50%, -50%); }
        }
        
        @keyframes loadBar {
          from { width: 0%; }
          to { width: 100%; }
        }
        
        .fadeInBox {
          animation: fadeIn 0.8s ease-in-out;
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
        </style>";
        
        echo "<div class='fadeInBox' style='
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
        '>
          <span style='font-size: 30px;'>✔️</span><br>
          Bild erfolgreich hochgeladen!
          
          <div class='progress-container'>
            <div class='progress-bar'></div>
          </div>
        </div>";
        
     // Automatische Weiterleitung zurück zur Galerie nach 2 Sekunden
echo "<script>
    setTimeout(() => { window.location.href = 'gallery.php'; }, 2000);
</script>";

// Wichtig: Ausstieg, um weiteren Code nicht mehr auszuführen
exit();

    } else {
        header('Content-Type: application/json');
        die(json_encode(["status" => "error", "message" => "Kein Bild hochgeladen"]));
    }
        // Fehlerbehandlung bei Datenbankfehlern
} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(["status" => "error", "message" => "Datenbankfehler: " . $e->getMessage()]));
}
?>