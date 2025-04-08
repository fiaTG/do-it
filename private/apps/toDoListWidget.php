<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php'; // Datenbankverbindung sicherstellen

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

// `famID` aus der Datenbank setzen, falls noch nicht in der Session
if (!isset($_SESSION['famID'])) {
    $stmt = $pdo->prepare("SELECT famID FROM User WHERE userID = :userID");
    $stmt->execute(['userID' => $_SESSION['userID']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['famID'] = $row['famID'] ?? null;
}

// Benutzerinformationen abrufen
$stmt = $pdo->prepare("
    SELECT User.vorname, Family.famName 
    FROM User 
    LEFT JOIN Family ON User.famID = Family.famID 
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// ToDo-Liste abrufen
$stmt = $pdo->prepare("
    SELECT ToDo.toDoID, ToDo.toDoName, ToDo.ischecked, User.vorname AS creator, UserToDo.userID AS assignedID
    FROM ToDo
    JOIN UserToDo ON ToDo.toDoID = UserToDo.toDoID
    JOIN User ON UserToDo.userID = User.userID
    WHERE User.famID = :famID
    ORDER BY ToDo.toDoID DESC
");
$stmt->execute(['famID' => $_SESSION['famID']]);
$todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Funktion zur Namensanzeige des zugewiesenen Benutzers
function getAssignedUserName($pdo, $assignedID) {
    $stmt = $pdo->prepare("SELECT vorname, nachname FROM User WHERE userID = :userID");
    $stmt->execute(['userID' => $assignedID]);
    $assignedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    return $assignedUser ? htmlspecialchars($assignedUser['vorname'] . ' ' . $assignedUser['nachname']) : 'Unbekannt';
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo Liste</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/stylesdashb.css">
</head>

<body>



<!-- Container für die ToDo-Liste und das Formular -->
<section class="todo-list-wrapper" style="margin: 1% 10% 5% 10%; margin-left: 20%;">
    
    <!-- ToDo Liste -->
    <section class="todo-list-container" style="background-color: white; padding: 20px; border-radius: 10px; box-shadow: var(--box-shadow);">
        <h3 style="font-size: 20px; font-weight: bold; color: #333; margin-bottom: 20px; text-align: center;">Deine ToDo Liste</h3>
        <ul class="todo-list" style="list-style: none; padding: 0; margin: 0;">
            <?php foreach ($todos as $todo): ?>
                <li class="todo-item" style="display: flex; justify-content: space-between; align-items: center; background-color: #f8f9fa; padding: 15px; margin-bottom: 10px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); font-size: 14px;">
                    <input type="checkbox" class="todo-checkbox" <?php echo $todo['ischecked'] ? 'checked' : ''; ?> onclick="toggleTask(<?php echo $todo['toDoID']; ?>)" style="margin-right: 10px; transform: scale(1.2);">
                    <span class="todo-text" style="flex-grow: 1; font-size: 14px; color: #333; font-family: var(--font-syncopate); font-weight: bold;"><?php echo htmlspecialchars($todo['toDoName']); ?></span>
                    <small class="todo-small" style="font-size: 12px; color: #555; margin-left: 10px; order: 1;"><?php echo htmlspecialchars($todo['creator']); ?></small>
                    <a href="delete_todo.php?todo_id=<?php echo $todo['toDoID']; ?>" class="todo-delete-link" style="text-decoration: none; font-size: 18px; color: #dc3545; background-color: transparent; padding: 5px; transition: background-color 0.3s ease; margin-left: 15px;">×</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</section>


</body>
</html>
