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

// Prüfen, ob der Nutzer diese App schon hinzugefügt hat
$stmtCheck = $pdo->prepare("SELECT * FROM UserApps WHERE userID = ? AND appID = ?");
$stmtCheck->execute([$userID, $appID]);

// Falls schon vorhanden → Fehlermeldung
if ($stmtCheck->rowCount() > 0) {
    echo json_encode(["status" => "error", "message" => "App bereits hinzugefügt"]);
    exit;
}


// App mit dem Nutzer verknüpfen (in die Tabelle einfügen)
$stmt = $pdo->prepare("INSERT INTO UserApps (userID, appID) VALUES (?, ?)");
// Erfolg oder Fehler beim Einfügen zurückgeben
if ($stmt->execute([$userID, $appID])) {
    echo json_encode(["status" => "success", "message" => "App erfolgreich hinzugefügt"]);
} else {
    echo json_encode(["status" => "error", "message" => "Fehler beim Speichern"]);
}
?>