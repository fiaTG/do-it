<?php
session_start();
header('Content-Type: application/json'); // Sicherstellen, dass die Antwort im JSON-Format erfolgt

require'../config/db.php';

if(!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Nicht autorisiert"]);
    exit;
}

$userID = $_SESSION['userID'];

// Abrufen der Apps, die noch nicht mit dem Benutzer verknüpft sind
$stmt = $pdo->prepare("
    SELECT * FROM App
    WHERE appID NOT IN (SELECT appID FROM UserApps WHERE userID = ?)
");

$stmt->execute([$userID]);

$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Wenn verfügbare Apps vorhanden sind, zurückgeben
if ($apps) {
    echo json_encode(["status" => "success", "apps" => $apps]);
} else {
    echo json_encode(["status" => "error", "message" => "Keine verfügbaren Apps"]);
}
exit; // Stoppt unerwartete Ausgabe
?>
