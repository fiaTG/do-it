<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>

<h2>Willkommen, <?php echo htmlspecialchars($_SESSION["vorname"]); ?>!</h2>

<p>Hier entsteht in kürze dein Dashboard</p>

<a href="../private/auth/logout-handler.php">Logout</a>

</body>
</html>
