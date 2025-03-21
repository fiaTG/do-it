<?php
$fehler = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung</title>
</head>
<body>
    <h2>Registrierung</h2>

    <?php if (!empty($fehler)): ?>
        <p style="color:red;"><?php echo $fehler; ?></p>
    <?php endif; ?>

    <form action="../private/auth/register-handler.php" method="POST">

  <!-- Verstecktes Token-Feld -->
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
     
        <label for="vorname">Vorname</label>
        <input id="vorname" name="vorname" type="text" required>

        <label for="nachname">Nachname</label>
        <input id="nachname" name="nachname" type="text" required>

            <label for="email">E-Mail</label>
        <input id="email" name="email" type="email" required>

        <label for="password">Passwort</label>
        <input id="password" name="password" type="password" required>

        <button type="submit">Registrieren</button>
    </form>
</body>
</html>
