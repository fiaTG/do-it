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
        SELECT eventID AS id, title, startDate AS start, endDate AS end, carReserved, category 
        FROM Events 
        WHERE famID = ?
    ");
    $stmt->execute([$famID]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Überprüfen, ob carReserved korrekt gesetzt ist
    foreach ($events as &$event) {
        // carReserved in extendedProps einfügen
        $event['extendedProps'] = [
            'carReserved' => $event['carReserved'],
            'category' => $event['category']
        ];
        unset($event['carReserved']);  // carReserved aus der Hauptantwort entfernen
    }
    echo json_encode($events);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Neues Event erstellen
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['title'], $data['start'], $data['end'], $data['category'])) {
        http_response_code(400);
        echo json_encode(["error" => "Fehlende Parameter"]);
        exit();
    }


$carReserved = isset($data['carReserved']) ? $data['carReserved'] : 0;  // Hier wird carReserved nur gesetzt, wenn es explizit übergeben wird.

$stmt = $pdo->prepare("INSERT INTO Events (title, startDate, endDate, userID, famID, carReserved, category) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $data['title'],
    $data['start'],
    $data['end'],
    $userID,
    $famID,
    $carReserved,  // carReserved wird hier korrekt übergeben
    $data['category'] ?? 'Sonstiges' // Kategorie hier übergeben
]);

    echo json_encode(["success" => true]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Event aktualisieren
    $data = json_decode(file_get_contents("php://input"), true);

    // Überprüfen, ob die erforderlichen Daten vorhanden sind
    if (!isset($data['eventID'], $data['title'], $data['start'], $data['end'], $data['category'])) {
        http_response_code(400);
        echo json_encode(["error" => "Fehlende Parameter"]);
        exit();
    }

    $carReserved = isset($data['carReserved']) ? $data['carReserved'] : 0;

    // Event aktualisieren (einschließlich Kategorie)
    $stmt = $pdo->prepare("
        UPDATE Events 
        SET title = ?, startDate = ?, endDate = ?, carReserved = ?, category = ?
        WHERE eventID = ?
    ");
    $stmt->execute([
        $data['title'],
        $data['start'],
        $data['end'],
        $carReserved,  
        $data['category'],  // Hier die Kategorie einfügen
        $data['eventID']
    ]);

    // Erfolgsmeldung zurückgeben
    echo json_encode(["success" => true]);
    exit();
}

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

    echo json_encode(["success" => true]);
    exit();
}
?>
