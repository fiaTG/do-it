<?php
session_start();
require_once "../private/config/db.php"; // Verbindung zur DB

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        // Nutzer anhand der E-Mail-Adresse suchen
        $stmt = $pdo->prepare("SELECT userID, vorname, nachname, email, password FROM User WHERE email = :email");
        $stmt->execute(["email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            // Login erfolgreich → Session setzen
            $_SESSION["userID"] = $user["userID"];
            $_SESSION["vorname"] = $user["vorname"];
            $_SESSION["nachname"] = $user["nachname"];
            $_SESSION["email"] = $user["email"];

            echo "<!DOCTYPE html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Logout</title>
    <script>
        setTimeout(function() {
            window.location.href = '/files/Do-IT/public/dashboard.php?message=Login+erfolgreich!';
        }, 2000); // 2 Sekunden Verzögerung
    </script>
</head>
<body>
    <h2 style='color: green; text-align: center;'>Du wurdest erfolgreich eingeloggt!</h2>
    <p style='text-align: center;'>Du wirst in 2 Sekunden zum Dashboard weitergeleitet...</p>
</body>
</html>";

            


            exit();
        } else {
            echo " Falsche E-Mail-Adresse oder Passwort!";
        }
    } else {
        echo " Bitte alle Felder ausfüllen!";
    }
}
?>
