<?php
error_log("Raw JSON: " . file_get_contents('php://input'));
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Daten aus dem POST-Body abrufen
    $data = json_decode(file_get_contents('php://input'), true);

    // Überprüfen, ob die erforderlichen Parameter gesetzt sind
    if (!isset($data['userID']) || !isset($data['itemName']) || !isset($data['shopID'])) {
        echo json_encode(['error' => 'Ungültige Eingabe']);
        http_response_code(400); // Bad Request
        exit();
    }

    // Daten aus der Anfrage
    $userID = intval($data['userID']);
    $itemName = trim($data['itemName']);  // Entfernen von Leerzeichen vor/nach dem Artikelname
    $shopID = intval($data['shopID']);

    // Sicherstellen, dass der Artikelname nicht leer ist
    if (empty($itemName)) {
        echo json_encode(['error' => 'Artikelname darf nicht leer sein']);
        http_response_code(400); // Bad Request
        exit();
    }

    try {
        // Hole die shopItemsID des Artikels
        $stmt = $pdo->prepare("SELECT shopItemsID FROM ShopItems WHERE itemName = :itemName");
        $stmt->execute(['itemName' => $itemName]);
        $shopItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$shopItem) {
            echo json_encode(['error' => 'Artikel nicht gefunden']);
            http_response_code(404); // Not Found
            exit();
        }

        $shopItemsID = $shopItem['shopItemsID'];

        // 1. Löschen des Artikels aus UserItems
        $stmt = $pdo->prepare("DELETE FROM UserItems WHERE userID = :userID AND shopitemsID = :shopItemsID AND shopID = :shopID");
        $stmt->execute(['userID' => $userID, 'shopItemsID' => $shopItemsID, 'shopID' => $shopID]);

        // 2. Prüfen, ob der Artikel erfolgreich gelöscht wurde
        if ($stmt->rowCount() > 0) {
            // Optional: Löschen des Artikels aus der ShopItems-Tabelle, falls er nicht mehr verwendet wird
            $stmt = $pdo->prepare("DELETE FROM ShopItems WHERE shopItemsID = :shopItemsID AND NOT EXISTS (SELECT 1 FROM UserItems WHERE shopItemsID = :shopItemsID)");
            $stmt->execute(['shopItemsID' => $shopItemsID]);

            echo json_encode(['success' => 'Artikel gelöscht']);
            http_response_code(200); // OK
        } else {
            echo json_encode(['error' => 'Artikel nicht gefunden in der UserItems-Tabelle']);
            http_response_code(404); // Not Found
        }
    } catch (PDOException $e) {
        // Fehlerbehandlung
        echo json_encode(['error' => 'Fehler: ' . $e->getMessage()]);
        http_response_code(500); // Internal Server Error
    }
} else {
    echo json_encode(['error' => 'Ungültige Anfrage']);
    http_response_code(405); // Method Not Allowed
}
error_log(print_r($data, true));
?>
