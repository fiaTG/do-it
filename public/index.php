<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page mit Dreieck</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">

        <div class="logo"></div>
        <!-- Linkes Bild -->
        <div class="outer-layer left" aria-hidden="true"></div>
        <!-- Rechtes Bild -->
        <div class="outer-layer right" aria-hidden="true"></div>

        <!-- Login-Formular und triangle Area -->
        <div class="triangle" aria-hidden="true">
            <div class="login-area">
                <div class="logoMini"></div>

                <form id="loginForm" action="auth.php" method="POST">
                    <label for="email">E-Mail</label>
                    <input id="email" name="email" type="email" required>

                    <label for="password">Passwort</label>
                    <input id="password" name="password" type="password" required>
                </form>

 
            
            <!-- PHP-Code zur Anzeige einer Nachricht nach erfolgreicher Registrierung
             Überprüfung, ob eine Nachricht in der URL als GET-Parameter übergeben wurde -->
            <?php if (isset($_GET['message']) && $_GET['message'] === 'registered'): ?>
                <p style="color: white;font-size:9px;">Erfolgreich registriert! Bitte logge dich ein.</p>
            <?php endif; ?>
        </div>

        <!-- 🔹 Button-Container -->
<div class="button-container">
  
  <!-- Login-Button -->
  <a class="box__link button-animation" href="#" onclick="submitForm(event, 'loginForm')"
     onmouseover="toggleIcon(true)" onmouseout="toggleIcon(false)">
    <i id="icon" class="fa-solid fa-door-closed"></i>
  </a>

  <!-- Registrieren-Button -->
  <a class="box__link button-animation" href="register_public.php"
     onmouseover="toggleRegisterIcon(true)" onmouseout="toggleRegisterIcon(false)">
    <i id="register-icon" class="fa-solid fa-user-plus"></i>
  </a>

</div>
</div>

        <!-- Unterer Bildbereich -->
        <div class="bottom-image" aria-hidden="true"></div>

    <script src="js\main.js" defer></script>
</body>
</html>