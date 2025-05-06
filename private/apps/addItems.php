<?php
session_start();

// addItem.php - Fügt Artikel zur Datenbank hinzu
require_once __DIR__ . '/../config/db.php';

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

// Überprüfen, ob die Anfrage POST ist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = $_POST['itemName'] ?? '';  // Artikelname
    $itemQuantity = $_POST['itemQuantity'] ?? 1;  // Menge
    $itemStore = $_POST['itemStore'] ?? '';  // ShopID (Aldi, Lidl, Rewe)
    $userID = $_SESSION['userID'];  // NutzerID aus der Session

    // Überprüfen, ob alle benötigten Daten vorhanden sind
    if (!empty($itemName) && !empty($itemStore) && !empty($userID)) {
        // 1. Schritt: Artikel in die ShopItems-Tabelle einfügen oder Menge aktualisieren
        $stmt = $pdo->prepare("INSERT INTO ShopItems (itemName, menge) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE menge = menge + ?");
        $stmt->execute([$itemName, $itemQuantity, $itemQuantity]);

        // Holt die ID des zuletzt eingefügten oder aktualisierten Artikels
        $shopItemID = $pdo->lastInsertId();

        // 2. Schritt: Artikel für den Benutzer und den Shop in die UserItems-Tabelle einfügen
        // Hier nehmen wir an, dass "itemStore" den ShopID-Wert enthält (z. B. 1 für Aldi)
        $stmt = $pdo->prepare("INSERT IGNORE INTO UserItems (userID, shopitemsID, shopID) 
                               VALUES (?, ?, ?)");
        $stmt->execute([$userID, $shopItemID, $itemStore]);

        // Erfolgsmeldung in die Session setzen
        $_SESSION['success_message'] = 'Artikel erfolgreich hinzugefügt!';

        // Weiterleitung zur shoppingList.php
        header("Location: shoppingList.php");
        exit();  // Beendet das Skript, um sicherzustellen, dass keine weitere Ausgabe erfolgt
    } else {
        // Fehlende Daten
        echo json_encode(['success' => false, 'error' => 'Fehlende Daten']);
    }
} else {
    // Ungültige Anfrage
    echo json_encode(['success' => false, 'error' => 'Ungültige Anfrage']);
}
?>
