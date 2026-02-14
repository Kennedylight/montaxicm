<?php

include('inc/main.php');

$current_page = $_GET['page'] ?? 'dashboard';

if (!isset($_SESSION['id'])) {
  header('Location: ./login');
  exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - Mon Taxi CM</title>

  <link rel="stylesheet" href="<?= $css ?>polices.css">
  <link rel="stylesheet" href="<?= $css ?>dashboard.css">
  <link rel="icon" type="image/x-icon" href="<?= $img ?>fav.png">
  <link rel="manifest" href="/manifest2.json">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
</head>

<body>

  <!-- Header fixe -->
  <header class="header">
    <div class="header-left">
      <i class="fas fa-bars menu-toggle"></i>
      <img src="<?= $img ?>logo2.png" style="width: 180px; " alt="Logo Mon Taxi CM">

      <span style=" border:solid 1px #0001; padding:5px 10px ; border-radius:5px; font-family: Po02; font-size: 12px;"> Admin</span>
    </div>
    <div class="header-right">
      <span>Bonjour, <?= htmlspecialchars($_SESSION['nom'] ?? 'Admin') ?></span>
      <a href="./logout.php" id="deconnexion" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
      </a>
    </div>
  </header>

  <!-- Sidebar fixe -->
  <aside class="sidebar">
    <ul>
      <li data-page="accueil" class="<?= $current_page === 'accueil' || $current_page === null || $current_page === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-home"></i> Accueil</li>
      <li data-page="utilisateurs" class="<?= $current_page === 'utilisateurs' ? 'active' : '' ?>"><i class="fas fa-users"></i> Utilisateurs</li>
      <li data-page="conducteurs" class="<?= $current_page === 'conducteurs' ? 'active' : '' ?>"><i class="fas fa-car"></i> Conducteurs</li>
      <li data-page="verificationkyc" class="<?= $current_page === 'verificationkyc' ? 'active' : '' ?>"><i class="fas fa-check-circle"></i> Vérification kyc</li>
      <li data-page="tarification" class="<?= $current_page === 'tarification' ? 'active' : '' ?>"><i class="fas fa-money-bill-wave"></i> Tarification</li>

    </ul>
  </aside>

  <!-- Contenu principal (chargé dynamiquement par JS) -->
  <main class="main-content">
    <div class="loader"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>
    <div id="content"></div>
  </main>

  <script src="<?= $js ?>dashboard.js"></script>
</body>

</html>