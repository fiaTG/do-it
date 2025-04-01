<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Wenn Composer verwendet wird


// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

// Abrufen der Benutzerinformationen einschließlich Familienname
$stmt = $pdo->prepare("
    SELECT User.vorname, User.famID, Family.famName 
    FROM User 
    LEFT JOIN Family ON User.famID = Family.famID 
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Wenn der Benutzer nicht gefunden wird
if (!$row) {
    echo "Benutzer nicht gefunden.";
    exit();
}

$vorname = $row['vorname']; // Speichert den Vornamen des Benutzers
$famName = $row['famName'] ?? 'Noch keine Familie'; // Wenn der Familienname nicht gesetzt ist, wird 'Noch keine Familie' verwendet
$famID = $row['famID']; // Speichert die Familien-ID des Benutzers

// Einkaufsartikel der Familie abrufen
$stmt = $pdo->prepare("
    SELECT si.itemName, si.menge, s.shopname
    FROM ShopItems si
    JOIN UserItems ui ON si.shopItemsID = ui.shopitemsID
    JOIN Shop s ON ui.shopID = s.shopID
    JOIN User u ON ui.userID = u.userID
    WHERE u.famID = :famID
");
$stmt->execute(['famID' => $famID]); // Hier verwenden wir die famID aus der Session
$shopItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Wenn keine Artikel vorhanden sind
if (empty($shopItems)) {
    echo "Keine Einkaufsartikel gefunden.";
    exit();
}


// PDF erstellen
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);  // Titel etwas größer
$pdf->Cell(0, 10, 'Einkaufsliste fuer Familie: ' . $famName, 0, 1, 'C');
$pdf->Ln(5);  // Zeilenumbruch nach dem Titel

$pdf->SetFont('Arial', '', 12);

// Artikel nach Shop gruppieren
$shopGroupedItems = [];

foreach ($shopItems as $item) {
    $shopGroupedItems[$item['shopname']][] = $item;
}

// Tabellen für jeden Shop erstellen
foreach ($shopGroupedItems as $shopName => $items) {
    // Shopname als Überschrift
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Shop: ' . $shopName, 0, 1, 'L');
    $pdf->Ln(3); // Zeilenumbruch für Abstand

    // Tabelle mit Überschriften für den aktuellen Shop
    $pdf->SetFillColor(200, 220, 255); // Hintergrundfarbe für die Überschrift
    $pdf->Cell(20, 10, 'Menge', 1, 0, 'C', true);
    $pdf->Cell(100, 10, 'Artikel', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Shop', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Abhaken', 1, 1, 'C', true); // Extra Zelle für das Kästchen

    // Artikelliste für den aktuellen Shop hinzufügen
    foreach ($items as $item) {
        $pdf->Cell(20, 10, $item['menge'], 1, 0, 'C'); // Menge des Artikels
        $pdf->Cell(100, 10, $item['itemName'], 1, 0, 'L'); // Artikelname
        $pdf->Cell(50, 10, $item['shopname'], 1, 0, 'L'); // Shopname
        $pdf->Cell(20, 10, '[]', 1, 1, 'C'); // Leeres Kästchen zum Abhaken
    }

    $pdf->Ln(10); // Abstand zwischen den Tabellen der Shops
}

// PDF ausgeben (Download)
$pdf->Output('D', 'Einkaufsliste.pdf');
?>