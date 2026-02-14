<?php
session_start();
include('../inc/main.php');


if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  header("Location: /");
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Connexion Admin - Mon Taxi CM</title>
  <link rel="stylesheet" href="<?= $css ?>polices.css">
  <link rel="stylesheet" href="<?= $css ?>login.css">
  <link rel="manifest" href="/manifest2.json">

  <script>
    // Enregistrement du Service Worker et gestion hors ligne
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
          .then(reg => console.log('Service Worker enregistré avec succès.'))
          .catch(err => console.error('Erreur SW:', err));
      });
    }
    window.addEventListener('online', () => {
      if (typeof showToast === 'function') showToast('Connexion rétablie', 'success');
    });
    window.addEventListener('offline', () => {
      if (typeof showToast === 'function') showToast('Vous êtes hors ligne. Mode hors connexion activé.', 'error');
    });
  </script>
  <link rel="icon" type="image/x-icon" href="<?= $img ?>fav.png">
  <style>
    body {
      font-family: Po02;
    }

    button,
    input,
    select,
    textarea {
      font-family: Po02 !important;
    }

    .header img {
      width: 210px;
    }
  </style>
  <link rel="manifest" href="/manifest2.json">

  <script>
    // Enregistrement du Service Worker et gestion hors ligne
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
          .then(reg => console.log('Service Worker enregistré avec succès.'))
          .catch(err => console.error('Erreur SW:', err));
      });
    }
    window.addEventListener('online', () => {
      if (typeof showToast === 'function') showToast('Connexion rétablie', 'success');
    });
    window.addEventListener('offline', () => {
      if (typeof showToast === 'function') showToast('Vous êtes hors ligne. Mode hors connexion activé.', 'error');
    });
  </script>
</head>

<body>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="header">
        <img src="<?= $img ?>logo2.png">
        <p>Accès réservé aux administrateurs</p>
      </div>

      <div class="error-message" id="error-box" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <span id="error-text"></span>
      </div>

      <form id="login-form" action="" method="post">
        <div class="form-field">
          <label for="email">Email d'utilisateur</label>
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="email" id="email" name="email" placeholder="admin" required autofocus>
          </div>
        </div>

        <div class="form-field">
          <label for="password">Mot de passe</label>
          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <i class="fas fa-eye password-toggle" id="toggle-password"></i>
          </div>
        </div>

        <button type="submit" class="submit-btn" id="submit-btn">
          <i class="fas fa-sign-in-alt"></i> Se connecter
        </button>
      </form>

      <div class="extra-links">
        <a href="#">Mot de passe oublié ?</a>
      </div>
    </div>
  </div>

  <script src="<?= $js ?>login.js"></script>
</body>

</html>