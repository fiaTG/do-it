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
