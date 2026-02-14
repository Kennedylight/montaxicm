<?php include('inc/main.php');


$isLoggedIn = isset($_SESSION['id']);
$chauffeurNom = '';

if ($isLoggedIn) {
  $stmt = $bdd->prepare("SELECT prenom, nom, statut FROM chauffeurs WHERE id = ?");
  $stmt->execute([$_SESSION['id']]);
  $ch = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$ch) {
    // Session corrompue
    session_destroy();
    $isLoggedIn = false;
  } else {
    $chauffeurNom = htmlspecialchars($ch['prenom']);
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MonTaxi — Conduisez, Gagnez, Librement</title>
  <link rel="stylesheet" href="<?= $css ?>polices.css">
  <link rel="shortcut icon" href="<?= $img ?>fav.png" type="image/x-icon">

  <link rel="manifest" href="/manifest2.json">

  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --noir: #0d0d0d;
      --blanc: #ffffff;
      --gris-bg: #f5f4f0;
      --gris-clair: #e8e6e1;
      --or: #f5cb17;
      --or-clair: #f0d98a;
      --texte: #1a1a1a;
      --texte-doux: #5a5a5a;
      --radius: 14px;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: Po02;
      background: var(--blanc);
      color: var(--texte);
      overflow-x: hidden;
    }

    /* ── NAV ── */
    nav {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 60px;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
      transition: padding .3s;
    }

    nav.scrolled {
      padding: 14px 60px;
    }

    .logo {
      font-family: Po01;
      font-size: 1.5rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      color: var(--texte);
    }

    .logo img {
      width: 200px;
    }

    .logo-badge {
      background: var(--noir);
      color: var(--or);
      border-radius: 8px;
      padding: 3px 10px;
      font-size: .85rem;
      font-weight: 700;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 28px;
      border-radius: 50px;
      font-family: Po01;
      font-weight: 500;
      font-size: .9rem;
      cursor: pointer;
      transition: all .25s;
      text-decoration: none;
      border: none;
    }

    .btn-ghost {
      background: transparent;
      color: var(--texte);
      border: 1.5px solid var(--gris-clair);
    }

    .btn-ghost:hover {
      border-color: var(--texte);
      background: var(--gris-bg);
    }

    .btn-primary {
      background: var(--noir);
      color: var(--blanc);
    }

    .btn-primary:hover {
      background: #2a2a2a;
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, .15);
    }

    .btn-gold {
      background: var(--or);
      color: var(--noir);
      font-weight: 700;
      font-size: 1.05rem;
      padding: 16px 38px;
    }

    .btn-gold:hover {
      background: #b8943f;
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(201, 168, 76, .35);
    }

    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      padding: 120px 60px 80px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
      background: var(--blanc);
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: -200px;
      right: -200px;
      width: 700px;
      height: 700px;
      background: radial-gradient(circle, rgba(201, 168, 76, .12) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 120px;
      background: linear-gradient(to bottom, transparent, var(--gris-bg));
      pointer-events: none;
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--gris-bg);
      border: 1px solid var(--gris-clair);
      border-radius: 50px;
      padding: 6px 16px;
      font-size: .82rem;
      font-weight: 500;
      color: var(--texte-doux);
      margin-bottom: 28px;
      animation: fadeUp .6s ease both;
    }

    .hero-eyebrow span {
      color: var(--or);
      font-weight: 700;
    }

    .dot-anim {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #22c55e;
      animation: pulse 1.8s ease-in-out infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1)
      }

      50% {
        opacity: .5;
        transform: scale(1.4)
      }
    }

    .hero-title {
      font-family: Po01;
      font-size: clamp(2.5rem, 5vw, 4.2rem);
      font-weight: 800;
      line-height: 1.08;
      margin-bottom: 24px;
      animation: fadeUp .7s .1s ease both;
    }

    .hero-title em {
      font-style: normal;
      color: var(--or);
      position: relative;
      display: inline-block;
    }

    .hero-title em::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: var(--or);
      border-radius: 2px;
    }

    .hero-sub {
      font-size: 1.1rem;
      color: var(--texte-doux);
      line-height: 1.7;
      max-width: 460px;
      margin-bottom: 40px;
      animation: fadeUp .7s .2s ease both;
    }

    .hero-actions {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      animation: fadeUp .7s .3s ease both;
    }

    .hero-trust {
      margin-top: 56px;
      display: flex;
      align-items: center;
      gap: 24px;
      animation: fadeUp .7s .4s ease both;
    }

    .trust-item {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .trust-num {
      font-family: Po01;
      font-size: 1.6rem;
      font-weight: 800;
    }

    .trust-label {
      font-size: .8rem;
      color: var(--texte-doux);
      font-weight: 400;
    }

    .trust-sep {
      width: 1px;
      height: 40px;
      background: var(--gris-clair);
    }

    /* ── HERO VISUAL ── */
    .hero-visual {
      position: relative;
      animation: fadeLeft .8s .2s ease both;
    }

    .phone-mockup {
      width: 100%;
      max-width: 380px;
      margin: 0 auto;
      background: var(--noir);
      border-radius: 40px;
      padding: 20px;
      box-shadow: 0 40px 80px rgba(0, 0, 0, .18), 0 0 0 1px rgba(255, 255, 255, .08);
      position: relative;
    }

    .phone-screen {
      background: var(--gris-bg);
      border-radius: 28px;
      overflow: hidden;
      aspect-ratio: 9/17;
      position: relative;
    }

    .phone-map {
      width: 100%;
      height: 65%;
      background:
        linear-gradient(rgba(240, 238, 232, .7), rgba(240, 238, 232, .7)),
        repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(0, 0, 0, .04) 39px, rgba(0, 0, 0, .04) 40px),
        repeating-linear-gradient(90deg, transparent, transparent 39px, rgba(0, 0, 0, .04) 39px, rgba(0, 0, 0, .04) 40px);
      position: relative;
    }

    /* Fake route sur la carte */
    .map-route {
      position: absolute;
      inset: 0;
    }

    .map-route svg {
      width: 100%;
      height: 100%;
    }

    .map-pin {
      position: absolute;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
    }

    .map-pin-start {
      background: var(--or);
      top: 30%;
      left: 20%;
    }

    .map-pin-end {
      background: var(--noir);
      color: var(--blanc);
      bottom: 10%;
      right: 20%;
    }

    .map-car {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 1.8rem;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, .2));
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

      0%,
      100% {
        transform: translate(-50%, -50%) translateY(0)
      }

      50% {
        transform: translate(-50%, -50%) translateY(-6px)
      }
    }

    .phone-ui {
      padding: 16px;
      background: var(--blanc);
      height: 35%;
    }

    .phone-greeting {
      font-size: .65rem;
      color: var(--texte-doux);
      margin-bottom: 4px;
    }

    .phone-name {
      font-family: Po01;
      font-size: .85rem;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .phone-status {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #ecfdf5;
      border-radius: 10px;
      padding: 8px 12px;
      margin-bottom: 12px;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #22c55e;
    }

    .status-text {
      font-size: .7rem;
      color: #166534;
      font-weight: 500;
    }

    .phone-card {
      background: var(--gris-bg);
      border-radius: 12px;
      padding: 10px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .phone-card-label {
      font-size: .62rem;
      color: var(--texte-doux);
    }

    .phone-card-val {
      font-family: Po01;
      font-size: .9rem;
      font-weight: 700;
    }

    .phone-card-badge {
      background: var(--or);
      color: var(--noir);
      border-radius: 6px;
      padding: 3px 8px;
      font-size: .6rem;
      font-weight: 700;
    }

    /* Badge flottant */
    .float-badge {
      position: absolute;
      background: var(--blanc);
      border-radius: 14px;
      padding: 12px 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
      display: flex;
      align-items: center;
      gap: 10px;
      animation: badgePop .5s ease both;
    }

    .float-badge-1 {
      top: 10%;
      right: -10%;
      animation-delay: .6s;
    }

    .float-badge-2 {
      bottom: 20%;
      left: -8%;
      animation-delay: .8s;
    }

    @keyframes badgePop {
      from {
        opacity: 0;
        transform: scale(.8)
      }

      to {
        opacity: 1;
        transform: scale(1)
      }
    }

    .badge-icon {
      font-size: 1.4rem;
    }

    .badge-text strong {
      display: block;
      font-size: .8rem;
      font-weight: 600;
    }

    .badge-text span {
      font-size: .7rem;
      color: var(--texte-doux);
    }

    /* ── FEATURES ── */
    .section-features {
      background: var(--gris-bg);
      padding: 100px 60px;
    }

    .section-header {
      text-align: center;
      margin-bottom: 64px;
    }

    .section-tag {
      display: inline-block;
      background: var(--noir);
      color: var(--or);
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      border-radius: 50px;
      padding: 5px 16px;
      margin-bottom: 16px;
    }

    .section-title {
      font-family: Po01;
      font-size: clamp(1.8rem, 3vw, 2.8rem);
      font-weight: 800;
      line-height: 1.2;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
      max-width: 1100px;
      margin: 0 auto;
    }

    .feature-card {
      background: var(--blanc);
      border-radius: var(--radius);
      padding: 36px 32px;
      border: 1px solid var(--gris-clair);
      transition: transform .25s, box-shadow .25s;
      position: relative;
      overflow: hidden;
    }

    .feature-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 40px rgba(0, 0, 0, .08);
    }

    .feature-card.featured {
      background: var(--noir);
      color: var(--blanc);
      border-color: var(--noir);
    }

    .feature-card.featured .feature-desc {
      color: rgba(255, 255, 255, .65);
    }

    .feature-ico {
      width: 52px;
      height: 52px;
      background: var(--gris-bg);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 22px;
    }

    .feature-card.featured .feature-ico {
      background: rgba(255, 255, 255, .1);
    }

    .feature-title {
      font-family: Po01;
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .feature-desc {
      font-size: .9rem;
      color: var(--texte-doux);
      line-height: 1.65;
    }

    /* ── GAINS ── */
    .section-gains {
      padding: 100px 60px;
      background: var(--blanc);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
    }

    .gains-title {
      font-family: Po01;
      font-size: clamp(2rem, 3.5vw, 3rem);
      font-weight: 800;
      line-height: 1.15;
      margin-bottom: 20px;
    }

    .gains-sub {
      font-size: 1rem;
      color: var(--texte-doux);
      line-height: 1.7;
      margin-bottom: 36px;
    }

    .gains-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-bottom: 40px;
    }

    .gains-list li {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      font-size: .95rem;
      line-height: 1.5;
    }

    .gains-check {
      width: 24px;
      height: 24px;
      flex-shrink: 0;
      background: #ecfdf5;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .8rem;
      color: #16a34a;
      margin-top: 1px;
    }

    .earnings-card {
      background: var(--noir);
      color: var(--blanc);
      border-radius: 24px;
      padding: 40px;
      position: relative;
      overflow: hidden;
    }

    .earnings-card::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -80px;
      width: 250px;
      height: 250px;
      background: radial-gradient(circle, rgba(201, 168, 76, .25) 0%, transparent 70%);
    }

    .earnings-label {
      font-size: .8rem;
      color: rgba(255, 255, 255, .6);
      margin-bottom: 8px;
      font-weight: 500;
    }

    .earnings-amount {
      font-family: Po01;
      font-size: 3rem;
      font-weight: 800;
      color: var(--or);
      margin-bottom: 4px;
    }

    .earnings-sub {
      font-size: .85rem;
      color: rgba(255, 255, 255, .5);
      margin-bottom: 32px;
    }

    .earnings-breakdown {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .breakdown-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .breakdown-left {
      font-size: .85rem;
      color: rgba(255, 255, 255, .6);
    }

    .breakdown-right {
      font-size: .9rem;
      font-weight: 600;
    }

    .breakdown-bar {
      height: 4px;
      background: rgba(255, 255, 255, .1);
      border-radius: 2px;
      margin-top: 4px;
    }

    .breakdown-fill {
      height: 100%;
      background: var(--or);
      border-radius: 2px;
      transition: width 1s ease;
    }

    /* ── STEPS ── */
    .section-steps {
      background: var(--gris-bg);
      padding: 100px 60px;
      text-align: center;
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0;
      max-width: 1100px;
      margin: 64px auto 0;
      position: relative;
    }

    .steps-grid::before {
      content: '';
      position: absolute;
      top: 28px;
      left: 12.5%;
      right: 12.5%;
      height: 2px;
      background: var(--gris-clair);
      z-index: 0;
    }

    .step {
      padding: 0 20px;
      position: relative;
      z-index: 1;
    }

    .step-num {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--blanc);
      border: 2px solid var(--gris-clair);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: Po01;
      font-size: 1.1rem;
      font-weight: 800;
      margin: 0 auto 20px;
      transition: background .3s, border-color .3s, color .3s;
    }

    .step:hover .step-num {
      background: var(--noir);
      color: var(--or);
      border-color: var(--noir);
    }

    .step-title {
      font-family: Po01;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .step-desc {
      font-size: .85rem;
      color: var(--texte-doux);
      line-height: 1.6;
    }

    /* ── CTA FINAL ── */
    .section-cta {
      padding: 100px 60px;
      background: var(--noir);
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .section-cta::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 800px;
      height: 400px;
      background: radial-gradient(ellipse, rgba(201, 168, 76, .15) 0%, transparent 70%);
    }

    .cta-title {
      font-family: Po01;
      font-size: clamp(2rem, 4vw, 3.5rem);
      font-weight: 800;
      color: var(--blanc);
      line-height: 1.1;
      margin-bottom: 20px;
      position: relative;
    }

    .cta-sub {
      font-size: 1.05rem;
      color: rgba(255, 255, 255, .6);
      margin-bottom: 40px;
      position: relative;
    }

    .cta-actions {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
      position: relative;
    }

    .btn-ghost-white {
      background: transparent;
      color: var(--blanc);
      border: 1.5px solid rgba(255, 255, 255, .25);
    }

    .btn-ghost-white:hover {
      border-color: rgba(255, 255, 255, .6);
      background: rgba(255, 255, 255, .06);
    }

    /* ── FOOTER ── */
    footer {
      background: #080808;
      padding: 40px 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .footer-logo {
      color: rgba(255, 255, 255, .7);
      font-family: Po01;
      font-weight: 700;
    }

    .footer-copy {
      font-size: .82rem;
      color: rgba(255, 255, 255, .35);
    }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(24px)
      }

      to {
        opacity: 1;
        transform: none
      }
    }

    @keyframes fadeLeft {
      from {
        opacity: 0;
        transform: translateX(24px)
      }

      to {
        opacity: 1;
        transform: none
      }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      nav {
        padding: 18px 24px;
      }

      .hero {
        grid-template-columns: 1fr;
        padding: 100px 24px 60px;
      }

      .hero-visual {
        display: none;
      }

      .section-features,
      .section-steps,
      .section-cta {
        padding: 70px 24px;
      }

      .section-gains {
        grid-template-columns: 1fr;
        padding: 70px 24px;
        gap: 40px;
      }

      .features-grid {
        grid-template-columns: 1fr;
      }

      .steps-grid {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
      }

      .steps-grid::before {
        display: none;
      }

      footer {
        flex-direction: column;
        gap: 12px;
        text-align: center;
        padding: 28px 24px;
      }
    }

    @media (max-width: 600px) {
      .btn-ghost {
        display: none;
      }

      nav.scrolled {
        padding: 10px 20px;
      }

      .steps-grid {
        gap: 20px;
      }

      .steps-grid .step {
        padding: 0;
      }
    }
  </style>
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

  <!-- ── NAV ── -->
  <nav id="navbar">
    <a href="#" class="logo">
      <img src="<?= $img ?>logo2.png" alt="MonTaxi>
    </a>
    <div class="nav-links">
      <?php if ($isLoggedIn): ?>
        <span style="font-size:.85rem;color:#6b6b6b;font-weight:500">Bonjour, <?= $chauffeurNom ?> 👋</span>
        <a href="/app" class="btn btn-primary">🚖 Mon tableau de bord</a>
      <?php else: ?>
        <a href="/sign?mode=login" class="btn btn-ghost">Connexion</a>
        <a href="/sign?mode=register" class="btn btn-primary">S'inscrire</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <section class="hero">
    <div class="hero-content">
      <div class="hero-eyebrow">
        <div class="dot-anim"></div>
        <span><span>+500</span> chauffeurs actifs au Cameroun</span>
      </div>

      <h1 class="hero-title">
        Conduisez librement.<br>
        Gagnez <em>plus</em>.<br>
        Chaque jour.
      </h1>

      <p class="hero-sub">
        Rejoignez la plateforme de taxi la plus moderne du Cameroun. Vous gérez vos horaires, vous acceptez les courses qui vous conviennent, et vous êtes payé directement.
      </p>

      <div class="hero-actions">
        <?php if ($isLoggedIn): ?>
          <a href="/app" class="btn btn-gold">🚖 Accéder à mon espace</a>
        <?php else: ?>
          <a href="/sign?mode=register" class="btn btn-gold">🚖 Inscrivez-vous maintenant</a>
        <?php endif; ?>
        <a href="#comment-ca-marche" class="btn btn-ghost">Comment ça marche →</a>
      </div>

      <div class="hero-trust">
        <div class="trust-item">
          <span class="trust-num">87%</span>
          <span class="trust-label">de revenus reversés</span>
        </div>
        <div class="trust-sep"></div>
        <div class="trust-item">
          <span class="trust-num">0 FCFA</span>
          <span class="trust-label">d'inscription</span>
        </div>
        <div class="trust-sep"></div>
        <div class="trust-item">
          <span class="trust-num">24h</span>
          <span class="trust-label">validation KYC</span>
        </div>
      </div>
    </div>

    <!-- Phone mockup -->
    <div class="hero-visual">
      <div class="phone-mockup">
        <div class="phone-screen">
          <div class="phone-map">
            <div class="map-route">
              <svg viewBox="0 0 300 200" preserveAspectRatio="none">
                <path d="M60,140 C80,120 100,100 140,90 C180,80 220,70 250,60"
                  stroke="#c9a84c" stroke-width="3" fill="none"
                  stroke-dasharray="8,4" opacity=".8" />
              </svg>
            </div>
            <div class="map-pin map-pin-start">📍</div>
            <div class="map-pin map-pin-end">🏁</div>
            <div class="map-car">🚖</div>
          </div>
          <div class="phone-ui">
            <div class="phone-greeting">Bonjour, bienvenue 👋</div>
            <div class="phone-name">Paul Mbarga</div>
            <div class="phone-status">
              <div class="status-dot"></div>
              <span class="status-text">En ligne • 3 courses à proximité</span>
            </div>
            <div class="phone-card">
              <div>
                <div class="phone-card-label">Gains aujourd'hui</div>
                <div class="phone-card-val">12 500 FCFA</div>
              </div>
              <div class="phone-card-badge">+3 courses</div>
            </div>
          </div>
        </div>
      </div>

      <div class="float-badge float-badge-1">
        <div class="badge-icon">⭐</div>
        <div class="badge-text">
          <strong>4.9 / 5</strong>
          <span>Note moyenne</span>
        </div>
      </div>

      <div class="float-badge float-badge-2">
        <div class="badge-icon">💰</div>
        <div class="badge-text">
          <strong>+285 000 FCFA</strong>
          <span>Ce mois-ci</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ── FEATURES ── -->
  <section class="section-features" id="avantages">
    <div class="section-header">
      <div class="section-tag">Pourquoi MonTaxi ?</div>
      <h2 class="section-title">Tout ce dont vous avez besoin<br>pour réussir</h2>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-ico">🗺️</div>
        <div class="feature-title">Courses intelligentes</div>
        <p class="feature-desc">Recevez uniquement les courses dans un rayon d'1 km. Moins de déplacements à vide, plus de gains réels.</p>
      </div>
      <div class="feature-card featured">
        <div class="feature-ico">💵</div>
        <div class="feature-title">87% pour vous</div>
        <p class="feature-desc">La commission la plus basse du marché camerounais. Vous gardez l'essentiel de ce que vous gagnez, point final.</p>
      </div>
      <div class="feature-card">
        <div class="feature-ico">🕐</div>
        <div class="feature-title">Horaires libres</div>
        <p class="feature-desc">Aucun minimum d'heures imposé. Travaillez le matin, le soir, le week-end — selon votre vie.</p>
      </div>
      <div class="feature-card">
        <div class="feature-ico">🚦</div>
        <div class="feature-title">Covoiturage intégré</div>
        <p class="feature-desc">Maximisez chaque trajet en acceptant plusieurs clients sur le même itinéraire. Plus de courses, plus de revenus.</p>
      </div>
      <div class="feature-card">
        <div class="feature-ico">🛡️</div>
        <div class="feature-title">KYC sécurisé</div>
        <p class="feature-desc">Vérification sous 24h. Votre profil validé inspire confiance aux clients et protège votre réputation.</p>
      </div>
      <div class="feature-card">
        <div class="feature-ico">📊</div>
        <div class="feature-title">Suivi en temps réel</div>
        <p class="feature-desc">Consultez vos gains, vos courses et votre historique directement depuis votre tableau de bord.</p>
      </div>
    </div>
  </section>

  <!-- ── GAINS ── -->
  <div style="background:var(--blanc); padding: 80px 0;">
    <div class="section-gains">
      <div>
        <div class="section-tag" style="text-align:left">Revenus réels</div>
        <h2 class="gains-title">Gagnez<br><em style="color:var(--or);font-style:normal">plus</em> qu'ailleurs</h2>
        <p class="gains-sub">
          Nos chauffeurs actifs à Yaoundé et Douala génèrent en moyenne 250 000 à 400 000 FCFA par mois en travaillant à mi-temps.
        </p>
        <ul class="gains-list">
          <li>
            <div class="gains-check">✓</div>
            <span><strong>Paiement immédiat</strong> — vos gains sont crédités après chaque course acceptée.</span>
          </li>
          <li>
            <div class="gains-check">✓</div>
            <span><strong>Zéro frais cachés</strong> — commission fixe et transparente de 13%, jamais plus.</span>
          </li>
          <li>
            <div class="gains-check">✓</div>
            <span><strong>Bonus de fidélité</strong> — plus vous conduisez, plus votre taux de commission baisse.</span>
          </li>
        </ul>
        <a href="sign/?mode=register" class="btn btn-gold">Commencer gratuitement →</a>
      </div>

      <div class="earnings-card">
        <div class="earnings-label">Exemple de gains mensuels</div>
        <div class="earnings-amount">285 000</div>
        <div class="earnings-sub">FCFA · chauffeur actif · Yaoundé</div>

        <div class="earnings-breakdown">
          <div>
            <div class="breakdown-row">
              <span class="breakdown-left">Courses longues</span>
              <span class="breakdown-right">145 000 FCFA</span>
            </div>
            <div class="breakdown-bar">
              <div class="breakdown-fill" style="width:75%"></div>
            </div>
          </div>
          <div>
            <div class="breakdown-row">
              <span class="breakdown-left">Courses courtes</span>
              <span class="breakdown-right">98 000 FCFA</span>
            </div>
            <div class="breakdown-bar">
              <div class="breakdown-fill" style="width:50%"></div>
            </div>
          </div>
          <div>
            <div class="breakdown-row">
              <span class="breakdown-left">Covoiturage</span>
              <span class="breakdown-right">42 000 FCFA</span>
            </div>
            <div class="breakdown-bar">
              <div class="breakdown-fill" style="width:22%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── STEPS ── -->
  <section class="section-steps" id="comment-ca-marche">
    <div class="section-tag">En 4 étapes</div>
    <h2 class="section-title">Démarrez en moins<br>de 24 heures</h2>

    <div class="steps-grid">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-title">Créez votre compte</div>
        <p class="step-desc">Vos informations personnelles et votre mot de passe. 2 minutes chrono.</p>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-title">Soumettez votre KYC</div>
        <p class="step-desc">Photo, CNI et adresse. Tout se fait depuis votre téléphone.</p>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-title">Validation sous 24h</div>
        <p class="step-desc">Notre équipe vérifie votre dossier rapidement et vous notifie.</p>
      </div>
      <div class="step">
        <div class="step-num">4</div>
        <div class="step-title">Conduisez et gagnez</div>
        <p class="step-desc">Activez-vous sur la carte et recevez vos premières courses.</p>
      </div>
    </div>
  </section>

  <!-- ── CTA FINAL ── -->
  <section class="section-cta">
    <h2 class="cta-title">Prêt à transformer<br>votre volant en revenus ?</h2>
    <p class="cta-sub">Rejoignez des centaines de chauffeurs qui gagnent leur vie avec MonTaxi.</p>
    <div class="cta-actions">
      <a href="sign/?mode=register" class="btn btn-gold">
        🚖 Inscrivez-vous — c'est gratuit
      </a>
      <a href="sign/?mode=login" class="btn btn btn-ghost-white">
        Déjà inscrit ? Connexion
      </a>
    </div>
  </section>

  <!-- ── FOOTER ── -->
  <footer>
    <div class="footer-logo">MonTaxi · Cameroun 🇨🇲</div>
    <div class="footer-copy">© 2025 MonTaxi. Tous droits réservés.</div>
  </footer>

  <script>
    // Nav scroll effect
    const nav = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 40);
    });

    // Animate earnings bars on scroll
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.querySelectorAll('.breakdown-fill').forEach(bar => {
            bar.style.width = bar.style.width; // trigger reflow
          });
        }
      });
    }, {
      threshold: .3
    });
    document.querySelectorAll('.earnings-card').forEach(el => observer.observe(el));
  </script>
</body>

</html>