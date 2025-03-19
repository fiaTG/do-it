
<?php
echo "register.php wurde aufgerufen!<br>"; // Debugging-Text
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/db.php';

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errors.log');
error_reporting(E_ALL);

if (!isset($pdo)) {
    die("Datenbankverbindung nicht gefunden!");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vorname = trim($_POST["vorname"]);
    $nachname = trim($_POST["nachname"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Basisvalidierung
    if (empty($vorname) || empty($nachname) || empty($email) || empty($password)) {
        header("Location: /public/register_public.php?error=" . urlencode("Bitte alle Felder ausfüllen!"));
        exit();
    }

    // E-Mail-Format prüfen
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /public/register_public.php?error=" . urlencode("Ungültige E-Mail-Adresse!"));
        exit();
    }

    // Passwortlänge prüfen
    if (strlen($password) < 8) {
        header("Location: /public/register_public.php?error=" . urlencode("Passwort muss mindestens 8 Zeichen lang sein!"));
        exit();
    }

    try {
        // Prüfen, ob die E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = :email");
        $stmt->execute(["email" => $email]);

        if ($stmt->fetch()) {
            header("Location: /public/register_public.php?error=" . urlencode("Diese E-Mail ist bereits registriert!"));
            exit();
        }

        // Passwort sicher hashen
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Benutzer speichern
        $stmt = $pdo->prepare("INSERT INTO User (vorname, nachname, email, password) VALUES (:vorname, :nachname, :email, :password)");
        $stmt->execute([
            "vorname" => $vorname,
            "nachname" => $nachname,
            "email" => $email,
            "password" => $hashed_password
        ]);

        // Erfolgreich -> Zur Login-Seite oder Startseite
        header("Location: /public/index.php?message=registered");
        exit();
    } catch (PDOException $e) {
        error_log("DB-Fehler: " . $e->getMessage());
        header("Location: /public/register_public.php?error=" . urlencode("Datenbankfehler!"));
        exit();
    }
} else {
    header("Location: /public/register_public.php?error=" . urlencode("Ungültige Anfrage"));
    exit();
}
