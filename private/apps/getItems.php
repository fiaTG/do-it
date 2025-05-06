<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    echo json_encode(['error' => 'Nicht eingeloggt']);
    exit();
}

// Holt die famID des Benutzers
$stmt = $pdo->prepare("SELECT famID FROM User WHERE userID = :userID");
$stmt->execute(['userID' => $_SESSION['userID']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Wenn die `famID` nicht vorhanden ist, gib einen Fehler zurück
if (!$user) {
    echo json_encode(['error' => 'Benutzer nicht gefunden']);
    exit();
}

$famID = $user['famID'];

// Einkaufsartikel abrufen, die zur gleichen Familie gehören
$shopItemsStmt = $pdo->prepare("
    SELECT si.shopItemsID, si.itemName, si.menge, s.shopname, ui.shopID, ui.userID as creatorID
    FROM ShopItems si
    JOIN UserItems ui ON si.shopItemsID = ui.shopitemsID
    JOIN Shop s ON ui.shopID = s.shopID
    JOIN User u ON ui.userID = u.userID
    WHERE u.famID = :famID
");
$shopItemsStmt->execute(['famID' => $famID]);
$shopItems = $shopItemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Gebe die Artikel als JSON zurück
echo json_encode($shopItems);
?>
