<?php
// Stellen Sie sicher, dass keine Ausgabe (z.B. Leerzeilen oder Debug-Statements) vor diesem Tag vorhanden ist!

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json'); // Erzwinge JSON-Format

session_start();
if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit();
}

require_once __DIR__ . '/../config/db.php'; // Datenbankverbindung sicherstellen

$userID = $_SESSION['userID'];

// FamID des Nutzers holen
$stmt = $pdo->prepare("SELECT famID FROM User WHERE userID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$famID = $user['famID'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("
        SELECT eventID AS id, title, startDate AS start, endDate AS end, carReserved 
        FROM Events 
        WHERE famID = ?
    ");
    $stmt->execute([$famID]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($events);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Neues Event erstellen
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['title'], $data['start'], $data['end'])) {
        http_response_code(400);
        echo json_encode(["error" => "Fehlende Parameter"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO Events (title, startDate, endDate, userID, famID, carReserved) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['title'],
        $data['start'],
        $data['end'],
        $userID,
        $famID,
        $data['carReserved'] ?? 0
    ]);

    echo json_encode(["success" => true]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT eventID, title, startDate AS start, endDate AS end, carReserved 
    FROM Events 
    WHERE famID = ?
");
$stmt->execute([$famID]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($events);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Event löschen (nur Ersteller oder Admin)
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['eventID'])) {
        http_response_code(400);
        echo json_encode(["error" => "Fehlende Event-ID"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM Events WHERE eventID = ? AND (userID = ? OR ? IN (SELECT userID FROM User WHERE famID = ?))");
    $stmt->execute([
        $data['eventID'],
        $userID,
        $userID,
        $famID
    ]);
    ob_clean(); // Vorherigen Output entfernen
    echo json_encode(["success" => true]);
    exit();
}
