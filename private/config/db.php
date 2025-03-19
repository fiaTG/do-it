<?php
// Verbindungseinstellungen
$host = "localhost";   // Server (bei XAMPP/MAMP: "localhost")
$dbname = "FamilyBoard";  // Name der Datenbank
$username = "root";    // Standardnutzer (bei XAMPP/MAMP meist "root")
$password = "";        // Standardpasswort (bei XAMPP/MAMP leer)

// Verbindung herstellen
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Fehler bei der Datenbankverbindung: " . $e->getMessage());
}
?>
