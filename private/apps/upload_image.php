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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
        $image = $_FILES['image'];

        if ($image['error'] !== UPLOAD_ERR_OK) {
            header('Content-Type: application/json');
            die(json_encode(["status" => "error", "message" => "Fehler beim Hochladen: Code " . $image['error']]));
        }

        if ($image['size'] > 10 * 1024 * 1024) {
            header('Content-Type: application/json');
            die(json_encode(["status" => "error", "message" => "Bild zu groß! Max: 10 MB"]));
        }

        // Sicherstellen, dass der MIME-Typ gültig ist
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $image['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            header('Content-Type: application/json');
            die(json_encode(["status" => "error", "message" => "Ungültiger Dateityp! Erlaubt: JPG, PNG, GIF"]));
        }

        // Bildinhalt aus Datei lesen
        $imageData = file_get_contents($image['tmp_name']);
        $titel = isset($_POST['titel']) ? $_POST['titel'] : 'Kein Titel';

        // Bild in die Datenbank einfügen
        $stmt = $pdo->prepare("INSERT INTO Bilder (titel, bild, famID, userID) VALUES (:titel, :bild, :famID, :userID)");
        $stmt->bindParam(':titel', $titel);
        $stmt->bindParam(':bild', $imageData, PDO::PARAM_LOB);
        $stmt->bindParam(':famID', $famID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
       
        echo "<div style='
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #d4edda;
        color: #155724;
        padding: 20px;
        border-radius: 10px;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    '>
    ✅ Bild erfolgreich hochgeladen! <br> Du wirst weitergeleitet...
</div>";

echo "<script>
    setTimeout(() => { window.location.href = 'gallery.php'; }, 2000);
</script>";

exit();
    } else {
        header('Content-Type: application/json');
        die(json_encode(["status" => "error", "message" => "Kein Bild hochgeladen"]));
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(["status" => "error", "message" => "Datenbankfehler: " . $e->getMessage()]));
}
?>
