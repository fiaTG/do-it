<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["userID"])) {
    header("Location: ../public/login.php?error=Bitte zuerst einloggen!");
    exit();
}

// Abrufen der Benutzerinformationen einschließlich Familienname
$stmt = $pdo->prepare("
    SELECT User.vorname, User.famID, Family.famName 
    FROM User 
    LEFT JOIN Family ON User.famID = Family.famID 
    WHERE User.userID = :userID
");
$stmt->execute(['userID' => $_SESSION['userID']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Wenn der Benutzer nicht gefunden wird
if (!$row) {
    echo "Benutzer nicht gefunden.";
    exit();
}

$vorname = $row['vorname']; // Speichert den Vornamen des Benutzers
$famName = $row['famName'] ?? 'Noch keine Familie'; // Wenn der Familienname nicht gesetzt ist, wird 'Noch keine Familie' verwendet
$famID = $row['famID']; // Speichert die Familien-ID des Benutzers

// Einkaufsartikel abrufen, die zur gleichen Familie gehören
$shopItemsStmt = $pdo->prepare("
    SELECT si.shopItemsID, si.itemName, si.menge, s.shopname, ui.shopID
    FROM ShopItems si
    JOIN UserItems ui ON si.shopItemsID = ui.shopitemsID
    JOIN Shop s ON ui.shopID = s.shopID
    JOIN User u ON ui.userID = u.userID
    WHERE u.famID = :famID AND ui.userID = :userID
");
$shopItemsStmt->execute(['famID' => $famID, 'userID' => $_SESSION['userID']]);
$shopItems = $shopItemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Artikel hinzufügen, wenn das Formular gesendet wird
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = $_POST['itemName'] ?? '';  // Artikelname
    $itemQuantity = $_POST['itemQuantity'] ?? 1;  // Menge
    $itemStore = $_POST['itemStore'] ?? '';  // ShopID (Aldi, Lidl, Rewe)
    $userID = $_SESSION['userID'];  // NutzerID aus der Session

    // Überprüfen, ob alle benötigten Daten vorhanden sind
    if (!empty($itemName) && !empty($itemStore) && !empty($userID)) {
        // 1. Schritt: Artikel in die ShopItems-Tabelle einfügen oder Menge aktualisieren
        $stmt = $pdo->prepare("INSERT INTO ShopItems (itemName, menge) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE menge = menge + ?");
        $stmt->execute([$itemName, $itemQuantity, $itemQuantity]);

        // Holt die ID des zuletzt eingefügten oder aktualisierten Artikels
        $shopItemID = $pdo->lastInsertId();

        // 2. Schritt: Artikel für den Benutzer und den Shop in die UserItems-Tabelle einfügen
        $stmt = $pdo->prepare("INSERT IGNORE INTO UserItems (userID, shopitemsID, shopID) 
                               VALUES (?, ?, ?)");
        $stmt->execute([$userID, $shopItemID, $itemStore]);

        // Erfolgsmeldung weiterleiten
        header("Location: shoppingList.php?success=Artikel erfolgreich hinzugefügt!");
        exit();
    } else {
        // Fehlende Daten
        echo "Fehlende Daten";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einkaufsliste</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/stylesdashb.css">
    <link rel="stylesheet" href="../../public/stylesApps.css">
</head>
<body>
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
        <br>
        <h2>Hey, <?php echo htmlspecialchars($row["vorname"]); ?>!</h2>
        <ul class="sidebar-menu">
            <li><a href="/files/Do-IT/public/dashboard.php"><i class="fas fa-home"></i> <span>Startseite</span></a></li>
            <li><a href="/files/Do-IT/private/dashboard/profile.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-user"></i> <span>Profil</span></a></li>
            <li><a href="/files/Do-IT/private/dashboard/family_members.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-users"></i> <span>Familienmitglieder</span></a></li>
            </ul>
        <ul class="sidebar-bottom">
        <li><a href="/files/Do-IT/private/dashboard/setup.php?famID=<?= $row['famID'] ?>&userID=<?= $_SESSION['userID'] ?>"><i class="fas fa-cog"></i> <span>Einstellungen</span></a></li>
            <li><a href="/files/Do-IT/private/auth/logout-handler.php"><i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span></a></li>
        </ul>
    </nav>

        <!-- Header mit Familienbild und Namen -->
        <header class="dashboard-header">

<div class="family-info">
    <p>Shopping List Familie
        <?php echo !empty($row['famName']) ? htmlspecialchars($row['famName']) : 'Noch keine Familie'; ?></p>
</div>
</header>
<div class="shopping-container">
    <form method="POST" action="shoppingList.php" class="shopping-form">
        <div class="top-row">
            <input type="text" id="itemName" name="itemName" placeholder="Artikelname" required>
            <button type="submit">  <i class="fas fa-plus"></i> Add article</button>
            <a href="generatePDF.php" class="pdf-button">📄 PDF</a>
        </div>

        <div class="second-row">
            <input type="number" id="itemQuantity" name="itemQuantity" value="1" required placeholder="Menge">
            <select id="itemStore" name="itemStore" required>
                <option value="1">Aldi</option>
                <option value="2">Lidl</option>
                <option value="3">Rewe</option>
            </select>
        </div>
    </form>


    <?php if (isset($_GET['success'])): ?>

        <div id="successMessage">
    <span style="font-size: 26px;">✔️</span><br>
    <?php echo htmlspecialchars($_GET['success']); ?>

    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
</div>

    <script>
setTimeout(function() {
    const box = document.getElementById('successMessage');
    if (box) {
        box.style.opacity = '0';
        setTimeout(() => box.remove(), 500);
    }
}, 1200);
</script>
<?php endif; ?>

    <div class="shopping-list-container">

    <ul id="shoppingList"></ul> <!-- Hier werden die Artikel dynamisch angezeigt -->
    </div>

    <div id="confirmBox" style="display: none;">
    <div class="confirm-content">
        <p>Möchtest du diesen Artikel wirklich löschen?</p>
        <button id="confirmYes">Ja</button>
        <button id="confirmNo">Nein</button>
    </div>
</div>

    
    <script>
// Lädt die Artikel per Fetch und fügt sie der Seite hinzu
function loadItems() {
    fetch('getItems.php')
        .then(response => response.json())
        .then(data => {
            const shoppingList = document.getElementById('shoppingList');
            shoppingList.innerHTML = ''; // Bestehende Liste löschen

            if (data.error) {
                alert(data.error);
            } else {
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.id = 'item-' + item.shopItemsID;

                    // Prüfen, ob der aktuelle Benutzer der Ersteller ist
                    const isOwner = item.creatorID == <?= $_SESSION['userID'] ?>;
                    
                    // Button-HTML mit "disabled" falls nicht der Ersteller
                    const deleteButton = isOwner 
                        ? `<button class="delete-btn" title="Löschen" onclick="deleteItem(${item.shopItemsID}, '${item.itemName.replace(/'/g, "\\'")}', ${item.shopID})">❌</button>` 
                        : `<button class="delete-btn" title="Löschen" disabled style="opacity: 0.5; cursor: not-allowed;">❌</button>`;

                    li.innerHTML = `${item.itemName} (${item.menge}) bei ${item.shopname} ${deleteButton}`;
                    shoppingList.appendChild(li);
                });
            }
        })
        .catch(error => {
            console.error('Fehler:', error);
            alert('Es gab einen Fehler beim Laden der Artikel.');
        });
}


// Lädt die Artikel, wenn die Seite geladen wird
window.onload = loadItems;

function deleteItem(shopItemsID, itemName, shopID) {
    const confirmBox = document.getElementById('confirmBox');
    confirmBox.style.display = 'flex';

    document.getElementById('confirmYes').onclick = () => {
        confirmBox.style.display = 'none';

        fetch('deleteItems.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                userID: <?= $_SESSION['userID'] ?>,
                itemName: itemName,
                shopID: shopID,
                shopItemsID: shopItemsID
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadItems();
            } else {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Fehler:', error);
            alert('Es gab einen Fehler beim Löschen.');
        });
    };

    document.getElementById('confirmNo').onclick = () => {
        confirmBox.style.display = 'none';
    };
}

    </script>
</body>
</html>
