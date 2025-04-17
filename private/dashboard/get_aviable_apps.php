<?php
session_start();
// Gibt an, dass die Rückgabe JSON ist – wichtig für Frontend-Requests (AJAX etc.)
header('Content-Type: application/json'); 

require'../config/db.php';

if(!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Nicht autorisiert"]);
    exit;
}

$userID = $_SESSION['userID'];

// SQL-Abfrage: Alle Apps, die der Benutzer *noch nicht* mit seinem Account verknüpft hat
$stmt = $pdo->prepare("
    SELECT * FROM App
    WHERE appID NOT IN (SELECT appID FROM UserApps WHERE userID = ?)
");

// Abfrage ausführen mit der userID
$stmt->execute([$userID]);

$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Wenn verfügbare Apps vorhanden sind, zurückgeben
if ($apps) {
    echo json_encode(["status" => "success", "apps" => $apps]);
} else {
     // Keine neuen Apps mehr verfügbar
    echo json_encode(["status" => "error", "message" => "Keine verfügbaren Apps"]);
}
exit; // Stoppt unerwartete Ausgabe
?>
