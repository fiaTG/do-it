<?php
session_start(); // Session starten, um auf die Session-Variablen zugreifen zu können
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/db.php'; // Datenbankverbindung sicherstellen

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}
// `famID` aus der Datenbank setzen, falls noch nicht in der Session
if (!isset($_SESSION['famID']) && isset($row['famID'])) {
    $_SESSION['famID'] = $row['famID'];
}

var_dump($_SESSION['famID']);

// Überprüfen, ob die todo_id gesetzt ist
if (isset($_GET['todo_id'])) {
    $todo_id = $_GET['todo_id'];

    // Überprüfen, ob die Aufgabe existiert und den aktuellen Status holen
    $stmt = $pdo->prepare("SELECT ischecked FROM ToDo WHERE toDoID = ?");
    $stmt->execute([$todo_id]);
    $todo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($todo) {
        // Status umkehren (wenn 1 dann auf 0 setzen, wenn 0 dann auf 1 setzen)
        $newStatus = $todo['ischecked'] == 1 ? 0 : 1;

        // Status in der Datenbank aktualisieren
        $updateStmt = $pdo->prepare("UPDATE ToDo SET ischecked = ? WHERE toDoID = ?");
        $updateStmt->execute([$newStatus, $todo_id]);
    }
}

// Nach dem Update zurück zur ToDo-Liste oder ein anderes Ziel
header('Location: toDoList.php');
exit();
?>
