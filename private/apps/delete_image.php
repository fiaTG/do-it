<?php
session_start();

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['userID'])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bilderID'])) {
    $bilderID = (int) $_POST['bilderID'];
    $userID = $_SESSION['userID'];

    try {
        // Sicherstellen, dass das Bild zur Familie des Nutzers gehört
        $stmt = $pdo->prepare("SELECT famID FROM User WHERE userID = :userID");
        $stmt->execute(['userID' => $userID]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM Bilder WHERE bilderID = :bilderID AND famID = :famID");
        $stmt->execute([
            'bilderID' => $bilderID,
            'famID' => $user['famID']
        ]);

        header("Location: gallery.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        echo "Fehler beim Löschen: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Ungültiger Zugriff.";
}
?>
