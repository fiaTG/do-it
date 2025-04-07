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
    <link rel="stylesheet" href="../../public/stylesApps.css">
</head>

<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <h2>Hey, <?php echo htmlspecialchars($userData["vorname"]); ?>!</h2>
        <ul class="sidebar-menu">
            <li><a href="/files/Do-IT/public/dashboard.php"><i class="fas fa-home"></i> Startseite</a></li>
            <li><a href="/files/Do-IT/private/dashboard/profile.php"><i class="fas fa-user"></i> <span>Profil</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> Familienmitglieder</a></li>
        </ul>
        <ul class="sidebar-bottom">
            <li><a href="#"><i class="fas fa-cog"></i> Einstellungen</a></li>
            <li><a href="/files/Do-IT/private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Header mit Familieninfo -->
    <header class="dashboard-header">
        <div class="family-info">
            <p>ToDo Liste Familie <?php echo !empty($userData['famName']) ? htmlspecialchars($userData['famName']) : 'Noch keine Familie'; ?></p>
        </div>
    </header>

<!-- Container für die ToDo-Liste und das Formular nebeneinander -->
<section class="todo-list-wrapper">

    <!-- Neues ToDo hinzufügen -->
    <form action="add_todo.php" method="POST" class="add-todo-form">
        <input type="text" name="task" placeholder="Neue Aufgabe" required>
        <button type="submit">Hinzufügen</button>
    </form>
    
    <!-- ToDo Liste -->
    <section class="todo-list-container">
        <h3>Deine ToDo Liste</h3>
        <ul class="todo-list">
            <?php foreach ($todos as $todo): ?>
                <li class="todo-item">
                    <input type="checkbox" class="todo-checkbox" <?php echo $todo['ischecked'] ? 'checked' : ''; ?> onclick="toggleTask(<?php echo $todo['toDoID']; ?>)">
                    <span class="todo-text"><?php echo htmlspecialchars($todo['toDoName']); ?></span>
                    <small class="todo-small">Erstellt von: <?php echo htmlspecialchars($todo['creator']); ?></small>
                    <?php if ($todo['assignedID']): ?>
                    <?php endif; ?>
                    <a href="delete_todo.php?todo_id=<?php echo $todo['toDoID']; ?>" class="todo-delete-link">Löschen</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>


</section>



    <!-- JavaScript-Funktion für das Umschalten von Aufgaben -->
    <script>
        function toggleTask(todoID) {
            window.location.href = 'toggle_task.php?todo_id=' + todoID;
        }
    </script>

</body>
</html>
