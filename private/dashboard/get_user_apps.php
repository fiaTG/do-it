<?php
session_start();
header('Content-Type: application/json'); // Sicherstellen, dass die Antwort im JSON-Format erfolgt

require '../config/db.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Nicht autorisiert"]);
    exit;
}

$userID = $_SESSION['userID'];

// Apps des Benutzers aus der Datenbank abrufen
$stmt = $pdo->prepare("
      SELECT App.appID, App.appName, App.appIcon, App.appPfad
    FROM UserApps 
    INNER JOIN App ON UserApps.appID = App.appID
    WHERE UserApps.userID = ?
");

$stmt->execute([$userID]);

$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Wenn der Benutzer Apps hat, gebe sie zurück
if ($apps) {
    echo json_encode(["status" => "success", "apps" => $apps]);
} else {
    echo json_encode(["status" => "error", "message" => "Keine Apps gefunden"]);
}
?>
