<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task'])) {
    $task = $_POST['task'];

    // Eintrag in die ToDo-Tabelle einfügen
    $stmt = $pdo->prepare("INSERT INTO ToDo (toDoName) VALUES (:task)");
    $stmt->execute(['task' => $task]);

    // Die neu eingefügte ToDo-ID abrufen
    $toDoID = $pdo->lastInsertId();

    // Beziehung in der UserToDo-Tabelle hinzufügen
    $stmt = $pdo->prepare("INSERT INTO UserToDo (userID, toDoID) VALUES (:userID, :toDoID)");
    $stmt->execute(['userID' => $_SESSION['userID'], 'toDoID' => $toDoID]);

    // Weiterleitung zur ToDo-Seite
    header('Location: /files/Do-IT/private/apps/toDoList.php');
    exit();
}
?>
