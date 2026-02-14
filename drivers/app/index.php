<?php
// ============================================================
// /app/index.php — Espace chauffeur connecté
// ============================================================

$isDashboard = true;
include('../inc/main.php');

// ── 1. Auth ────────────────────────────────────────────────
if (!isset($_SESSION['id'])) {
  header('Location: /sign?mode=login');
  exit;
}

// ── 2. Récupérer le chauffeur ──────────────────────────────
$stmt = $bdd->prepare("SELECT * FROM chauffeurs WHERE id = ?");
$stmt->execute([$_SESSION['id']]);
$chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chauffeur) {
  session_destroy();
  header('Location: /sign?mode=login');
  exit;
}

// ── 3. Récupérer le KYC ───────────────────────────────────
$stmt = $bdd->prepare("
    SELECT statut, commentaire_admin
    FROM kyc WHERE chauffeur_id = ?
    ORDER BY id DESC LIMIT 1
");
$stmt->execute([$_SESSION['id']]);
$kyc = $stmt->fetch(PDO::FETCH_ASSOC);

// ── 4. Routing selon statut ───────────────────────────────
// Pas de KYC soumis
if (!$kyc || $chauffeur['statut'] === 'inactif') {
  header('Location: /kyc');
  exit;
}
// KYC rejeté
if ($kyc['statut'] === 'rejete') {
  header('Location: /kyc?rejected=1');
  exit;
}

// KYC en attente ou en cours de vérification
$showPending = in_array($kyc['statut'], ['soumis', 'en_cours']);

// ── 5. Récupérer les véhicules si dashboard actif ─────────
$vehicules = [];
if (!$showPending) {
  $stmt = $bdd->prepare("
        SELECT id, marque, modele, immatriculation
        FROM vehicules WHERE chauffeur_id = ? AND actif = 1
        ORDER BY id ASC
    ");
  $stmt->execute([$_SESSION['id']]);
  $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
  <title>MonTaxi — Mon espace chauffeur</title>
  <link rel="shortcut icon" href="<?= $img ?>fav.png" type="image/x-icon">
  <link rel="stylesheet" href="<?= $css ?>polices.css">
  <link rel="stylesheet" href="<?= $css ?>errorOrSuccessBox.css">
  <link rel="stylesheet" href="<?= $css ?>popupsBox.css">
  <link rel="manifest" href="/manifest2.json">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    :root {
      --noir: #0d0d0d;
      --blanc: #ffffff;
      --gris-bg: #f5f4f0;
      --gris-clair: #e8e6e1;
      --or: #f5cb17;
      --or-clair: #f0d98a;
      --texte: #1a1a1a;
      --texte-doux: #6b6b6b;
      --vert: #22c55e;
      --rouge: #ef4444;
      --bleu: #3b82f6;
      --sh: 0 4px 20px rgba(0, 0, 0, .1);
    }

    html,
    body {
      height: 100%;
      overflow: hidden;
      font-family: Po02;
      background: var(--gris-bg);
      color: var(--texte)
    }

    .app {
      display: flex;
      flex-direction: column;
      height: 100vh;
      position: relative
    }

    /* ── TOP BAR ── */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      background: var(--blanc);
      border-bottom: 1px solid var(--gris-clair);
      z-index: 50;
      flex-shrink: 0
    }

    .menu-btn {
      font-size: 1.5rem;
      cursor: pointer;
      margin-right: 12px;
      color: var(--noir)
    }

    .tb-left {
      display: flex;
      align-items: center;
      gap: 12px
    }

    .logo {
      font-family: Po01;
      font-size: 1.1rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
      color: var(--texte)
    }

    .logo img {
      width: 180px
    }

    .driver-info {
      display: flex;
      flex-direction: column
    }

    .driver-name {
      font-family: Po01;
      font-size: .9rem;
      font-weight: 700
    }

    .driver-sub {
      font-size: .72rem;
      color: var(--texte-doux)
    }

    .tb-right {
      display: flex;
      align-items: center;
      gap: 10px
    }

    /* Toggle en ligne */
    .online-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--gris-bg);
      border-radius: 50px;
      padding: 6px 14px 6px 10px;
      cursor: pointer;
      border: 1.5px solid var(--gris-clair);
      transition: all .25s
    }

    .online-toggle.active {
      background: #ecfdf5;
      border-color: #86efac
    }

    .toggle-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #d1d5db;
      transition: background .25s
    }

    .online-toggle.active .toggle-dot {
      background: var(--vert);
      animation: pulse 1.5s infinite
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
        transform: scale(1)
      }

      50% {
        opacity: .6;
        transform: scale(1.3)
      }
    }

    .toggle-label {
      font-size: .8rem;
      font-weight: 500;
      color: var(--texte-doux)
    }

    .online-toggle.active .toggle-label {
      color: #166534
    }

    .notif-btn {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--gris-bg);
      border: 1px solid var(--gris-clair);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 1.1rem;
      position: relative
    }

    .notif-badge {
      position: absolute;
      top: -2px;
      right: -2px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: var(--rouge);
      color: var(--blanc);
      font-size: .55rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center
    }

    /* ── SIDE MENU ── */
    .side-menu {
      position: fixed;
      top: 0;
      left: -280px;
      width: 280px;
      height: 100%;
      background: var(--blanc);
      z-index: 100;
      transition: left .3s ease;
      box-shadow: 2px 0 20px rgba(0, 0, 0, .1);
      display: flex;
      flex-direction: column
    }

    .side-menu.open {
      left: 0
    }

    .menu-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .5);
      z-index: 90;
      display: none
    }

    .menu-overlay.open {
      display: block
    }

    .menu-header {
      padding: 24px 20px;
      background: var(--gris-bg);
      border-bottom: 1px solid var(--gris-clair)
    }

    .menu-user {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px
    }

    .menu-avatar {
      width: 48px;
      height: 48px;
      background: var(--blanc);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem
    }

    .menu-name {
      font-family: Po01;
      font-weight: 700;
      font-size: 1rem
    }

    .menu-stat {
      font-size: .78rem;
      color: var(--texte-doux)
    }

    .menu-content {
      padding: 20px;
      flex: 1;
      overflow-y: auto
    }

    .menu-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      color: var(--texte);
      text-decoration: none;
      font-weight: 500;
      border-bottom: 1px solid var(--gris-bg)
    }

    .menu-item:last-child {
      border-bottom: none
    }

    .menu-section {
      margin-bottom: 24px
    }

    .menu-section label {
      display: block;
      font-size: .75rem;
      color: var(--texte-doux);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: .5px
    }

    select.vehicle-select {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid var(--gris-clair);
      background: var(--gris-bg);
      font-family: Po02;
      font-size: .9rem
    }

    .close-menu {
      position: absolute;
      top: 16px;
      right: 16px;
      background: none;
      border: none;
      font-size: 1.2rem;
      cursor: pointer
    }

    /* ── MAP ── */
    #main-map {
      flex: 1;
      z-index: 1;
      position: relative
    }

    /* ── PENDING (KYC en attente) ── */
    .pending-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px
    }

    .pending-card {
      background: var(--blanc);
      border-radius: 24px;
      padding: 40px 32px;
      max-width: 420px;
      width: 100%;
      text-align: center;
      border: 1px solid var(--gris-clair);
      box-shadow: var(--sh)
    }

    .pending-icon {
      font-size: 3.5rem;
      margin-bottom: 16px;
      animation: bounce .8s ease infinite alternate
    }

    @keyframes bounce {
      from {
        transform: translateY(0)
      }

      to {
        transform: translateY(-8px)
      }
    }

    .pending-title {
      font-family: Po01;
      font-size: 1.5rem;
      font-weight: 800;
      margin-bottom: 10px
    }

    .pending-sub {
      font-size: .9rem;
      color: var(--texte-doux);
      line-height: 1.7;
      margin-bottom: 24px
    }

    .pending-steps {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-bottom: 20px
    }

    .ps-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--gris-clair)
    }

    .ps-dot.done {
      background: var(--vert)
    }

    .ps-dot.active {
      background: var(--or);
      animation: pulse 1.5s infinite
    }

    .info-box {
      background: var(--gris-bg);
      border-radius: 14px;
      padding: 16px;
      border: 1px solid var(--gris-clair);
      text-align: left;
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 20px
    }

    .info-row {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: .84rem
    }

    .btn-check {
      display: block;
      width: 100%;
      padding: 13px;
      background: var(--noir);
      color: var(--blanc);
      border: none;
      border-radius: 12px;
      font-family: Po02;
      font-size: .95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s
    }

    .btn-check:hover {
      background: #222
    }

    .btn-logout {
      display: block;
      width: 100%;
      padding: 11px;
      background: transparent;
      color: var(--texte-doux);
      border: 1px solid var(--gris-clair);
      border-radius: 12px;
      font-family: Po02;
      font-size: .88rem;
      cursor: pointer;
      margin-top: 10px;
      transition: all .2s;
      text-decoration: none;
      text-align: center
    }

    .btn-logout:hover {
      border-color: var(--rouge);
      color: var(--rouge)
    }

    /* ── BOTTOM SHEET ── */
    .bottom-sheet {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 40;
      background: var(--blanc);
      border-radius: 22px 22px 0 0;
      box-shadow: 0 -4px 30px rgba(0, 0, 0, .12);
      transition: transform .35s cubic-bezier(.25, .8, .25, 1);
      max-height: 80vh;
      overflow: hidden;
      display: flex;
      flex-direction: column
    }

    .sheet-handle {
      width: 40px;
      height: 4px;
      border-radius: 2px;
      background: var(--gris-clair);
      margin: 12px auto 0;
      cursor: grab;
      flex-shrink: 0
    }

    .sheet-header {
      padding: 14px 20px 0;
      flex-shrink: 0
    }

    .sheet-title {
      font-family: Po01;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 4px
    }

    .sheet-subtitle {
      font-size: .78rem;
      color: var(--texte-doux)
    }

    .sheet-content {
      flex: 1;
      overflow-y: auto;
      padding: 16px 20px 24px
    }

    .sheet-mode {
      display: none
    }

    .sheet-mode.active {
      display: block
    }

    /* ── STATS ── */
    .quick-stats {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 10px;
      margin-bottom: 16px
    }

    .qs {
      background: var(--gris-bg);
      border-radius: 12px;
      padding: 12px;
      text-align: center;
      border: 1px solid var(--gris-clair)
    }

    .qs-val {
      font-family: Po01;
      font-size: 1.1rem;
      font-weight: 800
    }

    .qs-label {
      font-size: .68rem;
      color: var(--texte-doux);
      margin-top: 2px
    }

    /* ── COURSE CARD ── */
    .course-card {
      background: var(--gris-bg);
      border-radius: 16px;
      border: 1.5px solid var(--gris-clair);
      padding: 16px;
      margin-bottom: 12px;
      transition: transform .2s, box-shadow .2s
    }

    .course-card:last-child {
      margin-bottom: 0
    }

    .course-card.new-ride {
      border-color: var(--or);
      background: #fffbeb;
      animation: slideIn .4s ease
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(12px)
      }

      to {
        opacity: 1;
        transform: none
      }
    }

    .cc-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px
    }

    .cc-client {
      display: flex;
      align-items: center;
      gap: 10px
    }

    .cc-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--gris-clair);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0
    }

    .cc-name {
      font-weight: 600;
      font-size: .9rem
    }

    .cc-note {
      font-size: .72rem;
      color: var(--texte-doux)
    }

    .cc-dist {
      background: var(--noir);
      color: var(--or);
      border-radius: 50px;
      padding: 4px 12px;
      font-size: .75rem;
      font-weight: 700
    }

    .cc-route {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-bottom: 12px;
      padding: 10px 12px;
      background: var(--blanc);
      border-radius: 10px;
      border: 1px solid var(--gris-clair)
    }

    .cc-point {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: .82rem
    }

    .cc-point-ico {
      font-size: .9rem;
      width: 20px;
      text-align: center
    }

    .cc-dashed {
      width: 1px;
      height: 16px;
      border-left: 2px dashed var(--gris-clair);
      margin-left: 9px
    }

    .cc-meta {
      display: flex;
      gap: 12px;
      margin-bottom: 12px
    }

    .cc-meta-item {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: .78rem;
      color: var(--texte-doux)
    }

    .cc-covoiturage-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #eff6ff;
      color: #1d4ed8;
      border-radius: 50px;
      padding: 3px 10px;
      font-size: .72rem;
      font-weight: 600;
      margin-bottom: 12px
    }

    .cc-actions {
      display: flex;
      gap: 8px
    }

    .btn-accept {
      flex: 2;
      padding: 12px;
      background: var(--noir);
      color: var(--blanc);
      border: none;
      border-radius: 10px;
      font-family: Po02;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, transform .2s
    }

    .btn-accept:hover {
      background: #222;
      transform: scale(1.02)
    }

    .btn-decline {
      flex: 1;
      padding: 12px;
      background: transparent;
      color: var(--texte);
      border: 1.5px solid var(--gris-clair);
      border-radius: 10px;
      font-family: Po02;
      font-size: .88rem;
      cursor: pointer;
      transition: all .2s
    }

    .btn-decline:hover {
      border-color: var(--rouge);
      color: var(--rouge)
    }

    /* ── ACTIVE RIDE ── */
    .active-ride {
      background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%);
      border-radius: 16px;
      padding: 20px;
      color: var(--blanc);
      margin-bottom: 12px
    }

    .ar-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px
    }

    .ar-status {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, .1);
      border-radius: 50px;
      padding: 5px 14px;
      font-size: .78rem;
      font-weight: 600
    }

    .ar-status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--or);
      animation: pulse 1.5s infinite
    }

    .ar-price {
      font-family: Po01;
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--or)
    }

    .ar-client {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px
    }

    .ar-avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem
    }

    .ar-client-name {
      font-weight: 600
    }

    .ar-client-tel {
      font-size: .75rem;
      color: rgba(255, 255, 255, .55)
    }

    .ar-call {
      margin-left: auto;
      width: 36px;
      height: 36px;
      background: var(--or);
      color: var(--noir);
      border: none;
      border-radius: 50%;
      cursor: pointer;
      font-size: 1rem;
      display: flex;
      align-items: center;
      justify-content: center
    }

    .ar-route {
      background: rgba(255, 255, 255, .07);
      border-radius: 12px;
      padding: 12px;
      margin-bottom: 16px
    }

    .ar-route-row {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: .82rem;
      color: rgba(255, 255, 255, .8)
    }

    .ar-route-ico {
      font-size: .9rem;
      width: 20px
    }

    .ar-dashed {
      width: 1px;
      height: 14px;
      border-left: 2px dashed rgba(255, 255, 255, .2);
      margin-left: 9px;
      margin-top: 2px;
      margin-bottom: 2px
    }

    .ar-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px
    }

    .ar-btn {
      padding: 12px;
      border: none;
      border-radius: 10px;
      font-family: Po02;
      font-size: .82rem;
      font-weight: 600;
      cursor: pointer;
      transition: all .2s
    }

    .ar-btn-arrived {
      background: var(--vert);
      color: var(--blanc)
    }

    .ar-btn-arrived:hover {
      background: #16a34a
    }

    .ar-btn-finish {
      background: var(--or);
      color: var(--noir)
    }

    .ar-btn-finish:hover {
      background: #d4b014
    }

    .ar-btn-covoiturage {
      background: rgba(59, 130, 246, .2);
      color: #93c5fd;
      grid-column: 1/-1
    }

    .ar-btn-covoiturage:hover {
      background: rgba(59, 130, 246, .35)
    }

    .covoiturage-list {
      margin-top: 10px;
      display: flex;
      flex-direction: column;
      gap: 6px
    }

    .cov-item {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, .06);
      border-radius: 8px;
      padding: 8px 10px;
      font-size: .78rem;
      color: rgba(255, 255, 255, .75)
    }

    .cov-status-badge {
      margin-left: auto;
      font-size: .68rem;
      padding: 2px 8px;
      border-radius: 50px
    }

    .cov-status-badge.en-route {
      background: rgba(34, 197, 94, .2);
      color: #86efac
    }

    .cov-status-badge.attente {
      background: rgba(201, 168, 76, .2);
      color: var(--or-clair)
    }

    /* No rides */
    .no-rides {
      text-align: center;
      padding: 30px 20px
    }

    .no-rides-icon {
      font-size: 2.5rem;
      margin-bottom: 12px
    }

    .no-rides-title {
      font-family: Po01;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 6px
    }

    .no-rides-sub {
      font-size: .82rem;
      color: var(--texte-doux);
      line-height: 1.6
    }

    /* Rerouting toast */
    .reroute-toast {
      position: absolute;
      top: 70px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 45;
      background: #1d4ed8;
      color: var(--blanc);
      border-radius: 50px;
      padding: 8px 20px;
      font-family: Po01;
      font-size: .88rem;
      font-weight: 700;
      display: none;
      align-items: center;
      gap: 8px;
      box-shadow: var(--sh);
      white-space: nowrap
    }

    .reroute-toast.show {
      display: flex;
      animation: fadeInTop .3s ease
    }

    @keyframes fadeInTop {
      from {
        opacity: 0;
        transform: translateX(-50%) translateY(-8px)
      }

      to {
        opacity: 1;
        transform: translateX(-50%) translateY(0)
      }
    }

    .map-fab {
      position: absolute;
      bottom: calc(var(--sheet-h, 200px) + 16px);
      right: 16px;
      z-index: 35;
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: var(--blanc);
      border: 1px solid var(--gris-clair);
      box-shadow: var(--sh);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      cursor: pointer;
      transition: transform .2s
    }

    .map-fab:hover {
      transform: scale(1.08)
    }

    .ride-timer {
      position: absolute;
      top: 70px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 45;
      background: var(--noir);
      color: var(--blanc);
      border-radius: 50px;
      padding: 8px 20px;
      font-family: Po01;
      font-size: 1.1rem;
      font-weight: 800;
      display: none;
      align-items: center;
      gap: 8px;
      box-shadow: var(--sh)
    }

    .timer-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--or);
      animation: pulse 1s infinite
    }

    .cc-covoiturage-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px
    }

    @media(min-width:600px) {
      .bottom-sheet {
        max-width: 400px;
        left: auto;
        right: 16px;
        bottom: 16px;
        border-radius: 20px;
        box-shadow: var(--sh)
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
  <div class="app">

    <?php if ($showPending): ?>
      <!-- ══════════ KYC EN ATTENTE ══════════ -->
      <div class="topbar">
        <div class="tb-left">
          <a href="/" class="logo"><img src="<?= $img ?>logo2.png"></a>
        </div>
        <a href="/logout" style="font-size:.82rem;color:var(--texte-doux);text-decoration:none">Déconnexion</a>
      </div>
      <div class="pending-wrap">
        <div class="pending-card">
          <div class="pending-icon">⏳</div>
          <h2 class="pending-title">Dossier en vérification</h2>
          <p class="pending-sub">
            Votre KYC a été soumis le <strong><?= date('d/m/Y', strtotime($kyc['soumis_le'] ?? 'now')) ?></strong>.
            Notre équipe le vérifie sous 24 à 48 heures.
          </p>
          <div class="pending-steps">
            <div class="ps-dot done" title="Inscription"></div>
            <div class="ps-dot done" title="KYC soumis"></div>
            <div class="ps-dot active" title="Vérification"></div>
            <div class="ps-dot" title="Activé"></div>
          </div>
          <div class="info-box">
            <div class="info-row">⏱️ <span>Délai moyen : <strong>24 à 48 heures</strong></span></div>
            <div class="info-row">📧 <span>Notification par email à la validation</span></div>
            <div class="info-row">🚖 <span>Vous démarrerez dès l'approbation</span></div>
          </div>
          <button class="btn-check" onclick="checkKycStatus()">🔄 Vérifier le statut</button>
          <a href="/logout" class="btn-logout">Se déconnecter</a>
        </div>
      </div>
      <script>
        async function checkKycStatus() {
          const btn = document.querySelector('.btn-check');
          btn.textContent = 'Vérification...';
          btn.disabled = true;
          try {
            const res = await fetch('/auth/kyc.php?action=status');
            const data = await res.json();
            if (data.code === 0 && data.kyc) {
              if (data.kyc.statut === 'approuve') {
                window.location.reload();
              } else if (data.kyc.statut === 'rejete') {
                window.location.href = '/kyc?rejected=1';
              } else {
                btn.textContent = '⏳ Toujours en cours…';
                setTimeout(() => {
                  btn.textContent = '🔄 Vérifier le statut';
                  btn.disabled = false;
                }, 3000);
                return;
              }
            }
          } catch (e) {
            btn.textContent = '🔄 Vérifier le statut';
            btn.disabled = false;
          }
        }
      </script>

    <?php else: ?>
      <!-- ══════════ DASHBOARD ACTIF ══════════ -->

      <!-- TOP BAR -->
      <div class="topbar">
        <div class="tb-left">
          <div class="menu-btn" onclick="toggleMenu()">☰</div>
          <div>
            <a href="/" class="logo"><img src="<?= $img ?>logo2.png"></a>
            <div class="driver-info">
              <div class="driver-name"><?= htmlspecialchars($chauffeur['prenom'] . ' ' . $chauffeur['nom']) ?></div>
              <div class="driver-sub" id="topbar-vehicle">
                <?= count($vehicules) ? htmlspecialchars($vehicules[0]['marque'] . ' ' . $vehicules[0]['modele'] . ' · ' . $vehicules[0]['immatriculation']) : 'Aucun véhicule' ?>
              </div>
            </div>
          </div>
        </div>
        <div class="tb-right">
          <div class="online-toggle" id="online-toggle" onclick="toggleOnline()">
            <div class="toggle-dot"></div>
            <span class="toggle-label" id="toggle-label">OffLine</span>
          </div>
          <div class="notif-btn" onclick="openNotifs()">
            🔔
            <div class="notif-badge" id="notif-badge" style="display:none"></div>
          </div>
        </div>
      </div>

      <!-- SIDE MENU -->
      <div class="menu-overlay" id="menu-overlay" onclick="toggleMenu()"></div>
      <div class="side-menu" id="side-menu">
        <div class="menu-header">
          <button class="close-menu" onclick="toggleMenu()">✕</button>
          <div class="menu-user">
            <div class="menu-avatar">👤</div>
            <div>
              <div class="menu-name"><?= htmlspecialchars($chauffeur['prenom'] . ' ' . $chauffeur['nom']) ?></div>
              <div class="menu-stat">⭐ <?= number_format((float)$chauffeur['note_moyenne'], 1) ?> · <?= $chauffeur['nombre_courses'] ?> courses</div>
            </div>
          </div>
        </div>
        <div class="menu-content">
          <?php if ($vehicules): ?>
            <div class="menu-section">
              <label>Véhicule actif</label>
              <select class="vehicle-select" id="vehicle-select" onchange="changeVehicle(this)">
                <?php foreach ($vehicules as $v): ?>
                  <option value="<?= $v['id'] ?>" data-label="<?= htmlspecialchars($v['marque'] . ' ' . $v['modele'] . ' · ' . $v['immatriculation']) ?>">
                    <?= htmlspecialchars($v['marque'] . ' ' . $v['modele'] . ' · ' . $v['immatriculation']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <a href="#" class="menu-item" onclick="openMenuModal('profil'); toggleMenu()">
            👤 Mon profil
          </a>
          <a href="#" class="menu-item" onclick="openMenuModal('securite'); toggleMenu()">
            🔒 Sécurité
          </a>
          <a href="#" class="menu-item">🚙 Gérer mes véhicules</a>
          <a href="#" class="menu-item">💰 Mes revenus</a>
          <a href="#" class="menu-item">📜 Historique des courses</a>
          <a href="/logout" class="menu-item" style="color:var(--rouge)">🚪 Déconnexion</a>
        </div>
      </div>

      <!-- MAP -->
      <div id="main-map"></div>

      <!-- FAB recentrer -->
      <div class="map-fab" onclick="recenterMap()" title="Recentrer">📍</div>

      <!-- Timer acceptation -->
      <div class="ride-timer" id="ride-timer">
        <div class="timer-dot"></div>
        <span id="timer-count">30s</span>
      </div>

      <!-- Toast recalcul itinéraire -->
      <div class="reroute-toast" id="reroute-toast">🔄 Recalcul de l'itinéraire…</div>

      <!-- BOTTOM SHEET -->
      <div class="bottom-sheet" id="bottom-sheet">
        <div class="sheet-handle"></div>
        <div class="sheet-header">
          <div class="sheet-title" id="sheet-title">Tableau de bord</div>
          <div class="sheet-subtitle" id="sheet-subtitle">Activez-vous pour recevoir des courses</div>
        </div>
        <div class="sheet-content">

          <!-- MODE offline -->
          <div class="sheet-mode active" id="mode-offline">
            <div class="quick-stats">
              <div class="qs">
                <div class="qs-val" id="stat-gains">–</div>
                <div class="qs-label">FCFA aujourd'hui</div>
              </div>
              <div class="qs">
                <div class="qs-val" id="stat-courses">–</div>
                <div class="qs-label">Courses</div>
              </div>
              <div class="qs">
                <div class="qs-val" id="stat-note">–</div>
                <div class="qs-label">⭐ Note</div>
              </div>
            </div>
            <div class="no-rides">
              <div class="no-rides-icon">🚖</div>
              <div class="no-rides-title">Vous êtes hors ligne</div>
              <div class="no-rides-sub">Activez le mode "En ligne" pour recevoir des courses dans votre zone.</div>
            </div>
          </div>

          <!-- MODE waiting -->
          <div class="sheet-mode" id="mode-waiting">
            <div class="quick-stats">
              <div class="qs">
                <div class="qs-val" id="stat-gains2">–</div>
                <div class="qs-label">FCFA aujourd'hui</div>
              </div>
              <div class="qs">
                <div class="qs-val" id="stat-courses2">–</div>
                <div class="qs-label">Courses</div>
              </div>
              <div class="qs">
                <div class="qs-val" id="stat-note2">–</div>
                <div class="qs-label">⭐ Note</div>
              </div>
            </div>
            <div id="rides-list"></div>
          </div>

          <!-- MODE en-route (vers le client) -->
          <div class="sheet-mode" id="mode-en-route">
            <div class="active-ride">
              <div class="ar-header">
                <div class="ar-status">
                  <div class="ar-status-dot"></div>En route vers le client
                </div>
                <div class="ar-price" id="ar-price"></div>
              </div>
              <div class="ar-client">
                <div class="ar-avatar">👤</div>
                <div>
                  <div class="ar-client-name" id="ar-client-name"></div>
                  <div class="ar-client-tel" id="ar-client-tel"></div>
                </div>
                <button class="ar-call" id="ar-call-btn">📞</button>
              </div>
              <div class="ar-route">
                <div class="ar-route-row"><span class="ar-route-ico">🟡</span><span id="ar-depart"></span></div>
                <div class="ar-dashed"></div>
                <div class="ar-route-row"><span class="ar-route-ico">🔴</span><span id="ar-arrivee"></span></div>
              </div>
              <div class="ar-actions">
                <button class="ar-btn ar-btn-arrived" onclick="clientMonteDansVoiture()">✅ Client à bord</button>
                <button class="ar-btn" style="background:rgba(239,68,68,.15);color:#fca5a5" onclick="cancelRide()">❌ Annuler</button>
              </div>
            </div>
            <div id="covoiturage-section" style="display:none">
              <div class="cc-covoiturage-badge">🚦 Covoiturage actif</div>
              <div class="covoiturage-list" id="covoiturage-list"></div>
            </div>
          </div>

          <!-- MODE in-ride (client à bord) -->
          <div class="sheet-mode" id="mode-in-ride">
            <div class="active-ride">
              <div class="ar-header">
                <div class="ar-status">
                  <div class="ar-status-dot"></div>Course en cours
                </div>
                <div class="ar-price" id="ir-price"></div>
              </div>
              <div class="ar-client">
                <div class="ar-avatar">👤</div>
                <div>
                  <div class="ar-client-name" id="ir-client-name"></div>
                  <div class="ar-client-tel" id="ir-client-tel"></div>
                </div>
                <button class="ar-call" onclick="callClient()">📞</button>
              </div>
              <div class="ar-route">
                <div class="ar-route-row"><span class="ar-route-ico">🟡</span><span id="ir-depart"></span></div>
                <div class="ar-dashed"></div>
                <div class="ar-route-row"><span class="ar-route-ico">🏁</span><span id="ir-arrivee"></span></div>
              </div>
              <div class="ar-actions">
                <button class="ar-btn ar-btn-finish" onclick="finishRide()">🏁 Terminer la course</button>
                <button class="ar-btn ar-btn-covoiturage" id="btn-cov" style="display:none" onclick="acceptCovoiturage()">
                  + Accepter un covoiturage
                </button>
              </div>
            </div>
            <div id="in-ride-cov-section" style="display:none">
              <div class="cc-covoiturage-badge" style="margin-top:10px">👥 <span id="cov-count">0</span> passager(s) à bord</div>
              <div class="covoiturage-list" id="in-ride-cov-list"></div>
            </div>
          </div>

        </div>
      </div>
      <span class="mio ussdCall" style="padding: 8px;
          border-radius: 50%;
          background: #fff;
          color: gold;
          cursor: pointer;
          position: fixed;
          bottom: 30px;
          border: solid 1px #0001;
          right: 10px;
          z-index: 40;" onclick="document.querySelector('.ussd-simulator').classList.toggle('active');">phone</span>

      <div class="ussd-simulator">
        <div class="dialer-screen">
          <input type="text" id="ussdInput" readonly value="*237#"><span class="mio" onclick="clearLast()">backspace</span>
        </div>
        <div class="keypad">
          <button onclick="pressKey('1')">1</button><button onclick="pressKey('2')">2</button><button onclick="pressKey('3')">3</button>
          <button onclick="pressKey('4')">4</button><button onclick="pressKey('5')">5</button><button onclick="pressKey('6')">6</button>
          <button onclick="pressKey('7')">7</button><button onclick="pressKey('8')">8</button><button onclick="pressKey('9')">9</button>
          <button onclick="pressKey('*')">*</button><button onclick="pressKey('0')">0</button><button onclick="pressKey('#')">#</button>
        </div>
        <button class="btn-call" onclick="startUssd()"><i class="mio">call</i></button>
      </div>

      <div id="ussdModal" class="ussd-modal">
        <div class="ussd-content">
          <p id="ussdResponse">Chargement...</p>
          <input type="text" id="userChoice" placeholder="Répondre ici...">
          <div class="ussd-actions">
            <button onclick="closeUssd()">ANNULER</button>
            <button onclick="sendUssdChoice()">ENVOYER</button>
          </div>
        </div>
      </div>

      <!-- ══ MODAL PROFIL CHAUFFEUR ══ -->
      <div class="menu-overlay" id="modal-profil-bg" onclick="closeMenuModal('profil')" style="z-index:200"></div>
      <div id="modal-profil" style="
  position:fixed;bottom:0;left:0;right:0;z-index:201;
  background:var(--blanc);border-radius:22px 22px 0 0;
  box-shadow:0 -8px 40px rgba(0,0,0,.18);
  max-height:90dvh;display:none;flex-direction:column;
  animation:slideUp .3s ease;">

        <div style="width:36px;height:4px;background:var(--gris-clair);border-radius:2px;margin:12px auto 0;flex-shrink:0"></div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px 0;flex-shrink:0">
          <div style="font-family:Po01;font-weight:700;font-size:1.05rem">Mon profil</div>
          <button onclick="closeMenuModal('profil')" style="background:var(--gris-bg);border:1px solid var(--gris-clair);border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center">✕</button>
        </div>
        <div style="flex:1;overflow-y:auto;padding:16px 20px 36px">

          <!-- Photo -->
          <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
            <div id="mp-avatar" style="width:68px;height:68px;border-radius:50%;background:var(--noir);
        color:var(--or);display:flex;align-items:center;justify-content:center;
        font-family:Po01;font-weight:800;font-size:1.5rem;overflow:hidden;
        flex-shrink:0;border:3px solid var(--gris-clair)">
              <?= htmlspecialchars($chauffeur['prenom'][0] ?? '?') ?>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px">
              <label style="padding:8px 14px;border-radius:50px;font-size:.8rem;font-weight:500;
          background:var(--blanc);border:1.5px solid var(--gris-clair);cursor:pointer;
          display:inline-flex;align-items:center;gap:6px">
                📷 Changer
                <input type="file" id="mp-photo-input" accept="image/jpeg,image/png,image/webp"
                  style="display:none" onchange="mpPreviewPhoto(this)">
              </label>
              <button onclick="mpDeletePhoto()" style="padding:8px 14px;border-radius:50px;
          font-size:.8rem;background:var(--blanc);border:1.5px solid #fecaca;
          color:var(--rouge);cursor:pointer">🗑 Supprimer</button>
            </div>
          </div>

          <div id="mp-err" style="display:none;background:#fef2f2;border:1px solid #fecaca;
      border-radius:10px;padding:9px 13px;font-size:.8rem;color:var(--rouge);margin-bottom:12px"></div>
          <div id="mp-ok" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;
      border-radius:10px;padding:9px 13px;font-size:.8rem;color:#15803d;margin-bottom:12px"></div>

          <div style="margin-bottom:14px">
            <label style="display:block;font-size:.7rem;font-weight:600;color:var(--texte-doux);
        text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Prénom</label>
            <input id="mp-prenom" type="text" value="<?= htmlspecialchars($chauffeur['prenom']) ?>"
              style="width:100%;padding:12px 14px;border-radius:12px;border:2px solid var(--gris-clair);
        font-size:.9rem;background:var(--gris-bg);outline:none;font-family:Po02;
        transition:border-color .2s" onfocus="this.style.borderColor='var(--noir)'"
              onblur="this.style.borderColor='var(--gris-clair)'" />
          </div>
          <div style="margin-bottom:20px">
            <label style="display:block;font-size:.7rem;font-weight:600;color:var(--texte-doux);
        text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Nom</label>
            <input id="mp-nom" type="text" value="<?= htmlspecialchars($chauffeur['nom']) ?>"
              style="width:100%;padding:12px 14px;border-radius:12px;border:2px solid var(--gris-clair);
        font-size:.9rem;background:var(--gris-bg);outline:none;font-family:Po02;
        transition:border-color .2s" onfocus="this.style.borderColor='var(--noir)'"
              onblur="this.style.borderColor='var(--gris-clair)'" />
          </div>

          <button id="mp-save-btn" onclick="mpSave()" style="width:100%;padding:14px;border:none;
      border-radius:13px;background:var(--noir);color:var(--blanc);font-family:Po02;
      font-size:.9rem;font-weight:600;cursor:pointer;transition:all .2s;
      display:flex;align-items:center;justify-content:center;gap:8px">
            💾 Enregistrer
          </button>
        </div>
      </div>

      <!-- ══ MODAL SÉCURITÉ CHAUFFEUR ══ -->
      <div class="menu-overlay" id="modal-securite-bg" onclick="closeMenuModal('securite')" style="z-index:200"></div>
      <div id="modal-securite" style="
  position:fixed;bottom:0;left:0;right:0;z-index:201;
  background:var(--blanc);border-radius:22px 22px 0 0;
  box-shadow:0 -8px 40px rgba(0,0,0,.18);
  max-height:90dvh;display:none;flex-direction:column;">

        <div style="width:36px;height:4px;background:var(--gris-clair);border-radius:2px;margin:12px auto 0;flex-shrink:0"></div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px 0;flex-shrink:0">
          <div style="font-family:Po01;font-weight:700;font-size:1.05rem">Sécurité</div>
          <button onclick="closeMenuModal('securite')" style="background:var(--gris-bg);border:1px solid var(--gris-clair);border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center">✕</button>
        </div>
        <div style="flex:1;overflow-y:auto;padding:16px 20px 36px">

          <!-- Mot de passe -->
          <div style="font-family:Po01;font-size:.85rem;font-weight:700;margin-bottom:14px;
      padding-bottom:10px;border-bottom:1px solid var(--gris-clair)">
            🔑 Mot de passe
          </div>

          <div id="ms-err" style="display:none;background:#fef2f2;border:1px solid #fecaca;
      border-radius:10px;padding:9px 13px;font-size:.8rem;color:var(--rouge);margin-bottom:12px"></div>
          <div id="ms-ok" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;
      border-radius:10px;padding:9px 13px;font-size:.8rem;color:#15803d;margin-bottom:12px"></div>

          <?php foreach (
            [
              ['ms-old',     'Mot de passe actuel',          '••••••••'],
              ['ms-new',     'Nouveau mot de passe',          'Minimum 8 caractères'],
              ['ms-confirm', 'Confirmer le nouveau',          '••••••••'],
            ] as [$id, $label, $ph]
          ): ?>
            <div style="margin-bottom:14px">
              <label style="display:block;font-size:.7rem;font-weight:600;color:var(--texte-doux);
        text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px"><?= $label ?></label>
              <input id="<?= $id ?>" type="password" placeholder="<?= $ph ?>"
                style="width:100%;padding:12px 14px;border-radius:12px;border:2px solid var(--gris-clair);
        font-size:.9rem;background:var(--gris-bg);outline:none;font-family:Po02;
        transition:border-color .2s" onfocus="this.style.borderColor='var(--noir)'"
                onblur="this.style.borderColor='var(--gris-clair)'"
                <?= $id === 'ms-new' ? 'oninput="msCheckStrength(this.value)"' : '' ?> />
            </div>
          <?php endforeach; ?>

          <!-- Barre force -->
          <div style="display:flex;gap:4px;margin-bottom:4px">
            <?php for ($i = 1; $i <= 4; $i++): ?>
              <div id="ms-sb<?= $i ?>" style="flex:1;height:3px;border-radius:2px;background:var(--gris-clair);transition:background .2s"></div>
            <?php endfor; ?>
          </div>
          <div id="ms-strength" style="font-size:.72rem;color:var(--texte-doux);margin-bottom:16px"></div>

          <button id="ms-save-btn" onclick="msSavePassword()" style="width:100%;padding:14px;
      border:none;border-radius:13px;background:var(--noir);color:var(--blanc);
      font-family:Po02;font-size:.9rem;font-weight:600;cursor:pointer;
      display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:24px">
            🔒 Changer le mot de passe
          </button>

          <!-- 2FA -->
          <div style="font-family:Po01;font-size:.85rem;font-weight:700;margin-bottom:14px;
      padding-bottom:10px;border-bottom:1px solid var(--gris-clair)">
            🛡️ Double authentification (2FA)
          </div>

          <div id="tfa-status-wrap">
            <!-- Rempli dynamiquement -->
          </div>

          <!-- Zone setup 2FA (cachée par défaut) -->
          <div id="tfa-setup-wrap" style="display:none">
            <div style="font-size:.82rem;color:var(--texte-doux);margin-bottom:12px;line-height:1.6">
              Scannez ce QR code avec <strong>Google Authenticator</strong>, <strong>Authy</strong>
              ou toute application TOTP.
            </div>
            <div style="text-align:center;margin-bottom:14px">
              <img id="tfa-qr" src="" style="border-radius:12px;border:1px solid var(--gris-clair)" width="160" height="160" alt="QR Code 2FA" />
            </div>
            <div style="margin-bottom:14px">
              <label style="display:block;font-size:.7rem;font-weight:600;color:var(--texte-doux);
          text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">
                Code de vérification (6 chiffres)
              </label>
              <input id="tfa-code" type="text" inputmode="numeric" maxlength="6"
                placeholder="000000"
                style="width:100%;padding:12px 14px;border-radius:12px;border:2px solid var(--gris-clair);
          font-size:1.2rem;letter-spacing:.3em;text-align:center;background:var(--gris-bg);
          outline:none;font-family:Po01" onfocus="this.style.borderColor='var(--noir)'"
                onblur="this.style.borderColor='var(--gris-clair)'" />
            </div>
            <div id="tfa-err" style="display:none;background:#fef2f2;border:1px solid #fecaca;
        border-radius:10px;padding:9px 13px;font-size:.8rem;color:var(--rouge);margin-bottom:12px"></div>
            <div style="display:flex;gap:8px">
              <button onclick="tfaActivate()" style="flex:2;padding:13px;border:none;border-radius:12px;
          background:var(--noir);color:var(--blanc);font-family:Po02;font-size:.88rem;
          font-weight:600;cursor:pointer">✅ Activer</button>
              <button onclick="tfaCancelSetup()" style="flex:1;padding:13px;border:1.5px solid var(--gris-clair);
          border-radius:12px;background:transparent;font-family:Po02;font-size:.88rem;cursor:pointer">
                Annuler</button>
            </div>
          </div>

          <!-- Zone désactivation 2FA -->
          <div id="tfa-disable-wrap" style="display:none">
            <div style="font-size:.82rem;color:var(--texte-doux);margin-bottom:12px">
              Entrez le code de votre application pour confirmer la désactivation.
            </div>
            <input id="tfa-disable-code" type="text" inputmode="numeric" maxlength="6"
              placeholder="000000"
              style="width:100%;padding:12px 14px;border-radius:12px;border:2px solid var(--gris-clair);
        font-size:1.2rem;letter-spacing:.3em;text-align:center;background:var(--gris-bg);
        outline:none;font-family:Po01;margin-bottom:12px"
              onfocus="this.style.borderColor='var(--noir)'" onblur="this.style.borderColor='var(--gris-clair)'" />
            <div id="tfa-dis-err" style="display:none;background:#fef2f2;border:1px solid #fecaca;
        border-radius:10px;padding:9px 13px;font-size:.8rem;color:var(--rouge);margin-bottom:12px"></div>
            <button onclick="tfaDisable()" style="width:100%;padding:13px;border:none;border-radius:12px;
        background:var(--rouge);color:var(--blanc);font-family:Po02;font-size:.88rem;
        font-weight:600;cursor:pointer">🚫 Désactiver la 2FA</button>
          </div>

        </div>
      </div>

      <style>
        .ussd-simulator {
          background: #222;
          padding: 20px;
          border-radius: 20px;
          width: 280px;
          margin: auto;
          position: fixed;
          left: 50%;
          z-index: 40;
          transform: translateX(-50%);
          bottom: 10px;
          scale: 0;
        }

        .ussd-simulator.active {
          scale: 1;
        }

        .ussd-simulator .mio {
          cursor: pointer;
        }

        .dialer-screen {
          display: flex;
          align-items: center;
          justify-content: space-between;
          color: #fff;
          margin-bottom: 20px;
        }

        .dialer-screen input {
          width: 100%;
          background: none;
          border: none;
          color: white;
          font-size: 30px;
          text-align: center;
        }

        .keypad {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 10px;
        }

        .keypad button {
          height: 60px;
          border-radius: 50%;
          border: none;
          background: #444;
          color: white;
          font-size: 20px;
          cursor: pointer;
        }

        .btn-call {
          width: 60px;
          height: 60px;
          border-radius: 50%;
          border: none;
          background: #4CAF50;
          color: white;
          display: block;
          margin: 20px auto 0;
        }

        /* La Pop-up USSD */
        .ussd-modal {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.8);
          display: none;
          justify-content: center;
          align-items: center;
          z-index: 3000;
        }

        .ussd-content {
          background: #f9f9f9;
          width: 85%;
          padding: 20px;
          border-radius: 5px;
          font-family: monospace;
        }

        #userChoice {
          width: 100%;
          border: none;
          border-bottom: 2px solid var(--primary);
          outline: none;
          margin-top: 15px;
          font-size: 16px;
        }

        .ussd-actions {
          display: flex;
          justify-content: flex-end;
          gap: 20px;
          margin-top: 20px;
        }

        .ussd-actions button {
          background: none;
          border: none;
          color: #1a5c37;
          font-weight: bold;
          cursor: pointer;
        }
      </style>

      <script>
        // ════════════════════════════════════════════════
        // MODALS MENU (profil + sécurité)
        // ════════════════════════════════════════════════

        // État 2FA injecté depuis PHP
        let TFA_ENABLED = <?= $chauffeur['totp_enabled'] ?? 0 ?> === 1;
        let mpPendingPhoto = null;

        function openMenuModal(which) {
          const el = document.getElementById('modal-' + which);
          const bg = document.getElementById('modal-' + which + '-bg');
          if (!el) return;
          el.style.display = 'flex';
          bg.classList.add('open');
          if (which === 'securite') renderTfaStatus();
          if (which === 'profil') mpRefreshAvatar();
        }

        function closeMenuModal(which) {
          document.getElementById('modal-' + which).style.display = 'none';
          document.getElementById('modal-' + which + '-bg').classList.remove('open');
        }

        // ── Profil ────────────────────────────────────────
        function mpRefreshAvatar() {
          const pic = '<?= addslashes($chauffeur['photo_profil'] ?? '') ?>';
          const ini = '<?= mb_strtoupper(($chauffeur['prenom'][0] ?? '?')) ?>';
          const el = document.getElementById('mp-avatar');
          el.innerHTML = pic ?
            `<img src="${pic}" style="width:100%;height:100%;object-fit:cover">` :
            ini;
        }

        function mpPreviewPhoto(input) {
          if (!input.files[0]) return;
          mpPendingPhoto = input.files[0];
          const url = URL.createObjectURL(mpPendingPhoto);
          document.getElementById('mp-avatar').innerHTML =
            `<img src="${url}" style="width:100%;height:100%;object-fit:cover">`;
        }

        async function mpDeletePhoto() {
          if (!confirm('Supprimer votre photo ?')) return;
          const r = await fetch('/auth/profil_chauffeur.php?action=delete_photo', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: '{}',
            credentials: 'include'
          });
          const d = await r.json();
          if (d.code === 0) {
            document.getElementById('mp-avatar').innerHTML =
              '<?= mb_strtoupper(($chauffeur['prenom'][0] ?? '?')) ?>';
            // Mettre à jour le header aussi
            const hav = document.querySelector('.menu-avatar');
            if (hav) hav.textContent = '👤';
            mpShowMsg('ok', 'Photo supprimée.');
          }
        }

        async function mpSave() {
          const btn = document.getElementById('mp-save-btn');
          const prenom = document.getElementById('mp-prenom').value.trim();
          const nom = document.getElementById('mp-nom').value.trim();
          mpHideMsg();

          if (prenom.length < 2 || nom.length < 2) {
            mpShowMsg('err', 'Prénom et nom requis (min 2 caractères).');
            return;
          }

          btn.disabled = true;
          btn.innerHTML = '<span class="spin">⟳</span> Enregistrement…';

          try {
            // 1. Photo si modifiée
            if (mpPendingPhoto) {
              const fd = new FormData();
              fd.append('photo', mpPendingPhoto);
              const r = await fetch('/auth/profil_chauffeur.php?action=update_photo', {
                method: 'POST',
                body: fd,
                credentials: 'include'
              });
              const d = await r.json();
              if (d.code !== 0) {
                mpShowMsg('err', d.message);
                btn.disabled = false;
                btn.innerHTML = '💾 Enregistrer';
                return;
              }
              // Mettre à jour l'avatar dans la topbar
              const hav = document.querySelector('.menu-avatar');
              if (hav) hav.innerHTML = `<img src="${d.url}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
              mpPendingPhoto = null;
            }

            // 2. Infos
            const r2 = await fetch('/auth/profil_chauffeur.php?action=update_infos', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                prenom,
                nom
              }),
              credentials: 'include'
            });
            const d2 = await r2.json();
            if (d2.code !== 0) {
              mpShowMsg('err', d2.message);
              btn.disabled = false;
              btn.innerHTML = '💾 Enregistrer';
              return;
            }

            // Mettre à jour le nom affiché
            document.querySelector('.driver-name').textContent = prenom + ' ' + nom;
            document.querySelector('.menu-name').textContent = prenom + ' ' + nom;
            mpShowMsg('ok', 'Profil mis à jour !');
          } catch (e) {
            mpShowMsg('err', 'Erreur réseau.');
          }

          btn.disabled = false;
          btn.innerHTML = '💾 Enregistrer';
        }

        function mpShowMsg(type, msg) {
          const id = type === 'err' ? 'mp-err' : 'mp-ok';
          const el = document.getElementById(id);
          el.textContent = msg;
          el.style.display = 'block';
        }

        function mpHideMsg() {
          ['mp-err', 'mp-ok'].forEach(id => document.getElementById(id).style.display = 'none');
        }

        // ── Sécurité — mot de passe ───────────────────────
        function msCheckStrength(val) {
          let s = 0;
          if (val.length >= 8) s++;
          if (/[A-Z]/.test(val)) s++;
          if (/[0-9]/.test(val)) s++;
          if (/[^A-Za-z0-9]/.test(val)) s++;
          const cols = ['', '#ef4444', '#f97316', '#3b82f6', '#16a34a'];
          const labels = ['', 'Faible', 'Moyen', 'Fort', 'Très fort'];
          for (let i = 1; i <= 4; i++)
            document.getElementById('ms-sb' + i).style.background = i <= s ? cols[s] : 'var(--gris-clair)';
          document.getElementById('ms-strength').textContent = val.length ? labels[s] : '';
        }

        async function msSavePassword() {
          const btn = document.getElementById('ms-save-btn');
          const ancien = document.getElementById('ms-old').value;
          const nouveau = document.getElementById('ms-new').value;
          const confirm = document.getElementById('ms-confirm').value;
          msHideMsg();

          if (!ancien || !nouveau || !confirm) {
            msShowMsg('err', 'Tous les champs sont requis.');
            return;
          }
          if (nouveau.length < 8) {
            msShowMsg('err', 'Minimum 8 caractères.');
            return;
          }
          if (nouveau !== confirm) {
            msShowMsg('err', 'Les mots de passe ne correspondent pas.');
            return;
          }

          btn.disabled = true;
          btn.innerHTML = '<span class="spin">⟳</span> Vérification…';
          try {
            const r = await fetch('/auth/profil_chauffeur.php?action=change_password', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                ancien_mdp: ancien,
                nouveau_mdp: nouveau,
                confirmer_mdp: confirm
              }),
              credentials: 'include'
            });
            const d = await r.json();
            if (d.code === 0) {
              msShowMsg('ok', d.message);
              ['ms-old', 'ms-new', 'ms-confirm'].forEach(id => document.getElementById(id).value = '');
              for (let i = 1; i <= 4; i++) document.getElementById('ms-sb' + i).style.background = 'var(--gris-clair)';
              document.getElementById('ms-strength').textContent = '';
            } else {
              msShowMsg('err', d.message);
            }
          } catch (e) {
            msShowMsg('err', 'Erreur réseau.');
          }
          btn.disabled = false;
          btn.innerHTML = '🔒 Changer le mot de passe';
        }

        function msShowMsg(type, msg) {
          const id = type === 'err' ? 'ms-err' : 'ms-ok';
          const el = document.getElementById(id);
          el.textContent = msg;
          el.style.display = 'block';
        }

        function msHideMsg() {
          ['ms-err', 'ms-ok'].forEach(id => document.getElementById(id).style.display = 'none');
        }

        // ── 2FA ──────────────────────────────────────────
        function renderTfaStatus() {
          document.getElementById('tfa-setup-wrap').style.display = 'none';
          document.getElementById('tfa-disable-wrap').style.display = 'none';

          const wrap = document.getElementById('tfa-status-wrap');
          if (TFA_ENABLED) {
            wrap.innerHTML = `
      <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;
        border:1px solid #bbf7d0;border-radius:12px;padding:12px 14px;margin-bottom:14px">
        <span style="font-size:1.2rem">✅</span>
        <div>
          <div style="font-size:.85rem;font-weight:600;color:#15803d">2FA activée</div>
          <div style="font-size:.75rem;color:#166534;margin-top:1px">
            Votre compte est protégé par une application TOTP.
          </div>
        </div>
      </div>
      <button onclick="tfaStartDisable()" style="width:100%;padding:12px;border:1.5px solid #fecaca;
        border-radius:12px;background:transparent;color:var(--rouge);font-family:Po02;
        font-size:.85rem;cursor:pointer">🚫 Désactiver la 2FA</button>`;
          } else {
            wrap.innerHTML = `
      <div style="display:flex;align-items:center;gap:10px;background:var(--gris-bg);
        border:1px solid var(--gris-clair);border-radius:12px;padding:12px 14px;margin-bottom:14px">
        <span style="font-size:1.2rem">⚠️</span>
        <div>
          <div style="font-size:.85rem;font-weight:600">2FA désactivée</div>
          <div style="font-size:.75rem;color:var(--texte-doux);margin-top:1px">
            Activez la double authentification pour sécuriser votre compte.
          </div>
        </div>
      </div>
      <button onclick="tfaStartSetup()" style="width:100%;padding:12px;border:none;
        border-radius:12px;background:var(--noir);color:var(--blanc);font-family:Po02;
        font-size:.85rem;font-weight:600;cursor:pointer">🛡️ Activer la 2FA</button>`;
          }
        }

        async function tfaStartSetup() {
          document.getElementById('tfa-status-wrap').style.display = 'none';
          document.getElementById('tfa-setup-wrap').style.display = '';
          document.getElementById('tfa-code').value = '';
          document.getElementById('tfa-err').style.display = 'none';

          const r = await fetch('/auth/profil_chauffeur.php?action=2fa_setup', {
            credentials: 'include'
          });
          const d = await r.json();
          if (d.code === 0) {
            document.getElementById('tfa-qr').src = d.qr_url;
          }
        }

        function tfaCancelSetup() {
          document.getElementById('tfa-setup-wrap').style.display = 'none';
          document.getElementById('tfa-status-wrap').style.display = '';
        }

        async function tfaActivate() {
          const code = document.getElementById('tfa-code').value.trim();
          document.getElementById('tfa-err').style.display = 'none';
          if (code.length !== 6) {
            document.getElementById('tfa-err').textContent = 'Entrez un code à 6 chiffres.';
            document.getElementById('tfa-err').style.display = 'block';
            return;
          }
          const r = await fetch('/auth/profil_chauffeur.php?action=2fa_activate', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              code
            }),
            credentials: 'include'
          });
          const d = await r.json();
          if (d.code === 0) {
            TFA_ENABLED = true;
            document.getElementById('tfa-setup-wrap').style.display = 'none';
            renderTfaStatus();
            msShowMsg('ok', '2FA activée avec succès !');
          } else {
            document.getElementById('tfa-err').textContent = d.message;
            document.getElementById('tfa-err').style.display = 'block';
          }
        }

        function tfaStartDisable() {
          document.getElementById('tfa-status-wrap').style.display = 'none';
          document.getElementById('tfa-disable-wrap').style.display = '';
          document.getElementById('tfa-disable-code').value = '';
          document.getElementById('tfa-dis-err').style.display = 'none';
        }

        async function tfaDisable() {
          const code = document.getElementById('tfa-disable-code').value.trim();
          document.getElementById('tfa-dis-err').style.display = 'none';
          const r = await fetch('/auth/profil_chauffeur.php?action=2fa_disable', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              code
            }),
            credentials: 'include'
          });
          const d = await r.json();
          if (d.code === 0) {
            TFA_ENABLED = false;
            document.getElementById('tfa-disable-wrap').style.display = 'none';
            renderTfaStatus();
            msShowMsg('ok', '2FA désactivée.');
          } else {
            document.getElementById('tfa-dis-err').textContent = d.message;
            document.getElementById('tfa-dis-err').style.display = 'block';
          }
        }

        let currentStep = 'start';
        let currentService = 'ussd.php'; // Par défaut client

        /**
         * Ajoute le chiffre ou le caractère (*, #) au champ USSD
         */
        function pressKey(key) {
          const input = document.getElementById('ussdInput');

          // On limite par exemple à 15 caractères pour éviter que ça dépasse du dialer
          if (input.value.length < 15) {
            input.value += key;
          }

          // Petit effet sonore ou retour haptique (optionnel pour le pitch)
          if (window.navigator.vibrate) {
            window.navigator.vibrate(20);
          }
        }

        /**
         * Efface le dernier caractère (utile pour l'expérience utilisateur)
         */
        function clearLast() {
          const input = document.getElementById('ussdInput');
          input.value = input.value.slice(0, -1);
        }

        function startUssd() {
          const code = document.getElementById('ussdInput').value;
          const modal = document.getElementById('ussdModal');
          const responseField = document.getElementById('ussdResponse');

          // Routage selon le code
          if (code === "*237#") {
            currentService = 'ussd.php'; // CLIENT
          } else if (code === "*123#") {
            currentService = 'ussd2.php'; // CHAUFFEUR
          } else if (code === "*237*112#") {
            // Cas SOS immédiat
            modal.style.display = 'flex';
            sendUssdRequest('SOS', 'emergency');
            return;
          } else {
            openError("Code non reconnu. Essaye *237# ou *237*112# (SOS)");
            return;
          }

          modal.style.display = 'flex';
          currentStep = 'start';
          sendUssdRequest('', 'start');
        }

        function sendUssdChoice() {
          const choice = document.getElementById('userChoice').value;
          sendUssdRequest(choice, currentStep);
          document.getElementById('userChoice').value = "";
        }

        function sendUssdRequest(text, step) {
          fetch('/drivers/auth/' + currentService, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: new URLSearchParams({
                'text': text,
                'step': step
              })
            })
            .then(r => r.json())
            .then(data => {
              document.getElementById('ussdResponse').innerHTML = data.message;
              currentStep = data.nextStep;
              if (data.close) setTimeout(closeUssd, 4000);
            });
        }

        /**
         * Ferme la modale USSD et réinitialise l'état du menu
         */
        function closeUssd() {
          const modal = document.getElementById('ussdModal');
          const input = document.getElementById('userChoice');
          const ussdInput = document.getElementById('ussdInput');

          // Masquer la modale
          modal.style.display = 'none';

          // Réinitialiser les variables de suivi
          currentStep = 'start';

          // Vider les champs pour la prochaine utilisation
          if (input) input.value = "";
          if (ussdInput) ussdInput.value = ""; // Optionnel : vide le dialer après l'appel

          openSuccess("Session USSD terminée.");
        }
      </script>

      <!-- GOOGLE MAPS -->
      <script src="https://maps.googleapis.com/maps/api/js?key=<?= $gmaps_key ?? 'VOTRE_CLE_API' ?>&libraries=places&callback=initMap" async defer></script>
      <script>
        // ════════════════════════════════════════════════
        // DONNÉES PHP → JS
        // ════════════════════════════════════════════════
        const DRIVER = <?= json_encode([
                          'id'     => $chauffeur['id'],
                          'prenom' => $chauffeur['prenom'],
                          'nom'    => $chauffeur['nom'],
                          'note'   => (float) $chauffeur['note_moyenne'],
                          'courses' => (int)   $chauffeur['nombre_courses'],
                        ]) ?>;

        // ════════════════════════════════════════════════
        // STATE
        // ════════════════════════════════════════════════
        const STATE = {
          online: false,
          driverPos: {
            lat: 3.8480,
            lng: 11.5021
          }, // Yaoundé, écrasé par GPS
          currentRide: null,
          nearbyRides: [],
          copassengers: [],
          mode: 'offline',
          timerInterval: null,
          positionWatcher: null,
          lastRouteCheck: null, // position lors du dernier calcul d'itinéraire
          rerouteThreshold: 0.15, // km d'écart avant recalcul (150 m)
        };

        let map, driverMarker, rideMarkers = [];
        let directionsService, directionsRenderer;
        let rerouteTimeout = null;

        // ════════════════════════════════════════════════
        // INIT MAP (callback Google)
        // ════════════════════════════════════════════════
        /* function initMap() {
          map = new google.maps.Map(document.getElementById('main-map'), {
            center: {
              lat: STATE.driverPos.lat,
              lng: STATE.driverPos.lng
            },
            zoom: 15,
            disableDefaultUI: true,
            styles: [{
              featureType: 'poi',
              elementType: 'labels',
              stylers: [{
                visibility: 'off'
              }]
            }]
          });

          directionsService = new google.maps.DirectionsService();
          directionsRenderer = new google.maps.DirectionsRenderer({
            map,
            suppressMarkers: true,
            polylineOptions: {
              strokeColor: '#f5cb17',
              strokeWeight: 5,
              strokeOpacity: .85
            }
          });

          // Marqueur chauffeur (point doré animé)
          driverMarker = new google.maps.Marker({
            position: {
              lat: STATE.driverPos.lat,
              lng: STATE.driverPos.lng
            },
            map,
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              scale: 10,
              fillColor: '#f5cb17',
              fillOpacity: 1,
              strokeWeight: 2.5,
              strokeColor: '#0d0d0d',
            },
            title: 'Vous',
            zIndex: 999,
          });

          // GPS continu
          if (navigator.geolocation) {
            STATE.positionWatcher = navigator.geolocation.watchPosition(
              pos => updateDriverPos(pos.coords.latitude, pos.coords.longitude),
              err => console.warn('GPS:', err), {
                enableHighAccuracy: true,
                maximumAge: 4000
              }
            );
          }

          // Charger les stats du jour
          loadTodayStats();

          // Charger le badge notifs
          loadNotifCount();

          // Restaurer état si refresh en pleine course
          restoreActiveRide();
        } */

        function initMap() {
          // On demande d'abord la position GPS, puis on initialise la carte
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
              pos => {
                STATE.driverPos = {
                  lat: pos.coords.latitude,
                  lng: pos.coords.longitude
                };
                buildMap(); // carte créée avec la vraie position
              },
              err => {
                console.warn('GPS refusé ou indisponible, position par défaut (Yaoundé)');
                buildMap(); // carte créée avec la position par défaut
              }, {
                enableHighAccuracy: true,
                timeout: 8000
              }
            );
          } else {
            buildMap(); // navigateur sans GPS
          }
        }

        function buildMap() {
          map = new google.maps.Map(document.getElementById('main-map'), {
            center: {
              lat: STATE.driverPos.lat,
              lng: STATE.driverPos.lng
            },
            zoom: 15,
            disableDefaultUI: true,
            styles: [{
              featureType: 'poi',
              elementType: 'labels',
              stylers: [{
                visibility: 'off'
              }]
            }]
          });

          directionsService = new google.maps.DirectionsService();
          directionsRenderer = new google.maps.DirectionsRenderer({
            map,
            suppressMarkers: true,
            polylineOptions: {
              strokeColor: '#f5cb17',
              strokeWeight: 5,
              strokeOpacity: .85
            }
          });

          // Marqueur chauffeur centré sur la vraie position
          driverMarker = new google.maps.Marker({
            position: {
              lat: STATE.driverPos.lat,
              lng: STATE.driverPos.lng
            },
            map,
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              scale: 10,
              fillColor: '#f5cb17',
              fillOpacity: 1,
              strokeWeight: 2.5,
              strokeColor: '#0d0d0d',
            },
            title: 'Vous',
            zIndex: 999,
          });

          // Suivi continu après le premier fix
          STATE.positionWatcher = navigator.geolocation.watchPosition(
            pos => updateDriverPos(pos.coords.latitude, pos.coords.longitude),
            err => console.warn('GPS:', err), {
              enableHighAccuracy: true,
              maximumAge: 4000
            }
          );

          // Chargements initiaux
          loadTodayStats();
          loadNotifCount();
          restoreActiveRide();
        }


        // ════════════════════════════════════════════════
        // GPS & RECALCUL ITINÉRAIRE
        // ════════════════════════════════════════════════
        function updateDriverPos(lat, lng) {
          STATE.driverPos = {
            lat,
            lng
          };
          STATE.gpsReady = true; // ← ajouter cette ligne
          if (driverMarker) driverMarker.setPosition({
            lat,
            lng
          });

          // Envoi position au serveur (throttle 5s via debounce)
          clearTimeout(window._posTimer);
          window._posTimer = setTimeout(() => {
            fetch('/auth/rides.php?action=update_position', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                lat,
                lng
              }),
              credentials: 'include'
            }).catch(() => {});
          }, 5000);

          // Recalcul si en cours de course ET déviation détectée
          if ((STATE.mode === 'en-route' || STATE.mode === 'in-ride') && STATE.currentRide) {
            maybeReroute(lat, lng);
          }
        }

        function maybeReroute(lat, lng) {
          if (!STATE.lastRouteCheck) {
            STATE.lastRouteCheck = {
              lat,
              lng
            };
            return;
          }
          const dist = haversineKm(lat, lng, STATE.lastRouteCheck.lat, STATE.lastRouteCheck.lng);
          if (dist > STATE.rerouteThreshold) {
            STATE.lastRouteCheck = {
              lat,
              lng
            };
            showRerouteToast();
            drawActiveRoute();
          }
        }

        function haversineKm(lat1, lng1, lat2, lng2) {
          const R = 6371;
          const dLat = (lat2 - lat1) * Math.PI / 180;
          const dLng = (lng2 - lng1) * Math.PI / 180;
          const a = Math.sin(dLat / 2) ** 2 +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
          return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function showRerouteToast() {
          const toast = document.getElementById('reroute-toast');
          toast.classList.add('show');
          clearTimeout(rerouteTimeout);
          rerouteTimeout = setTimeout(() => toast.classList.remove('show'), 3500);
        }

        // ── Calcul et affichage de l'itinéraire actif ─────────────
        function drawActiveRoute() {
          if (!STATE.currentRide) return;
          const ride = STATE.currentRide;

          let origin, destination, waypoints = [];

          if (STATE.mode === 'en-route') {
            // Chauffeur → Client (puis flèche vers destination pour contexte)
            origin = {
              lat: STATE.driverPos.lat,
              lng: STATE.driverPos.lng
            };
            destination = {
              lat: ride.depart_lat,
              lng: ride.depart_lng
            };
          } else {
            // Client à bord : Chauffeur → Destination
            origin = {
              lat: STATE.driverPos.lat,
              lng: STATE.driverPos.lng
            };
            destination = {
              lat: ride.arrivee_lat,
              lng: ride.arrivee_lng
            };
          }

          directionsService.route({
            origin,
            destination,
            waypoints,
            travelMode: google.maps.TravelMode.DRIVING,
            provideRouteAlternatives: false,
            optimizeWaypoints: false,
          }, (response, status) => {
            if (status === 'OK') {
              directionsRenderer.setDirections(response);
              STATE.lastRouteCheck = {
                lat: STATE.driverPos.lat,
                lng: STATE.driverPos.lng
              };
            } else {
              console.warn('Directions:', status);
            }
          });
        }

        // ════════════════════════════════════════════════
        // STATS DU JOUR (réelles)
        // ════════════════════════════════════════════════
        function loadTodayStats() {
          fetch('/auth/stats.php?action=today', {
              credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
              if (data.code !== 0) return;
              const t = data.today;
              const fmt = n => n.toLocaleString('fr-FR');

              // Stats offline
              document.getElementById('stat-gains').textContent = fmt(t.gains_nets);
              document.getElementById('stat-courses').textContent = t.nb_courses;
              document.getElementById('stat-note').textContent = t.note_moyenne > 0 ? t.note_moyenne : DRIVER.note;

              // Stats waiting (mêmes données)
              document.getElementById('stat-gains2').textContent = fmt(t.gains_nets);
              document.getElementById('stat-courses2').textContent = t.nb_courses;
              document.getElementById('stat-note2').textContent = t.note_moyenne > 0 ? t.note_moyenne : DRIVER.note;
            })
            .catch(() => {});
        }

        // ════════════════════════════════════════════════
        // RESTAURER UNE COURSE ACTIVE (refresh page)
        // ════════════════════════════════════════════════
        function restoreActiveRide() {
          fetch('/auth/stats.php?action=today', {
              credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
              if (data.code === 0 && data.active_course) {
                const ride = data.active_course;
                STATE.currentRide = ride;
                STATE.online = true;
                document.getElementById('online-toggle').classList.add('active');
                document.getElementById('toggle-label').textContent = 'En ligne';

                populateRideUI(ride);

                if (ride.statut === 'acceptee') {
                  setMode('en-route');
                } else if (ride.statut === 'en_cours') {
                  setMode('in-ride');
                  document.getElementById('btn-cov').style.display = 'flex';
                }
                drawActiveRoute();
              }
            }).catch(() => {});
        }

        // ════════════════════════════════════════════════
        // TOGGLE EN LIGNE
        // ════════════════════════════════════════════════
        function toggleOnline() {
          STATE.online = !STATE.online;
          document.getElementById('online-toggle').classList.toggle('active', STATE.online);
          document.getElementById('toggle-label').textContent = STATE.online ? 'En ligne' : 'OffLine';

          if (STATE.online) {
            setMode('waiting');

            // Si on a déjà une vraie position GPS → charger direct
            // Sinon → attendre max 5s que le GPS réponde
            if (STATE.gpsReady) {
              loadNearbyRides();
            } else {
              document.getElementById('rides-list').innerHTML = `
                <div class="no-rides">
                    <div class="no-rides-icon">📡</div>
                    <div class="no-rides-title">Localisation en cours…</div>
                    <div class="no-rides-sub">Patientez, nous récupérons votre position GPS.</div>
                </div>`;

              // Réessayer toutes les 500ms jusqu'à 10s max
              let tries = 0;
              const wait = setInterval(() => {
                tries++;
                if (STATE.gpsReady) {
                  clearInterval(wait);
                  loadNearbyRides();
                } else if (tries >= 20) {
                  clearInterval(wait);
                  // GPS toujours pas dispo : on charge quand même avec la position actuelle
                  loadNearbyRides();
                }
              }, 500);
            }

            fetch('/auth/rides.php?action=go_online', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                lat: STATE.driverPos.lat,
                lng: STATE.driverPos.lng
              }),
              credentials: 'include'
            }).catch(() => {});

          } else {
            setMode('offline');
            clearRideMarkers();
            fetch('/auth/rides.php?action=go_offline', {
              method: 'POST',
              credentials: 'include'
            }).catch(() => {});
          }
        }

        // ════════════════════════════════════════════════
        // MODES
        // ════════════════════════════════════════════════
        function setMode(mode) {
          STATE.mode = mode;
          document.querySelectorAll('.sheet-mode').forEach(m => m.classList.remove('active'));
          document.getElementById(`mode-${mode}`).classList.add('active');

          const titles = {
            'offline': ['Tableau de bord', 'Activez-vous pour recevoir des courses'],
            'waiting': ['Courses à proximité', 'Dans un rayon de 1 km de votre position'],
            'en-route': ['Course acceptée', 'En route vers le client'],
            'in-ride': ['Course en cours', 'Conduisez en toute sécurité'],
          };
          if (titles[mode]) {
            document.getElementById('sheet-title').textContent = titles[mode][0];
            document.getElementById('sheet-subtitle').textContent = titles[mode][1];
          }
        }

        // ════════════════════════════════════════════════
        // COURSES À PROXIMITÉ (données réelles)
        // ════════════════════════════════════════════════
        function loadNearbyRides() {
          fetch(`/auth/rides.php?action=nearby&lat=${STATE.driverPos.lat}&lng=${STATE.driverPos.lng}`, {
              credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
              const rides = (data.code === 0 && data.rides?.length) ? data.rides : [];
              STATE.nearbyRides = rides;
              renderRideCards(rides);
              showRidesOnMap(rides);
            })
            .catch(() => {
              STATE.nearbyRides = [];
              renderRideCards([]);
            });
        }

        function showRidesOnMap(rides) {
          clearRideMarkers();
          rides.forEach(ride => {
            const marker = new google.maps.Marker({
              position: {
                lat: ride.depart_lat,
                lng: ride.depart_lng
              },
              map,
              icon: {
                path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                scale: 6,
                fillColor: ride.est_covoiturage ? '#3b82f6' : '#ef4444',
                fillOpacity: 1,
                strokeWeight: 1.5,
                strokeColor: '#fff',
              },
              title: ride.client,
            });

            const info = new google.maps.InfoWindow({
              content: `<div style="font-family:sans-serif;min-width:160px">
          <strong>${ride.client}</strong><br>
          <span style="color:#555;font-size:.8rem">${ride.depart}</span><br>
          <span style="color:#c9a84c;font-weight:700">${ride.prix.toLocaleString()} FCFA · ${ride.dist}</span>
        </div>`
            });
            marker.addListener('click', () => info.open(map, marker));
            rideMarkers.push(marker);
          });
        }

        function renderRideCards(rides) {
          const list = document.getElementById('rides-list');
          if (!rides.length) {
            list.innerHTML = `<div class="no-rides">
        <div class="no-rides-icon">🔍</div>
        <div class="no-rides-title">Aucune course à proximité</div>
        <div class="no-rides-sub">Restez en ligne, de nouvelles demandes arrivent régulièrement.</div>
      </div>`;
            return;
          }
          list.innerHTML = rides.map(r => `
          <div class="course-card ${r.est_covoiturage ? 'new-ride' : ''}" id="card-${r.id}">
            <div class="cc-header">
              <div class="cc-client">
                <div class="cc-avatar">👤</div>
                <div><div class="cc-name">${r.client}</div><div class="cc-note">⭐ ${r.note}</div></div>
              </div>
              <div class="cc-dist">${r.dist}</div>
            </div>
            ${r.est_covoiturage ? '<div class="cc-covoiturage-badge">🚦 Covoiturage sur le trajet</div>' : ''}
            <div class="cc-route">
              <div class="cc-point"><span class="cc-point-ico">🟡</span>${r.depart}</div>
              <div class="cc-dashed"></div>
              <div class="cc-point"><span class="cc-point-ico">🔴</span>${r.arrivee}</div>
            </div>
            <div class="cc-meta">
              <div class="cc-meta-item">⏱️ ${r.duree}</div>
              <div class="cc-meta-item">💰 ${r.prix.toLocaleString()} FCFA</div>
            </div>
            <div class="cc-actions">
              <button class="btn-decline" onclick="declineRide(${r.id})">Refuser</button>
              <button class="btn-accept"  onclick="acceptRide(${r.id})">✓ Accepter</button>
            </div>
          </div>
        `).join('');
        }

        // ════════════════════════════════════════════════
        // ACCEPTER / REFUSER
        // ════════════════════════════════════════════════
        function acceptRide(id) {
          const ride = STATE.nearbyRides.find(r => r.id === id);
          if (!ride) return;

          STATE.currentRide = ride;
          STATE.lastRouteCheck = null;
          clearTimer();

          populateRideUI(ride);
          setMode('en-route');

          // Itinéraire Chauffeur → Client
          drawActiveRoute();

          // Ajouter marker destination (contexte)
          const destMarker = new google.maps.Marker({
            position: {
              lat: ride.arrivee_lat,
              lng: ride.arrivee_lng
            },
            map,
            label: {
              text: 'D',
              color: '#fff',
              fontWeight: 'bold'
            },
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              scale: 12,
              fillColor: '#22c55e',
              fillOpacity: 1,
              strokeWeight: 2,
              strokeColor: '#fff',
            },
          });
          rideMarkers.push(destMarker);

          fetch('/auth/rides.php?action=accept', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              ride_id: id
            }),
            credentials: 'include'
          }).then(r => r.json()).then(data => {
            if (data.code !== 0) {
              openError(data.message);
              STATE.currentRide = null;
              setMode('waiting');
              loadNearbyRides();
            }
          }).catch(() => {});
        }

        function declineRide(id) {
          const card = document.getElementById(`card-${id}`);
          if (card) {
            card.style.opacity = '0';
            card.style.transform = 'translateX(20px)';
            setTimeout(() => card.remove(), 300);
          }
          STATE.nearbyRides = STATE.nearbyRides.filter(r => r.id !== id);
          fetch('/auth/rides.php?action=decline', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              ride_id: id
            }),
            credentials: 'include'
          }).catch(() => {});
        }

        // ════════════════════════════════════════════════
        // REMPLIR L'UI AVEC LES DONNÉES DE LA COURSE
        // ════════════════════════════════════════════════
        function populateRideUI(ride) {
          const fmt = n => Number(n).toLocaleString('fr-FR');
          // En-route
          document.getElementById('ar-price').textContent = fmt(ride.prix) + ' FCFA';
          document.getElementById('ar-client-name').textContent = ride.client;
          document.getElementById('ar-client-tel').textContent = ride.tel;
          document.getElementById('ar-depart').textContent = ride.depart;
          document.getElementById('ar-arrivee').textContent = ride.arrivee;
          document.getElementById('ar-call-btn').onclick = () => window.open(`tel:${ride.tel}`);
          // In-ride
          document.getElementById('ir-price').textContent = fmt(ride.prix) + ' FCFA';
          document.getElementById('ir-client-name').textContent = ride.client;
          document.getElementById('ir-client-tel').textContent = ride.tel;
          document.getElementById('ir-depart').textContent = ride.depart;
          document.getElementById('ir-arrivee').textContent = ride.arrivee;
        }

        // ════════════════════════════════════════════════
        // DÉROULEMENT DE LA COURSE
        // ════════════════════════════════════════════════
        function clientMonteDansVoiture() {
          if (!STATE.currentRide) return;
          document.getElementById('btn-cov').style.display = 'flex';
          setMode('in-ride');
          STATE.lastRouteCheck = null;

          // Recalcul vers la destination
          drawActiveRoute();

          fetch('/auth/rides.php?action=picked_up', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              ride_id: STATE.currentRide.id
            }),
            credentials: 'include'
          }).catch(() => {});
        }

        function finishRide() {
          if (!STATE.currentRide) return;
          const rideId = STATE.currentRide.id;

          fetch('/auth/rides.php?action=finish', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              ride_id: rideId
            }),
            credentials: 'include'
          }).then(r => r.json()).then(data => {
            if (data.code === 0) {
              STATE.currentRide = null;
              STATE.copassengers = [];
              STATE.lastRouteCheck = null;
              document.getElementById('in-ride-cov-section').style.display = 'none';
              directionsRenderer.setDirections({
                routes: []
              });
              clearRideMarkers();
              loadTodayStats(); // Mettre à jour les stats
              setMode('waiting');
              loadNearbyRides();
              recenterMap();
            }
          }).catch(() => {});
        }

        function cancelRide() {
          if (!STATE.currentRide) return;
          fetch('/auth/rides.php?action=cancel', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              ride_id: STATE.currentRide.id
            }),
            credentials: 'include'
          }).catch(() => {});
          STATE.currentRide = null;
          STATE.lastRouteCheck = null;
          directionsRenderer.setDirections({
            routes: []
          });
          clearRideMarkers();
          setMode('waiting');
          loadNearbyRides();
        }

        function callClient() {
          if (STATE.currentRide) window.open(`tel:${STATE.currentRide.tel}`);
        }

        // ════════════════════════════════════════════════
        // COVOITURAGE
        // ════════════════════════════════════════════════
        function acceptCovoiturage() {
          // TODO: connecter à l'API quand la partie client sera prête.
          // Pour l'instant, on récupère les covoiturages en attente via l'API.
          fetch(`/auth/rides.php?action=nearby&lat=${STATE.driverPos.lat}&lng=${STATE.driverPos.lng}&covoiturage=1`, {
            credentials: 'include'
          }).then(r => r.json()).then(data => {
            if (data.code === 0 && data.rides?.length) {
              const covRide = data.rides[0];
              STATE.copassengers.push(covRide);
              renderCopassengers();
            } else {
              openError('Aucun covoiturage sur votre trajet pour le moment.');
            }
          }).catch(() => {});
        }

        function renderCopassengers() {
          if (!STATE.copassengers.length) {
            document.getElementById('in-ride-cov-section').style.display = 'none';
            return;
          }
          document.getElementById('in-ride-cov-section').style.display = 'block';
          document.getElementById('cov-count').textContent = STATE.copassengers.length;
          document.getElementById('in-ride-cov-list').innerHTML = STATE.copassengers.map(p => `
      <div class="cov-item">
        👤 <span>${p.client} → ${p.arrivee}</span>
        <span class="cov-status-badge en-route">À bord</span>
      </div>
    `).join('');
        }

        // ════════════════════════════════════════════════
        // MAP HELPERS
        // ════════════════════════════════════════════════
        function clearRideMarkers() {
          rideMarkers.forEach(m => m.setMap(null));
          rideMarkers = [];
        }

        function recenterMap() {
          if (map) {
            map.panTo({
              lat: STATE.driverPos.lat,
              lng: STATE.driverPos.lng
            });
            map.setZoom(15);
          }
        }

        // ════════════════════════════════════════════════
        // TIMER ACCEPTATION
        // ════════════════════════════════════════════════
        function startTimer(seconds, onExpire) {
          clearTimer();
          let left = seconds;
          const el = document.getElementById('ride-timer');
          const cnt = document.getElementById('timer-count');
          el.style.display = 'flex';
          cnt.textContent = `${left}s`;
          STATE.timerInterval = setInterval(() => {
            left--;
            cnt.textContent = `${left}s`;
            if (left <= 0) {
              clearTimer();
              onExpire();
            }
          }, 1000);
        }

        function clearTimer() {
          if (STATE.timerInterval) {
            clearInterval(STATE.timerInterval);
            STATE.timerInterval = null;
          }
          document.getElementById('ride-timer').style.display = 'none';
        }

        // ════════════════════════════════════════════════
        // NOTIFICATIONS
        // ════════════════════════════════════════════════
        function loadNotifCount() {
          fetch('/auth/notifications.php?action=count', {
              credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
              if (data.code === 0 && data.unread > 0) {
                const badge = document.getElementById('notif-badge');
                badge.style.display = 'flex';
                badge.textContent = data.unread > 9 ? '9+' : data.unread;
              }
            }).catch(() => {});
        }

        function openNotifs() {
          fetch('/auth/notifications.php?action=list', {
              credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
              if (data.code === 0) {
                const msgs = data.notifications.length ?
                  data.notifications.map(n => `[${n.type}] ${n.titre}\n${n.message}`).join('\n\n') :
                  'Aucune notification.';
                openSuccess(msgs);
                // Marquer toutes comme lues
                fetch('/auth/notifications.php?action=read', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({}),
                  credentials: 'include'
                }).then(() => {
                  document.getElementById('notif-badge').style.display = 'none';
                }).catch(() => {});
              }
            }).catch(() => {
              openError('Impossible de charger les notifications.');
            });
        }

        // ════════════════════════════════════════════════
        // SIDE MENU
        // ════════════════════════════════════════════════
        function toggleMenu() {
          document.getElementById('side-menu').classList.toggle('open');
          document.getElementById('menu-overlay').classList.toggle('open');
        }

        function changeVehicle(select) {
          document.getElementById('topbar-vehicle').textContent = select.options[select.selectedIndex].dataset.label;
          // TODO: appel API pour changer le véhicule actif en BDD
        }

        // ════════════════════════════════════════════════
        // POLLING (toutes les 10s si en attente)
        // ════════════════════════════════════════════════
        setInterval(() => {
          if (STATE.online && STATE.mode === 'waiting') loadNearbyRides();
          if (STATE.online) loadNotifCount();
        }, 10000);
      </script>

    <?php endif; ?>

    <?php include($inc . 'popupsBox.php'); ?>

    <script src="<?= $js ?>functions.js"></script>
  </div><!-- /.app -->
</body>

</html>