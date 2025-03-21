<?php
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
    $token = $_POST["token"] ?? null; // Token aus POST statt GET holen

    // Basisvalidierung
    if (empty($vorname) || empty($nachname) || empty($email) || empty($password)) {
        header("Location: /files/Do-IT/public/register_public.php?error=" . urlencode("Bitte alle Felder ausfüllen!"));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /files/Do-IT/public/register_public.php?error=" . urlencode("Ungültige E-Mail-Adresse!"));
        exit();
    }

    function isStrongPassword($password) {
        return preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $password);
    }

    if (!isStrongPassword($password)) {
        header("Location: /files/Do-IT/public/register_public.php?error=" . urlencode("Passwort muss 8 Zeichen, eine Zahl, einen Buchstaben & ein Sonderzeichen enthalten!"));
        exit();
    }

    try {
        // Prüfen, ob die E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = :email");
        $stmt->execute(["email" => $email]);

        if ($stmt->fetch()) {
            header("Location: /files/Do-IT/public/register_public.php?error=" . urlencode("Diese E-Mail ist bereits registriert!"));
            exit();
        }

        // Standard: Keine Familie
        $famID = null;

        // Token überprüfen und FamID setzen
        if ($token) {
            $stmt = $pdo->prepare("SELECT famID FROM Invites WHERE token = :token");
            $stmt->execute(['token' => $token]);
            $invite = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($invite) {
                $famID = $invite['famID'] ?? null;
            }
        }

        // Passwort sicher hashen
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Benutzer speichern mit `famID`
        $stmt = $pdo->prepare("INSERT INTO User (vorname, nachname, email, password, famID) 
                               VALUES (:vorname, :nachname, :email, :password, :famID)");
        $stmt->execute([
            "vorname" => $vorname,
            "nachname" => $nachname,
            "email" => $email,
            "password" => $hashed_password,
            "famID" => $famID
        ]);

        // Einladung nach erfolgreicher Registrierung löschen
        if ($token) {
            $stmt = $pdo->prepare("DELETE FROM Invites WHERE token = :token");
            $stmt->execute(["token" => $token]);
        }

        echo "<!DOCTYPE html>
        <html lang='de'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Registrierung erfolgreich</title>
            <script>
                setTimeout(function() {
                    window.location.href = '/files/Do-IT/public/index.php?message=registered';
                }, 2000);
            </script>
        </head>
        <body>
            <h2 style='color: green; text-align: center;'>Sie haben sich erfolgreich registriert!</h2>
            <p style='text-align: center;'>Du wirst in 2 Sekunden zur Startseite weitergeleitet...</p>
        </body>
        </html>";
        exit();

    } catch (PDOException $e) {
        error_log("DB-Fehler: " . $e->getMessage());
        header("Location: /files/Do-IT/public/register_public.php?error=" . urlencode("Datenbankfehler!"));
        exit();
    }
}
?>
