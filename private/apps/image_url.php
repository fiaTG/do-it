<?php
require_once __DIR__ . '/../config/db.php';

// Bild-URL auf Basis der Bild-ID zurückgeben
if (isset($_GET['id'])) {
    $bildID = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT bild FROM Bilder WHERE bilderID = :id");
    $stmt->bindParam(':id', $bildID);
    $stmt->execute();
    $bild = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($bild) {
        // Bild als Base64 zurückgeben
        echo base64_encode($bild['bild']);
    } else {
        echo json_encode(["status" => "error", "message" => "Bild nicht gefunden"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Keine Bild-ID angegeben"]);
}


?>

<!-- Bild-URL zurückgeben:

Der Code überprüft, ob die Bild-ID (id) in der URL vorhanden ist.
Dann holt er sich mit dieser ID das Bild aus der Datenbank (aus der Tabelle Bilder).
Das Bild wird als BLOB gespeichert, daher wird der bild-Wert als binäre Daten aus der Datenbank
abgerufen.

Konvertierung in Base64:

Die binären Daten des Bildes werden mit der PHP-Funktion base64_encode() in das Base64-Format
umgewandelt.
Base64 ist ein Textformat, das binäre Daten in ASCII-Zeichen umwandelt. Es wird häufig verwendet,
um binäre Daten in einem textbasierten Format zu übertragen, z. B. in HTML-Dokumenten. -->