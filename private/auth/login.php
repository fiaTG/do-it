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

            header("Location: /files/Do-IT/public/dashboard.php");


            exit();
        } else {
            echo " Falsche E-Mail-Adresse oder Passwort!";
        }
    } else {
        echo " Bitte alle Felder ausfüllen!";
    }
}
?>
