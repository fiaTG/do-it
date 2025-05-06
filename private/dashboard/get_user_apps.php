<?php
session_start();
header ('Content-Type: application/json'); // Sicherstellen, dass die Antwort im JSON-Format erfolgt

require '../config/db.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Nicht autorisiert"]);
    exit;
}

$userID = $_SESSION['userID'];

// SQL: Verknüpfte Apps für diesen Benutzer abrufen
// Die UserApps-Tabelle enthält die Beziehung zwischen Benutzern und ihren aktiven Apps
//„Gib mir die ID, den Namen, das Icon und den Pfad jeder App, die mit der Benutzer-ID aus der UserApps-Tabelle verknüpft ist, 
// indem du die passenden App-Daten aus der App-Tabelle über einen INNER JOIN holst.
$stmt = $pdo->prepare("
      SELECT App.appID, App.appName, App.appIcon, App.appPfad
    FROM UserApps 
    INNER JOIN App ON UserApps.appID = App.appID
    WHERE UserApps.userID = ?
");
// Statement mit der Benutzer-ID ausführen
$stmt->execute([$userID]);



/* Ergebnis als assoziatives Array holen
Was ist ein „assoziatives Array“?

Ein normales Array sieht so aus: ["Apfel", "Birne", "Banane"]
Ein assoziatives Array verwendet Schlüssel-Wert-Paare, also z. B.:
[
  "appName" => "ToDo Liste",
  "appIcon" => "fas fa-list-check",
  "appPfad" => "todo.php"
]

bei mehreren Apps sieht das dann so aus 
[
  [
    "appID" => 1,
    "appName" => "ToDo Liste",
    "appIcon" => "fas fa-list-check",
    "appPfad" => "todo.php"
  ],
  [
    "appID" => 2,
    "appName" => "Kalender",
    "appIcon" => "fas fa-calendar-alt",
    "appPfad" => "kalender.php"
  ],
  // ... usw.
]
*/
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Wenn der Benutzer Apps hat, gebe sie zurück
if ($apps) {
    echo json_encode(["status" => "success", "apps" => $apps]);
} else {
    echo json_encode(["status" => "error", "message" => "Keine Apps gefunden"]);
}
?>
