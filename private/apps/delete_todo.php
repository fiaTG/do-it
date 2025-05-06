<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php'; // Verbindung zur Datenbank sicherstellen

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

// Prüfen, ob eine ToDo-ID übergeben wurde
if (isset($_GET['todo_id']) && is_numeric($_GET['todo_id'])) {
    $todo_id = intval($_GET['todo_id']);

    // Prüfen, ob der Nutzer die Aufgabe löschen darf
    $stmt = $pdo->prepare("
        SELECT UserToDo.toDoID 
        FROM UserToDo
        JOIN User ON UserToDo.userID = User.userID
        WHERE UserToDo.toDoID = :todo_id AND User.famID = :famID
    ");
    $stmt->execute([
        'todo_id' => $todo_id,
        'famID'   => $_SESSION['famID']
    ]);

    if ($stmt->fetch()) {
        // Löschen der Aufgabe aus `UserToDo`
        $stmt = $pdo->prepare("DELETE FROM UserToDo WHERE toDoID = :todo_id");
        $stmt->execute(['todo_id' => $todo_id]);

        // Löschen der Aufgabe aus `ToDo`
        $stmt = $pdo->prepare("DELETE FROM ToDo WHERE toDoID = :todo_id");
        $stmt->execute(['todo_id' => $todo_id]);
    }
}

// Zurück zur ToDo-Liste
header('Location: /files/Do-IT/private/apps/toDoList.php');
exit();
