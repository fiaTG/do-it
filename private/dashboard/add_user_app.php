<?php
session_start();
header('Content-Type: application/json'); // Immer JSON zurückgeben

require'../config/db.php'; // Pfad zur DB-Verbindung

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

// Prüfen, ob die App bereits hinzugefügt wurde
$stmtCheck = $pdo->prepare("SELECT * FROM UserApps WHERE userID = ? AND appID = ?");
$stmtCheck->execute([$userID, $appID]);

if ($stmtCheck->rowCount() > 0) {
    echo json_encode(["status" => "error", "message" => "App bereits hinzugefügt"]);
    exit;
}

// Speichern der App
$stmt = $pdo->prepare("INSERT INTO UserApps (userID, appID) VALUES (?, ?)");
if ($stmt->execute([$userID, $appID])) {
    echo json_encode(["status" => "success", "message" => "App erfolgreich hinzugefügt"]);
} else {
    echo json_encode(["status" => "error", "message" => "Fehler beim Speichern"]);
}
?>