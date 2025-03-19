<?php
require_once "../private/config/db.php"; // Datenbankverbindung einbinden

// Testabfrage
try {
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "Erfolgreich mit der Datenbank verbunden: " . $dbName;
} catch (PDOException $e) {
    echo "Fehler: " . $e->getMessage();
}
?>
