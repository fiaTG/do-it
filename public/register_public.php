<?php
$fehler = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";
// Prüft, ob in der URL ein Fehlerparameter übergeben wurde (z. B. ?error=Registrierung+fehlgeschlagen).
// Falls ja, wird der Fehlertext mit htmlspecialchars() gegen Cross-Site Scripting (XSS) abgesichert.
// Der bereinigte Text wird in der Variablen $fehler gespeichert und später zur Anzeige im HTML verwendet.
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung</title>
    <style>
@import url('https://fonts.googleapis.com/css2?family=Syncopate&display=swap');

:root {
    --primary-color: #406f8f;
    --secondary-color: #968d86;
    --light-bg: #fdfbf2;
}

body {
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #406f8f, #968d86); /* Hintergrund-Gradient */
    font-family: 'Syncopate', sans-serif;
    color: #fdfbf2;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Die Seite nimmt die volle Höhe des Bildschirms ein */
}

h2 {
    font-size: 2rem;
    text-align: center;
    color: #fdfbf2;
    margin-right: 10%;
}

form {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    font-family: 'Syncopate', sans-serif;
    color: #406f8f;
}

label {
    font-size: 1rem;
    margin-bottom: 8px;
    color: #406f8f;
}

input {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 1rem;
    width: 100%;
}

input[type="email"],
input[type="text"],
input[type="password"] {
    background-color: #f7f7f7;
}

button {
    background-color: var(--primary-color);
    color: white;
    padding: 12px;
    font-size: 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 20px;
    font-family: 'Syncopate', sans-serif;
    font-weight: bold;
}

button:hover {
    background-color: var(--secondary-color);
}

p {
    font-size: 1rem;
    text-align: center;
    color: red;
    margin-top: 20px;
}

    </style>
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
