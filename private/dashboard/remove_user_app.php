<?php
session_start();
header('Content-Type: application/json'); // Sicherstellen, dass JSON zurückgegeben wird

require '../config/db.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Nicht autorisiert"]);
    exit;
}

$userID = $_SESSION['userID'];
$appID = $_POST['appID'] ?? null;

if (!$appID) {
    echo json_encode(["status" => "error", "message" => "Keine App-ID angegeben"]);
    exit;
}

// Prüfen, ob die App existiert
$stmtCheck = $pdo->prepare("SELECT * FROM UserApps WHERE userID = ? AND appID = ?");
$stmtCheck->execute([$userID, $appID]);

if ($stmtCheck->rowCount() === 0) {
    echo json_encode(["status" => "error", "message" => "App nicht gefunden"]);
    exit;
}

// App aus der Datenbank entfernen
$stmt = $pdo->prepare("DELETE FROM UserApps WHERE userID = ? AND appID = ?");
if ($stmt->execute([$userID, $appID])) {
    echo json_encode(["status" => "success", "message" => "App erfolgreich entfernt"]);
} else {
    echo json_encode(["status" => "error", "message" => "Fehler beim Entfernen"]);
}
?>

