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

    
/*
Die Funktion isStrongPassword() verwendet eine Regex,  "regular expression", regex ist ein Muster oder eine "Suchabfrage", die verwendet wird, um Text zu durchsuchen, zu vergleichen oder zu manipulieren.
Sie stellt hier sicher das das passwort bestimmte Kriterien erfüllt
(mindestens 8 Zeichen, eine Zahl, ein Buchstabe und ein Sonderzeichen). 

*/
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
                }, 2500);
            </script>
            <style>
                    @import url('https://fonts.googleapis.com/css2?family=Syncopate&display=swap');

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #406f8f, #968d86);
            font-family: 'Syncopate', sans-serif;
            color: #fdfbf2;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -60%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }

        @keyframes loadBar {
            from { width: 0%; }
            to { width: 100%; }
        }

        .fadeInBox {
            animation: fadeIn 0.8s ease-in-out;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #406f8f, #fdfbf2);
            color: #406f8f;
            padding: 20px 30px;
            border-radius: 12px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15);
            min-width: 300px;
        }

        .progress-container {
            margin-top: 15px;
            height: 6px;
            width: 100%;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #406f8f;
            animation: loadBar 2s ease-in-out forwards;
        }

        .fadeInBox span {
            font-size: 32px;
            display: block;
            margin-bottom: 10px;
        }
               </style>
        </head>
        <body>
            <div class='fadeInBox'>
                <span>✔️</span>
            <h2 style='color: white; text-align: center;'>Sie haben sich erfolgreich registriert!</h2>
            <p style='text-align: center;'>Du wirst in 2 Sekunden zur Startseite weitergeleitet...</p>
            <div class='progress-container'>
                <div class='progress-bar'></div>
            </div>
        
        
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
