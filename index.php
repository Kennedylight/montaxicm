<?php

include('drivers/inc/main.php');
$isConnected = isset($_SESSION['id']);

if ($isConnected) include($inc . 'loginCli.php');

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= ccc("Mon Taxi CM • Reservez un moyen de transport rapidement et efficacement partout au Cameroun.", "Bienvenue sur mon Taxi CM • Connexion ou inscription.") ?></title>
  <meta name="description" content="<?= ccc("Mon Taxi CM est une plateforme de réservation de taxi en ligne qui vous permet de réserver un moyen de transport rapidement et efficacement partout au Cameroun. Avec Mon Taxi CM, vous pouvez facilement trouver un taxi à proximité, réserver votre trajet en quelques clics et profiter d'un service de qualité pour vos déplacements quotidiens ou occasionnels. Que ce soit pour aller au travail, faire du shopping ou visiter des amis, Mon Taxi CM est là pour vous offrir une expérience de transport pratique et fiable. Réservez dès maintenant et découvrez la commodité de voyager avec Mon Taxi CM !", "Inscrivez-vous ou créer votre compte facilement et simplement sur Mon Taxi CM.") ?>">

  <link rel="icon" href="<?= $img ?>fav.png">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#0a0a0a">
  <link rel="stylesheet" href="<?= $css ?>all.css">
  <link rel="stylesheet" href="<?= $css ?>popupsBox.css">
  <link rel="stylesheet" href="<?= $css ?>polices.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="<?= $css ?>style.css">
  <link rel="stylesheet" href="<?= $css ?>errorOrSuccessBox.css">

  <script src="<?= $js ?>router.js"></script>
</head>

<body>
  <main>
    <?php if ($isConnected): ?>
      <?php

      $clientId  = (int)$_SESSION['id'];
      $clientNom = $_SESSION['noms']  ?? 'Client';
      $clientPic = $_SESSION['pic']   ?? '';
      $clientEmail = $_SESSION['email'] ?? '';

      $initiale = mb_strtoupper(mb_substr($clientNom, 0, 1));
      ?>
      <style>
        *,
        *::before,
        *::after {
          box-sizing: border-box;
          margin: 0;
          padding: 0
        }

        :root {
          --noir: #0a0a0a;
          --blanc: #ffffff;
          --bg: #f7f6f2;
          --border: #e5e3de;
          --or: #f5cb17;
          --or2: #d4a900;
          --texte: #111;
          --doux: #777;
          --vert: #16a34a;
          --rouge: #dc2626;
          --bleu: #2563eb;
          --sh: 0 4px 24px rgba(0, 0, 0, .10);
          --sh2: 0 12px 40px rgba(0, 0, 0, .15);
          --r: 16px;
          --rb: 24px;
          --tab-h: 64px;
          --gris-clair: #0001;
        }

        html,
        body {
          height: 100%;
          overflow: hidden;
          font-family: Po02;
          background: var(--bg);
          color: var(--texte)
        }

        .app {
          display: flex;
          flex-direction: column;
          height: 100dvh;
          position: relative;
          overflow: hidden
        }

        button,
        select,
        input,
        textarea {
          font-family: Po02 !important;
        }

        /* ── MAP ── */
        #map {
          flex: 1;
          position: relative;
          z-index: 1
        }

        /* ── HEADER FLOTANT ── */
        .header {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          z-index: 20;
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 16px 16px 0;
          pointer-events: none;
        }

        .logo-pill {
          pointer-events: all;
          background: var(--blanc);
          border-radius: 50px;
          padding: 8px 16px 8px 10px;
          display: flex;
          align-items: center;
          gap: 8px;
          box-shadow: var(--sh);
          border: 1px solid var(--border);
        }

        .logo-pill img {
          height: 28px
        }

        .avatar-btn {
          pointer-events: all;
          width: 42px;
          height: 42px;
          border-radius: 50%;
          background: var(--noir);
          color: var(--or);
          border: 2.5px solid var(--or);
          display: flex;
          align-items: center;
          justify-content: center;
          font-family: Po01;
          font-weight: 800;
          font-size: 1rem;
          cursor: pointer;
          box-shadow: var(--sh);
          overflow: hidden;
          transition: transform .2s;
        }

        .avatar-btn:hover {
          transform: scale(1.05)
        }

        .avatar-btn img {
          width: 100%;
          height: 100%;
          object-fit: cover
        }

        /* ── BOTTOM SEARCH CARD ── */
        .search-card {
          position: absolute;
          bottom: calc(var(--tab-h) + 12px);
          left: 12px;
          right: 12px;
          z-index: 20;
          background: var(--blanc);
          border-radius: var(--rb);
          box-shadow: var(--sh2);
          border: 1px solid var(--border);
          padding: 18px 18px 14px;
          cursor: pointer;
          transition: transform .2s, box-shadow .2s;
        }

        .search-card:hover {
          transform: translateY(-2px);
          box-shadow: 0 16px 48px rgba(0, 0, 0, .18)
        }

        .sc-label {
          font-size: .72rem;
          color: var(--doux);
          font-weight: 500;
          letter-spacing: .04em;
          text-transform: uppercase;
          margin-bottom: 6px
        }

        .sc-content {
          display: flex;
          align-items: center;
          gap: 12px
        }

        .sc-icon {
          width: 40px;
          height: 40px;
          background: var(--noir);
          border-radius: 12px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.1rem;
          flex-shrink: 0
        }

        .sc-text {
          font-family: Po01;
          font-size: 1.05rem;
          font-weight: 700;
          color: var(--texte)
        }

        .sc-arrow {
          margin-left: auto;
          color: var(--doux);
          font-size: 1.1rem
        }

        /* ── BOTTOM TABS ── */
        .tabs {
          height: var(--tab-h);
          background: var(--blanc);
          border-top: 1px solid var(--border);
          display: flex;
          align-items: stretch;
          z-index: 30;
          flex-shrink: 0;
          position: fixed;
          bottom: 0;
          width: 100%;
        }

        .tab {
          flex: 1;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          gap: 3px;
          cursor: pointer;
          border: none;
          background: none;
          font-family: Po02;
          font-size: .66rem;
          font-weight: 500;
          color: var(--doux);
          transition: color .2s;
          position: relative;
        }

        .tab.active {
          color: var(--noir)
        }

        .tab-icon {
          font-size: 1.3rem;
          transition: transform .2s
        }

        .tab.active .tab-icon {
          transform: translateY(-1px)
        }

        .tab-dot {
          position: absolute;
          bottom: 6px;
          left: 50%;
          transform: translateX(-50%);
          width: 4px;
          height: 4px;
          border-radius: 50%;
          background: var(--or);
          opacity: 0;
          transition: opacity .2s;
        }

        .tab.active .tab-dot {
          opacity: 1
        }

        /* ── PANELS ── */
        .panel {
          display: none;
          flex: 1;
          overflow-y: auto;
          flex-direction: column;
          background: var(--bg)
        }

        .panel.active {
          display: flex
        }

        /* ── OVERLAY BOTTOM SHEET ── */
        .overlay {
          position: fixed;
          inset: 0;
          z-index: 50;
          background: rgba(0, 0, 0, .45);
          backdrop-filter: blur(3px);
          display: none;
          align-items: flex-end;
        }

        .overlay.open {
          display: flex
        }

        .sheet {
          width: 100%;
          background: var(--blanc);
          border-radius: 28px 28px 0 0;
          box-shadow: 0 -8px 40px rgba(0, 0, 0, .18);
          max-height: 92dvh;
          display: flex;
          flex-direction: column;
          animation: slideUp .35s cubic-bezier(.25, .8, .25, 1);
        }

        @keyframes slideUp {
          from {
            transform: translateY(100%)
          }

          to {
            transform: translateY(0)
          }
        }

        .sheet-handle {
          width: 36px;
          height: 4px;
          border-radius: 2px;
          background: var(--border);
          margin: 12px auto 0;
          flex-shrink: 0
        }

        .sheet-head {
          padding: 16px 20px 0;
          flex-shrink: 0
        }

        .sheet-scroll {
          flex: 1;
          overflow-y: auto;
          padding: 0 20px 32px
        }

        /* ── SEARCH INPUT ── */
        .search-wrap {
          position: relative;
          margin-bottom: 16px
        }

        .search-input {
          width: 100%;
          padding: 14px 48px 14px 48px;
          border-radius: 14px;
          border: 2px solid var(--border);
          font-family: Po02;
          font-size: 1rem;
          background: var(--bg);
          transition: border-color .2s;
          outline: none;
        }

        .search-input:focus {
          border-color: var(--noir)
        }

        .si-icon {
          position: absolute;
          left: 14px;
          top: 50%;
          transform: translateY(-50%);
          font-size: 1.1rem;
          pointer-events: none
        }

        .si-clear {
          position: absolute;
          right: 12px;
          top: 50%;
          transform: translateY(-50%);
          background: var(--border);
          border: none;
          border-radius: 50%;
          width: 22px;
          height: 22px;
          cursor: pointer;
          font-size: .75rem;
          display: none;
          align-items: center;
          justify-content: center
        }

        .si-clear.show {
          display: flex
        }

        /* Suggestions */
        .suggestions {
          display: flex;
          flex-direction: column;
          gap: 2px
        }

        .suggestion-item {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 12px;
          border-radius: 12px;
          cursor: pointer;
          transition: background .15s;
          border: 1px solid transparent;
        }

        .suggestion-item:hover {
          background: var(--bg);
          border-color: var(--border)
        }

        .sug-ico {
          width: 36px;
          height: 36px;
          border-radius: 10px;
          flex-shrink: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1rem;
        }

        .sug-ico.place {
          background: #f0fdf4;
          border: 1px solid #bbf7d0
        }

        .sug-ico.map {
          background: #eff6ff;
          border: 1px solid #bfdbfe
        }

        .sug-main {
          font-size: .88rem;
          font-weight: 500;
          line-height: 1.3
        }

        .sug-sub {
          font-size: .75rem;
          color: var(--doux)
        }

        .sug-dist {
          margin-left: auto;
          font-size: .72rem;
          color: var(--doux);
          white-space: nowrap
        }

        /* Sélection départ */
        .depart-indicator {
          display: flex;
          align-items: center;
          gap: 10px;
          padding: 10px 14px;
          background: var(--bg);
          border-radius: 12px;
          border: 1px solid var(--border);
          margin-bottom: 16px;
          font-size: .85rem;
        }

        .di-dot {
          width: 10px;
          height: 10px;
          border-radius: 50%;
          background: var(--vert);
          flex-shrink: 0
        }

        /* ── STEP 2 : Options de course ── */
        .step-title {
          font-family: Po01;
          font-size: 1.2rem;
          font-weight: 800;
          margin-bottom: 4px
        }

        .step-sub {
          font-size: .82rem;
          color: var(--doux);
          margin-bottom: 20px
        }

        /* Route summary */
        .route-summary {
          background: var(--bg);
          border-radius: 14px;
          border: 1px solid var(--border);
          padding: 14px;
          margin-bottom: 20px;
        }

        .rs-row {
          display: flex;
          align-items: center;
          gap: 10px;
          font-size: .85rem
        }

        .rs-ico {
          width: 20px;
          text-align: center;
          font-size: .9rem
        }

        .rs-dash {
          width: 1px;
          height: 14px;
          border-left: 2px dashed var(--border);
          margin-left: 9px
        }

        .rs-badge {
          margin-left: auto;
          background: var(--noir);
          color: var(--or);
          border-radius: 50px;
          padding: 3px 10px;
          font-size: .72rem;
          font-weight: 700
        }

        /* Véhicule */
        .section-label {
          font-size: .7rem;
          font-weight: 600;
          color: var(--doux);
          text-transform: uppercase;
          letter-spacing: .06em;
          margin-bottom: 10px
        }

        .vehicle-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 10px;
          margin-bottom: 20px
        }

        .vehicle-btn {
          border: 2px solid var(--border);
          border-radius: 14px;
          padding: 14px 12px;
          background: var(--blanc);
          cursor: pointer;
          text-align: center;
          transition: all .2s;
        }

        .vehicle-btn.active {
          border-color: var(--noir);
          background: #fafafa
        }

        .vb-ico {
          font-size: 1.8rem;
          margin-bottom: 6px
        }

        .vb-label {
          font-family: Po01;
          font-size: .88rem;
          font-weight: 700
        }

        .vb-sub {
          font-size: .72rem;
          color: var(--doux);
          margin-top: 2px
        }

        /* Places */
        .places-row {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 20px
        }

        .places-btn {
          width: 36px;
          height: 36px;
          border-radius: 50%;
          border: 1.5px solid var(--border);
          background: var(--blanc);
          font-size: 1.1rem;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: all .2s;
        }

        .places-btn:hover {
          border-color: var(--noir);
          background: var(--bg)
        }

        .places-count {
          font-family: Po01;
          font-size: 1.3rem;
          font-weight: 800;
          min-width: 32px;
          text-align: center
        }

        .places-label {
          font-size: .82rem;
          color: var(--doux)
        }

        /* Plans */
        .plans-grid {
          display: flex;
          flex-direction: column;
          gap: 10px;
          margin-bottom: 24px
        }

        .plan-card {
          border: 2px solid var(--border);
          border-radius: 14px;
          padding: 14px 16px;
          cursor: pointer;
          transition: all .2s;
          display: flex;
          align-items: center;
          gap: 14px;
          background: var(--blanc);
        }

        .plan-card.active {
          border-color: var(--noir);
          background: var(--noir);
          color: var(--blanc)
        }

        .pc-badge {
          width: 40px;
          height: 40px;
          border-radius: 10px;
          background: var(--bg);
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.2rem;
          flex-shrink: 0;
        }

        .plan-card.active .pc-badge {
          background: rgba(255, 255, 255, .12)
        }

        .pc-info {
          flex: 1
        }

        .pc-name {
          font-family: Po01;
          font-size: .9rem;
          font-weight: 700
        }

        .pc-desc {
          font-size: .75rem;
          color: var(--doux);
          margin-top: 2px
        }

        .plan-card.active .pc-desc {
          color: rgba(255, 255, 255, .6)
        }

        .pc-price {
          font-family: Po01;
          font-size: 1.1rem;
          font-weight: 800;
          color: var(--or2);
          white-space: nowrap;
        }

        .plan-card.active .pc-price {
          color: var(--or)
        }

        /* CTA Réserver */
        .btn-reserve {
          width: 100%;
          padding: 16px;
          border: none;
          border-radius: 16px;
          background: var(--noir);
          color: var(--blanc);
          font-family: Po01;
          font-size: 1rem;
          font-weight: 700;
          cursor: pointer;
          transition: all .25s;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 10px;
        }

        .btn-reserve:hover {
          background: #222;
          transform: translateY(-1px);
          box-shadow: 0 8px 24px rgba(0, 0, 0, .18)
        }

        .btn-reserve:disabled {
          background: #ccc;
          cursor: not-allowed;
          transform: none;
          box-shadow: none
        }

        /* ── STEP 3 : Recherche en cours ── */
        .searching-wrap {
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 32px 20px 40px;
          text-align: center;
        }

        .pulse-ring {
          width: 80px;
          height: 80px;
          border-radius: 50%;
          background: var(--noir);
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 2rem;
          position: relative;
          margin-bottom: 24px;
        }

        .pulse-ring::before,
        .pulse-ring::after {
          content: '';
          position: absolute;
          inset: 0;
          border-radius: 50%;
          border: 3px solid var(--or);
          animation: ring 2s ease-out infinite;
        }

        .pulse-ring::after {
          animation-delay: .7s
        }

        @keyframes ring {
          0% {
            transform: scale(1);
            opacity: .8
          }

          100% {
            transform: scale(2.2);
            opacity: 0
          }
        }

        .searching-title {
          font-family: Po01;
          font-size: 1.2rem;
          font-weight: 800;
          margin-bottom: 8px
        }

        .searching-sub {
          font-size: .85rem;
          color: var(--doux);
          line-height: 1.6;
          margin-bottom: 24px
        }

        .driver-found {
          width: 100%;
          background: var(--bg);
          border-radius: 16px;
          padding: 16px;
          border: 1px solid var(--border);
          margin-bottom: 12px;
          display: none;
        }

        .df-header {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 12px
        }

        .df-avatar {
          width: 44px;
          height: 44px;
          border-radius: 50%;
          background: var(--noir);
          color: var(--or);
          display: flex;
          align-items: center;
          justify-content: center;
          font-family: Po01;
          font-weight: 800
        }

        .df-name {
          font-family: Po01;
          font-weight: 700
        }

        .df-note {
          font-size: .78rem;
          color: var(--doux)
        }

        .df-vehicule {
          font-size: .8rem;
          color: var(--doux);
          margin-top: 2px
        }

        .df-eta {
          margin-left: auto;
          background: var(--noir);
          color: var(--or);
          border-radius: 50px;
          padding: 4px 12px;
          font-size: .78rem;
          font-weight: 700
        }

        .btn-cancel-ride {
          width: 100%;
          padding: 13px;
          border: 1.5px solid var(--border);
          border-radius: 14px;
          background: transparent;
          font-family: Po02;
          font-size: .9rem;
          cursor: pointer;
          color: var(--rouge);
          transition: all .2s
        }

        .btn-cancel-ride:hover {
          border-color: var(--rouge);
          background: #fef2f2
        }

        /* ── PANEL HISTORIQUE ── */
        .hist-header {
          padding: 24px 20px 16px;
          flex-shrink: 0;
          margin-top: 60px;
        }

        .hist-title {
          font-family: Po01;
          font-size: 1.3rem;
          font-weight: 800;
          margin-bottom: 4px
        }

        .hist-sub {
          font-size: .82rem;
          color: var(--doux)
        }

        .hist-list {
          padding: 0 16px 24px;
          display: flex;
          flex-direction: column;
          gap: 10px
        }

        .hist-card {
          background: var(--blanc);
          border-radius: 16px;
          border: 1px solid var(--border);
          padding: 16px;
          transition: box-shadow .2s;
        }

        .hist-card:hover {
          box-shadow: var(--sh)
        }

        .hc-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 10px
        }

        .hc-statut {
          display: inline-flex;
          align-items: center;
          gap: 5px;
          font-size: .72rem;
          font-weight: 600;
          border-radius: 50px;
          padding: 3px 10px
        }

        .hc-statut.terminee {
          background: #f0fdf4;
          color: #15803d
        }

        .hc-statut.annulee {
          background: #fef2f2;
          color: #dc2626
        }

        .hc-statut.en_attente {
          background: #fffbeb;
          color: #92400e
        }

        .hc-date {
          font-size: .72rem;
          color: var(--doux)
        }

        .hc-route {
          display: flex;
          flex-direction: column;
          gap: 4px;
          margin-bottom: 10px;
          padding: 10px;
          background: var(--bg);
          border-radius: 10px
        }

        .hc-pt {
          display: flex;
          align-items: center;
          gap: 8px;
          font-size: .8rem
        }

        .hc-pt-ico {
          width: 18px;
          text-align: center;
          font-size: .85rem
        }

        .hc-dash {
          width: 1px;
          height: 12px;
          border-left: 2px dashed var(--border);
          margin-left: 8px
        }

        .hc-footer {
          display: flex;
          align-items: center;
          justify-content: space-between
        }

        .hc-plan {
          font-size: .72rem;
          color: var(--doux);
          background: var(--bg);
          border-radius: 50px;
          padding: 2px 8px;
          border: 1px solid var(--border)
        }

        .hc-prix {
          font-family: Po01;
          font-size: 1rem;
          font-weight: 800
        }

        /* ── PANEL PARAMÈTRES ── */
        .param-header {
          padding: 24px 20px 16px;
          flex-shrink: 0
        }

        .param-title {
          font-family: Po01;
          font-size: 1.3rem;
          font-weight: 800;
          margin-bottom: 4px
        }

        .profile-card {
          margin: 0 16px 16px;
          background: var(--blanc);
          border-radius: 20px;
          border: 1px solid var(--border);
          padding: 24px;
          display: flex;
          align-items: center;
          gap: 16px;
        }

        .pc-avatar {
          width: 56px;
          height: 56px;
          border-radius: 50%;
          background: var(--noir);
          color: var(--or);
          display: flex;
          align-items: center;
          justify-content: center;
          font-family: Po01;
          font-weight: 800;
          font-size: 1.4rem;
          overflow: hidden;
          flex-shrink: 0;
        }

        .pc-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover
        }

        .pc-infos .pc-name {
          font-family: Po01;
          font-weight: 800;
          font-size: 1.05rem
        }

        .pc-infos .pc-email {
          font-size: .8rem;
          color: var(--doux);
          margin-top: 2px
        }

        .param-list {
          padding: 0 16px 24px;
          display: flex;
          flex-direction: column;
          gap: 8px
        }

        .param-item {
          background: var(--blanc);
          border-radius: 14px;
          border: 1px solid var(--border);
          padding: 14px 16px;
          display: flex;
          align-items: center;
          gap: 12px;
          cursor: pointer;
          transition: background .15s;
        }

        .param-item:hover {
          background: var(--bg)
        }

        .pi-ico {
          font-size: 1.2rem;
          width: 28px;
          text-align: center
        }

        .pi-label {
          font-size: .9rem;
          font-weight: 500
        }

        .pi-arrow {
          margin-left: auto;
          color: var(--doux)
        }

        .param-item.danger .pi-label {
          color: var(--rouge)
        }

        /* ── TOAST ── */
        .toast {
          position: fixed;
          top: 20px;
          left: 50%;
          transform: translateX(-50%) translateY(-80px);
          z-index: 200;
          background: var(--noir);
          color: var(--blanc);
          border-radius: 50px;
          padding: 10px 20px;
          font-size: .85rem;
          font-weight: 500;
          box-shadow: var(--sh2);
          transition: transform .35s cubic-bezier(.25, .8, .25, 1);
          white-space: nowrap;
        }

        .toast.show {
          transform: translateX(-50%) translateY(0)
        }

        .toast.error {
          background: var(--rouge)
        }

        .toast.success {
          background: var(--vert)
        }

        /* Loader spin */
        .spin {
          animation: spin .7s linear infinite;
          display: inline-block
        }

        @keyframes spin {
          to {
            transform: rotate(360deg)
          }
        }

        /* Marqueur chauffeur live sur la carte */
        .driver-live-label {
          background: var(--noir);
          color: var(--or);
          font-size: .7rem;
          font-weight: 700;
          padding: 3px 8px;
          border-radius: 50px;
          white-space: nowrap;
          box-shadow: 0 2px 8px rgba(0, 0, 0, .25);
        }

        /* Bouton annuler dans historique */
        .btn-cancel-hist {
          margin-top: 10px;
          width: 100%;
          padding: 10px;
          border: 1.5px solid #fecaca;
          border-radius: 12px;
          background: transparent;
          color: var(--rouge);
          font-family: Po02;
          font-size: .85rem;
          cursor: pointer;
          transition: all .2s;
        }

        .btn-cancel-hist:hover {
          background: #fef2f2;
        }
      </style>
      <div class="app">

        <!-- ── MAP ── -->
        <div id="map"></div>

        <!-- ── HEADER ── -->
        <div class="header">
          <div class="logo-pill">
            <img src="<?= $img ?>logo2.png" alt="MonTaxi">
          </div>
          <div class="avatar-btn" onclick="showTab('params')" title="Mon profil">
            <?php if ($clientPic): ?>
              <img src="<?= htmlspecialchars($clientPic) ?>" alt="">
            <?php else: ?>
              <?= $initiale ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- ── SEARCH CARD (visible sur accueil) ── -->
        <div class="search-card" id="search-card" onclick="openSearchSheet()">
          <div class="sc-label">Prochaine course</div>
          <div class="sc-content">
            <div class="sc-icon">🚖</div>
            <div class="sc-text">Où nous rendons-nous ?</div>
            <div class="sc-arrow">›</div>
          </div>
        </div>

        <!-- ── TABS ── -->
        <div class="tabs">
          <button class="tab active" id="tab-home" onclick="showTab('home')">
            <div class="tab-icon">🏠</div>
            <span>Accueil</span>
            <div class="tab-dot"></div>
          </button>
          <button class="tab" id="tab-hist" onclick="showTab('hist')">
            <div class="tab-icon">🧾</div>
            <span>Mes courses</span>
            <div class="tab-dot"></div>
          </button>
          <button class="tab" id="tab-params" onclick="showTab('params')">
            <div class="tab-icon">⚙️</div>
            <span>Paramètres</span>
            <div class="tab-dot"></div>
          </button>
        </div>

        <!-- ── HISTORIQUE PANEL ── -->
        <div class="panel" id="panel-hist">
          <div class="hist-header">
            <div class="hist-title">Mes courses</div>
            <div class="hist-sub">Retrouvez tout votre historique</div>
          </div>
          <div class="hist-list" id="hist-list">
            <div style="text-align:center;padding:40px;color:var(--doux)">Chargement…</div>
          </div>
        </div>

        <!-- ── PARAMÈTRES PANEL ── -->
        <div class="panel" id="panel-params">
          <div class="param-header">
            <div class="param-title">Mon compte</div>
          </div>
          <!-- profil résumé -->
          <div class="profile-card" id="profile-card-summary">
            <div class="pc-avatar" id="pp-avatar">
              <!-- mis à jour dynamiquement par JS -->
            </div>
            <div class="pc-infos">
              <div class="pc-name" id="pp-nom"><?= htmlspecialchars($clientNom) ?></div>
              <div class="pc-email"><?= htmlspecialchars($clientEmail) ?></div>
            </div>
          </div>
          <div class="param-list">
            <div class="param-item" onclick="openModalProfil()">
              <span class="pi-ico">👤</span>
              <span class="pi-label">Modifier mon profil</span>
              <span class="pi-arrow">›</span>
            </div>
            <div class="param-item" onclick="openModalSecurite()">
              <span class="pi-ico">🔒</span>
              <span class="pi-label">Sécurité & mot de passe</span>
              <span class="pi-arrow">›</span>
            </div>
            <div class="param-item">
              <span class="pi-ico">🔔</span>
              <span class="pi-label">Notifications</span>
              <span class="pi-arrow">›</span>
            </div>
            <div class="param-item">
              <span class="pi-ico">❓</span>
              <span class="pi-label">Aide & Support</span>
              <span class="pi-arrow">›</span>
            </div>
            <div class="param-item danger" onclick="window.location='/auth/logout.php'">
              <span class="pi-ico">🚪</span>
              <span class="pi-label">Se déconnecter</span>
            </div>
          </div>
        </div>

      </div>

      <!-- ════════ OVERLAY SHEET ════════ -->
      <div class="overlay" id="overlay" onclick="maybeCloseOverlay(event)">
        <div class="sheet" id="sheet">
          <div class="sheet-handle"></div>
          <div class="sheet-head">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
              <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem" id="sheet-title">Où allons-nous ?</div>
              <button onclick="closeSheet()" style="background:var(--bg);border:1px solid var(--border);border-radius:50%;width:30px;height:30px;cursor:pointer;font-size:.9rem;display:flex;align-items:center;justify-content:center">✕</button>
            </div>
          </div>
          <div class="sheet-scroll" id="sheet-scroll">

            <!-- STEP 1 : Recherche destination -->
            <div id="step-search">
              <div class="depart-indicator" id="depart-indicator">
                <div class="di-dot"></div>
                <span id="depart-text">Localisation en cours…</span>
              </div>
              <div class="search-wrap">
                <span class="si-icon">🔍</span>
                <input type="text" class="search-input" id="dest-input" placeholder="Entrez votre destination…" autocomplete="off" oninput="onSearchInput(this.value)" />
                <button class="si-clear" id="si-clear" onclick="clearSearch()">✕</button>
              </div>
              <div class="suggestions" id="suggestions-list">
                <div style="text-align:center;color:var(--doux);font-size:.85rem;padding:24px">
                  Commencez à saisir votre destination
                </div>
              </div>
            </div>

            <!-- STEP 2 : Options course -->
            <div id="step-options" style="display:none">
              <div class="step-title">Configurez votre course</div>
              <div class="step-sub" id="step-options-sub">Ajustez selon vos préférences</div>

              <div class="route-summary" id="route-summary">
                <div class="rs-row"><span class="rs-ico">🟢</span><span id="rs-depart">Ma position</span></div>
                <div class="rs-dash"></div>
                <div class="rs-row"><span class="rs-ico">🔴</span><span id="rs-arrivee">Destination</span><span class="rs-badge" id="rs-dist">—</span></div>
              </div>

              <div class="section-label">Type de véhicule</div>
              <div class="vehicle-grid">
                <button class="vehicle-btn active" id="vbtn-taxi" onclick="selectVehicule('taxi')">
                  <div class="vb-ico">🚖</div>
                  <div class="vb-label">Taxi</div>
                  <div class="vb-sub">Berline confort</div>
                </button>
                <button class="vehicle-btn" id="vbtn-moto" onclick="selectVehicule('moto')">
                  <div class="vb-ico">🏍️</div>
                  <div class="vb-label">Moto</div>
                  <div class="vb-sub">Rapide en ville</div>
                </button>
              </div>

              <div class="section-label">Nombre de places</div>
              <div class="places-row" id="places-row">
                <button class="places-btn" onclick="changePlaces(-1)">−</button>
                <div class="places-count" id="places-count">1</div>
                <button class="places-btn" onclick="changePlaces(1)">+</button>
                <span class="places-label">place(s) réservée(s)</span>
              </div>

              <div class="section-label">Choisissez votre forfait</div>
              <div class="plans-grid" id="plans-grid">
                <!-- rempli dynamiquement -->
              </div>

              <button class="btn-reserve" id="btn-reserve" onclick="createCourse()">
                🚖 Confirmer ma course
              </button>
            </div>

            <!-- STEP 3 : Recherche chauffeur -->
            <!-- Step 3 : Recherche / Suivi en temps réel -->
            <div id="step-searching" style="display:none">

              <!-- Vue : recherche en cours -->
              <div id="view-searching">
                <div class="searching-wrap">
                  <div class="pulse-ring">🚖</div>
                  <div class="searching-title" id="searching-title">Recherche de chauffeur…</div>
                  <div class="searching-sub" id="searching-sub">
                    Nous contactons les chauffeurs autour de vous.<br>Cela prend en général moins de 2 minutes.
                  </div>
                  <button class="btn-cancel-ride" id="btn-cancel-searching" onclick="cancelCourse()">
                    Annuler la course
                  </button>
                </div>
              </div>

              <!-- Vue : chauffeur en route vers le client -->
              <div id="view-en-route" style="display:none">
                <div style="background:linear-gradient(135deg,#0a0a0a,#1a1a1a);border-radius:18px;
      padding:20px;color:#fff;margin-bottom:12px">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                    <div style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);
          border-radius:50px;padding:5px 14px;font-size:.78rem;font-weight:600">
                      <div style="width:8px;height:8px;border-radius:50%;background:var(--or);
            animation:pulse 1.5s infinite"></div>
                      En route vers vous
                    </div>
                    <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;
          color:var(--or)" id="track-prix"></div>
                  </div>

                  <!-- Infos chauffeur -->
                  <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                    <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.1);
          display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;
          font-weight:800;font-size:1.1rem;color:var(--or)" id="track-avatar">?</div>
                    <div>
                      <div style="font-weight:600;font-size:.95rem" id="track-nom">—</div>
                      <div style="font-size:.75rem;color:rgba(255,255,255,.55)" id="track-note">⭐ —</div>
                      <div style="font-size:.72rem;color:rgba(255,255,255,.45)" id="track-vehicule">—</div>
                    </div>
                    <a id="track-call" href="#" style="margin-left:auto;width:38px;height:38px;
          background:var(--or);color:#000;border-radius:50%;display:flex;
          align-items:center;justify-content:center;font-size:1rem;text-decoration:none">📞</a>
                  </div>

                  <!-- Trajet résumé -->
                  <div style="background:rgba(255,255,255,.07);border-radius:12px;padding:12px">
                    <div style="display:flex;align-items:center;gap:8px;font-size:.82rem;
          color:rgba(255,255,255,.8)">
                      <span style="font-size:.85rem">📍</span>
                      <span id="track-depart">—</span>
                    </div>
                    <div style="width:1px;height:13px;border-left:2px dashed rgba(255,255,255,.2);
          margin-left:7px;margin:4px 0 4px 7px"></div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:.82rem;
          color:rgba(255,255,255,.8)">
                      <span style="font-size:.85rem">🏁</span>
                      <span id="track-arrivee">—</span>
                    </div>
                  </div>

                  <!-- ETA -->
                  <div style="margin-top:14px;text-align:center;font-size:.8rem;
        color:rgba(255,255,255,.5)">
                    Arrivée estimée : <strong style="color:var(--or)" id="track-eta">—</strong>
                  </div>
                </div>
              </div>

              <!-- Vue : client à bord, trajet en cours -->
              <div id="view-in-ride" style="display:none">
                <div style="background:linear-gradient(135deg,#0a0a0a,#1a1a1a);border-radius:18px;
      padding:20px;color:#fff;margin-bottom:12px">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                    <div style="display:flex;align-items:center;gap:8px;background:rgba(34,197,94,.15);
          border-radius:50px;padding:5px 14px;font-size:.78rem;font-weight:600;color:#86efac">
                      <div style="width:8px;height:8px;border-radius:50%;background:#22c55e;
            animation:pulse 1.5s infinite"></div>
                      Course en cours
                    </div>
                    <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;
          color:var(--or)" id="inride-prix"></div>
                  </div>

                  <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                    <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.1);
          display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;
          font-weight:800;font-size:1.1rem;color:var(--or)" id="inride-avatar">?</div>
                    <div>
                      <div style="font-weight:600;font-size:.95rem" id="inride-nom">—</div>
                      <div style="font-size:.75rem;color:rgba(255,255,255,.55)" id="inride-vehicule">—</div>
                    </div>
                    <a id="inride-call" href="#" style="margin-left:auto;width:38px;height:38px;
          background:var(--or);color:#000;border-radius:50%;display:flex;
          align-items:center;justify-content:center;font-size:1rem;text-decoration:none">📞</a>
                  </div>

                  <div style="background:rgba(255,255,255,.07);border-radius:12px;padding:12px">
                    <div style="display:flex;align-items:center;gap:8px;font-size:.82rem;
          color:rgba(255,255,255,.8)">
                      <span>🟡</span> <span id="inride-depart">—</span>
                    </div>
                    <div style="width:1px;height:13px;border-left:2px dashed rgba(255,255,255,.2);
          margin:4px 0 4px 7px"></div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:.82rem;
          color:rgba(255,255,255,.8)">
                      <span>🏁</span> <span id="inride-arrivee">—</span>
                    </div>
                  </div>

                  <div style="margin-top:14px;text-align:center;font-size:.8rem;color:rgba(255,255,255,.5)">
                    Destination estimée dans <strong style="color:#86efac" id="inride-eta">—</strong>
                  </div>
                </div>
              </div>

            </div>

          </div><!-- /.sheet-scroll -->
        </div><!-- /.sheet -->
      </div><!-- /.overlay -->

      <span class="mio ussdCall" style="padding: 8px; border-radius: 50%; background: #fff; color: gold; cursor: pointer; position: fixed; bottom: 200px; box-shadow: 1px 1px 10px #0001; right: 10px;  z-index: 30;" onclick="document.querySelector('.ussd-simulator').classList.toggle('active');">phone</span>

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

      <style>
        .ussd-simulator {
          background: #222;
          padding: 20px;
          border-radius: 20px;
          width: 280px;
          margin: auto;
          position: fixed;
          left: 50%;
          z-index: 30;
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

      <!-- TOAST -->
      <div class="toast" id="toast"></div>

      <script>
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
            alert("Code non reconnu. Essaye *237# ou *237*112# (SOS)");
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

          console.log("Session USSD terminée.");
        }
      </script>

      <!-- GOOGLE MAPS -->
      <script src="https://maps.googleapis.com/maps/api/js?key=<?= $gmaps_key ?? '' ?>&libraries=places&callback=initMap" async defer></script>
      <script>
        // ══════════════════════════════════════════════════
        // DONNÉES PHP
        // ══════════════════════════════════════════════════
        const CLIENT = <?= json_encode([
                          'id'    => $clientId,
                          'nom'   => $clientNom,
                          'email' => $clientEmail,
                        ]) ?>;

        // ══════════════════════════════════════════════════
        // STATE
        // ══════════════════════════════════════════════════
        const S = {
          userPos: null, // {lat,lng} position du client
          gpsReady: false,
          grille: [], // tranches tarifaires
          plans: [], // plans + facteurs
          selectedDest: null, // {lat,lng,adresse,distanceM}
          selectedPlan: null, // objet plan choisi
          typeVehicule: 'taxi',
          nbPlaces: 1,
          currentCourse: null, // {id, prix, duree}
          searchTimer: null,
          pollInterval: null,
          drivers: [], // marqueurs chauffeurs sur carte
          driverMarker: null, // marqueur chauffeur assigné
        };

        let map, userMarker, destMarker, autocomplete;
        let driverMarkers = [];

        // ══════════════════════════════════════════════════
        // INIT MAP
        // ══════════════════════════════════════════════════
        function initMap() {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
              S.userPos = {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude
              };
              S.gpsReady = true;
              buildMap();
            }, () => {
              S.userPos = {
                lat: 3.8480,
                lng: 11.5021
              };
              buildMap();
            }, {
              enableHighAccuracy: true,
              timeout: 8000
            });
          } else {
            S.userPos = {
              lat: 3.8480,
              lng: 11.5021
            };
            buildMap();
          }
        }

        function buildMap() {
          map = new google.maps.Map(document.getElementById('map'), {
            center: S.userPos,
            zoom: 15,
            disableDefaultUI: true,
            gestureHandling:  'greedy',
            styles: [{
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{
                  visibility: 'off'
                }]
              },
              {
                featureType: 'transit',
                stylers: [{
                  visibility: 'off'
                }]
              }
            ]
          });

          // Marqueur client (point bleu)
          userMarker = new google.maps.Marker({
            position: S.userPos,
            map,
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              scale: 9,
              fillColor: '#2563eb',
              fillOpacity: 1,
              strokeWeight: 3,
              strokeColor: '#fff',
            },
            zIndex: 999,
            title: 'Ma position',
          });

          // MAJ position en continu
          navigator.geolocation?.watchPosition(pos => {
            S.userPos = {
              lat: pos.coords.latitude,
              lng: pos.coords.longitude
            };
            S.gpsReady = true;
            userMarker?.setPosition(S.userPos);
            document.getElementById('depart-text').textContent = 'Ma position actuelle';
          }, null, {
            enableHighAccuracy: true,
            maximumAge: 5000
          });

          document.getElementById('depart-text').textContent = S.gpsReady ? 'Ma position actuelle' : 'Position approx. (GPS indisponible)';

          // Charger la grille tarifaire
          loadTarifs();
          restoreActiveCourse();
        }

        // ══════════════════════════════════════════════════
        // RESTAURATION COURSE ACTIVE AU CHARGEMENT
        // ══════════════════════════════════════════════════
        async function restoreActiveCourse() {
          try {
            const res = await fetch(`${auth}booking.php?action=active`);
            const data = await res.json();
            if (data.code !== 0 || !data.course) return;

            const course = data.course;
            S.currentCourse = {
              id: course.id,
              prix: course.prix,
              duree: course.duree,
            };
            S.selectedDest = {
              lat: course.arrivee_lat,
              lng: course.arrivee_lng,
              adresse: course.arrivee_adresse,
            };

            // Placer les marqueurs départ / arrivée
            placeDestMarker(course.arrivee_lat, course.arrivee_lng, course.arrivee_adresse);

            // Ouvrir la sheet sur le bon état
            document.getElementById('overlay').classList.add('open');
            showStep('searching');
            document.getElementById('sh-title').textContent = 'Votre course';

            if (course.statut === 'en_attente') {
              showSearchingView('searching');
              startPolling(course.id);

            } else if (course.statut === 'acceptee') {
              showSearchingView('en-route');
              populateTrackingUI(course);
              placeDriverMarker(course.chauffeur.lat, course.chauffeur.lng, course.chauffeur.nom);
              drawLiveRoute({
                lat: course.chauffeur.lat,
                lng: course.chauffeur.lng
              }, {
                lat: course.depart_lat,
                lng: course.depart_lng
              });
              startPolling(course.id);

            } else if (course.statut === 'en_cours') {
              showSearchingView('in-ride');
              populateInRideUI(course);
              placeDriverMarker(course.chauffeur.lat, course.chauffeur.lng, course.chauffeur.nom);
              drawLiveRoute({
                lat: course.chauffeur.lat,
                lng: course.chauffeur.lng
              }, {
                lat: course.arrivee_lat,
                lng: course.arrivee_lng
              });
              startPolling(course.id);
            }

          } catch (e) {
            /* pas de course active */
          }
        }

        // ══════════════════════════════════════════════════
        // TARIFS
        // ══════════════════════════════════════════════════
        function loadTarifs() {
          fetch(`${auth}booking.php?action=tarifs`)
            .then(r => r.json())
            .then(d => {
              if (d.code === 0) {
                S.grille = d.grille;
                S.plans = d.plans;
              }
            }).catch(() => {});
        }

        function calcPrix(distM, facteur) {
          if (distM > 9000) return null;
          const tranche = S.grille.find(t => distM >= parseInt(t.dist_min) && distM <= parseInt(t.dist_max));
          if (!tranche) return null;
          return Math.ceil(parseInt(tranche.prix_base) * facteur);
        }

        // ══════════════════════════════════════════════════
        // TABS
        // ══════════════════════════════════════════════════
        function showTab(tab) {
          document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
          document.querySelectorAll('.panel').forEach(p => {
            p.classList.remove('active');
            p.style.display = 'none';
          });

          const isHome = tab === 'home';
          document.getElementById('map').style.display = isHome ? 'flex' : 'none';
          document.getElementById('search-card').style.display = isHome ? '' : 'none';
          document.getElementById('tab-' + (tab === 'params' ? 'params' : tab === 'hist' ? 'hist' : 'home')).classList.add('active');

          if (tab === 'hist') {
            const p = document.getElementById('panel-hist');
            p.style.display = 'flex';
            p.classList.add('active');
            loadHistory();
            document.getElementById('tab-hist').classList.add('active');
          } else if (tab === 'params') {
            const p = document.getElementById('panel-params');
            p.style.display = 'flex';
            p.classList.add('active');
            document.getElementById('tab-params').classList.add('active');
          } else {
            document.getElementById('tab-home').classList.add('active');
            document.getElementById('map').style.display = '';
            document.getElementById('search-card').style.display = '';
          }
        }

        // ══════════════════════════════════════════════════
        // SHEET : OUVRIR / FERMER
        // ══════════════════════════════════════════════════
        function openSearchSheet() {
          showStep('search');
          document.getElementById('overlay').classList.add('open');
          document.getElementById('sheet-title').textContent = 'Où allons-nous ?';
          setTimeout(() => document.getElementById('dest-input').focus(), 400);
        }

        function closeSheet() {
          document.getElementById('overlay').classList.remove('open');
          if (S.currentCourse && S.pollInterval) return; // pas fermer si course active
        }

        function maybeCloseOverlay(e) {
          if (e.target === document.getElementById('overlay')) closeSheet();
        }

        // Remplace l'ancienne showStep() :
        function showStep(step) {
          ['search', 'options', 'searching'].forEach(s => {
            document.getElementById('step-' + s).style.display = s === step ? '' : 'none';
          });
        }

        // Nouvelle : bascule entre les 3 vues dans step-searching
        function showSearchingView(view) {
          // view = 'searching' | 'en-route' | 'in-ride'
          document.getElementById('view-searching').style.display = view === 'searching' ? '' : 'none';
          document.getElementById('view-en-route').style.display = view === 'en-route' ? '' : 'none';
          document.getElementById('view-in-ride').style.display = view === 'in-ride' ? '' : 'none';
        }

        // ══════════════════════════════════════════════════
        // STEP 1 : RECHERCHE AVEC AUTOCOMPLETE GOOGLE
        // ══════════════════════════════════════════════════
        let autocompleteService, geocoder;

        function onSearchInput(val) {
          const clear = document.getElementById('si-clear');
          clear.classList.toggle('show', val.length > 0);
          if (val.length < 2) {
            showDefaultSuggestions();
            return;
          }

          clearTimeout(S.searchTimer);
          S.searchTimer = setTimeout(() => {
            if (!autocompleteService) autocompleteService = new google.maps.places.AutocompleteService();
            const bounds = S.userPos ? new google.maps.Circle({
              center: S.userPos,
              radius: 15000
            }).getBounds() : null;
            autocompleteService.getPlacePredictions({
              input: val,
              componentRestrictions: {
                country: 'cm'
              },
              locationBias: bounds,
              types: ['geocode', 'establishment'],
            }, (predictions, status) => {
              if (status !== 'OK' || !predictions) {
                renderSuggestions([], val);
                return;
              }
              renderSuggestions(predictions.slice(0, 7), val);
            });
          }, 280);
        }

        function showDefaultSuggestions() {
          document.getElementById('suggestions-list').innerHTML = `
    <div style="text-align:center;color:var(--doux);font-size:.85rem;padding:24px">
      Commencez à saisir votre destination
    </div>`;
        }

        function renderSuggestions(predictions, query) {
          const list = document.getElementById('suggestions-list');
          let html = predictions.map(p => `
    <div class="suggestion-item" onclick="selectPlace('${escQ(p.place_id)}','${escQ(p.description)}')">
      <div class="sug-ico place">📍</div>
      <div>
        <div class="sug-main">${highlight(p.structured_formatting?.main_text || p.description, query)}</div>
        <div class="sug-sub">${p.structured_formatting?.secondary_text || ''}</div>
      </div>
    </div>`).join('');

          // Option "Rechercher sur la carte"
          html += `
    <div class="suggestion-item" onclick="searchOnMap('${escQ(query)}')">
      <div class="sug-ico map">🗺️</div>
      <div><div class="sug-main">Rechercher sur la carte</div><div class="sug-sub">${escH(query)}</div></div>
    </div>`;
          list.innerHTML = html;
        }

        function selectPlace(placeId, description) {
          if (!geocoder) geocoder = new google.maps.Geocoder();
          geocoder.geocode({
            placeId
          }, (results, status) => {
            if (status !== 'OK' || !results[0]) {
              showToast('Lieu introuvable', 'error');
              return;
            }
            const loc = results[0].geometry.location;
            onDestSelected(loc.lat(), loc.lng(), description);
          });
        }

        function searchOnMap(query) {
          if (!geocoder) geocoder = new google.maps.Geocoder();
          geocoder.geocode({
            address: query + ', Cameroun'
          }, (results, status) => {
            if (status !== 'OK' || !results[0]) {
              showToast('Lieu introuvable', 'error');
              return;
            }
            const loc = results[0].geometry.location;
            onDestSelected(loc.lat(), loc.lng(), results[0].formatted_address);
            map.panTo({
              lat: loc.lat(),
              lng: loc.lng()
            });
            map.setZoom(16);
          });
        }

        function onDestSelected(lat, lng, adresse) {
          if (!S.userPos) {
            showToast('Position GPS non disponible', 'error');
            return;
          }

          const distM = Math.round(google.maps.geometry ?
            google.maps.geometry.spherical.computeDistanceBetween(
              new google.maps.LatLng(S.userPos.lat, S.userPos.lng),
              new google.maps.LatLng(lat, lng)) :
            haversineM(S.userPos.lat, S.userPos.lng, lat, lng));

          if (distM > 9000) {
            showToast('Destination trop éloignée (max 9 km)', 'error');
            return;
          }
          if (distM < 50) {
            showToast('Destination trop proche', 'error');
            return;
          }

          S.selectedDest = {
            lat,
            lng,
            adresse,
            distanceM: distM
          };

          // Marqueur destination sur la carte
          destMarker?.setMap(null);
          destMarker = new google.maps.Marker({
            position: {
              lat,
              lng
            },
            map,
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              scale: 9,
              fillColor: '#dc2626',
              fillOpacity: 1,
              strokeWeight: 3,
              strokeColor: '#fff',
            },
            title: adresse,
          });
          map.panTo({
            lat: (S.userPos.lat + lat) / 2,
            lng: (S.userPos.lng + lng) / 2
          });

          openOptionsStep(distM, adresse);
        }

        function clearSearch() {
          document.getElementById('dest-input').value = '';
          document.getElementById('si-clear').classList.remove('show');
          showDefaultSuggestions();
          document.getElementById('dest-input').focus();
        }

        // ══════════════════════════════════════════════════
        // STEP 2 : OPTIONS
        // ══════════════════════════════════════════════════
        function openOptionsStep(distM, adresse) {
          showStep('options');
          document.getElementById('sheet-title').textContent = 'Votre course';

          const distText = distM >= 1000 ? (distM / 1000).toFixed(1) + ' km' : distM + ' m';
          document.getElementById('step-options-sub').textContent = `Distance estimée : ${distText}`;
          document.getElementById('rs-depart').textContent = 'Ma position actuelle';
          document.getElementById('rs-arrivee').textContent = adresse;
          document.getElementById('rs-dist').textContent = distText;

          renderPlans(distM);
        }

        function renderPlans(distM) {
          if (!S.plans.length) {
            loadTarifs();
            setTimeout(() => renderPlans(distM), 500);
            return;
          }

          const icons = {
            classique: '🚖',
            prestige: '⭐',
            prestige_plus: '💎'
          };
          const descs = {
            classique: 'Confort standard, trajet simple',
            prestige: 'Véhicule premium, chauffeur expérimenté',
            prestige_plus: 'Expérience 5 étoiles, service exclusif'
          };

          let html = '';
          S.plans.forEach((p, i) => {
            const prix = calcPrix(distM, parseFloat(p.facteur));
            const active = i === 0 ? 'active' : '';
            if (i === 0 && !S.selectedPlan) S.selectedPlan = p;
            html += `
      <div class="plan-card ${active}" id="plan-${p.slug}" onclick="selectPlan('${p.slug}')">
        <div class="pc-badge">${icons[p.slug] || '🚖'}</div>
        <div class="pc-info">
          <div class="pc-name">${p.nom_plan}</div>
          <div class="pc-desc">${descs[p.slug] || ''}</div>
        </div>
        <div class="pc-price">${prix !== null ? prix.toLocaleString('fr-FR') + ' FCFA' : '—'}</div>
      </div>`;
          });
          document.getElementById('plans-grid').innerHTML = html;
        }

        function selectPlan(slug) {
          S.selectedPlan = S.plans.find(p => p.slug === slug);
          document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('active'));
          document.getElementById('plan-' + slug)?.classList.add('active');
        }

        function selectVehicule(type) {
          S.typeVehicule = type;
          document.getElementById('vbtn-taxi').classList.toggle('active', type === 'taxi');
          document.getElementById('vbtn-moto').classList.toggle('active', type === 'moto');
          // Moto = 1 place max
          if (type === 'moto') {
            S.nbPlaces = 1;
            document.getElementById('places-count').textContent = 1;
            document.getElementById('places-row').style.opacity = '.4';
            document.getElementById('places-row').style.pointerEvents = 'none';
          } else {
            document.getElementById('places-row').style.opacity = '1';
            document.getElementById('places-row').style.pointerEvents = '';
          }
        }

        function changePlaces(delta) {
          S.nbPlaces = Math.max(1, Math.min(6, S.nbPlaces + delta));
          document.getElementById('places-count').textContent = S.nbPlaces;
        }

        // ══════════════════════════════════════════════════
        // STEP 3 : CRÉER + POLLING
        // ══════════════════════════════════════════════════
        async function createCourse() {
          if (!S.selectedDest || !S.selectedPlan) {
            showToast('Sélectionnez une destination et un plan', 'error');
            return;
          }
          if (!S.userPos) {
            showToast('Position GPS introuvable', 'error');
            return;
          }

          const btn = document.getElementById('btn-reserve');
          btn.disabled = true;
          btn.innerHTML = '<span class="spin">⟳</span> Création en cours…';

          try {
            const res = await fetch(`${auth}booking.php?action=create`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                depart_lat: S.userPos.lat,
                depart_lng: S.userPos.lng,
                depart_adresse: 'Ma position actuelle',
                arrivee_lat: S.selectedDest.lat,
                arrivee_lng: S.selectedDest.lng,
                arrivee_adresse: S.selectedDest.adresse,
                distance_m: S.selectedDest.distanceM,
                plan_slug: S.selectedPlan.slug,
                type_vehicule: S.typeVehicule,
                nb_places: S.nbPlaces,
              }),
            });
            const data = await res.json();

            if (data.code !== 0) {
              showToast(data.message, 'error');
              btn.disabled = false;
              btn.innerHTML = '🚖 Confirmer ma course';
              return;
            }

            S.currentCourse = {
              id: data.course_id,
              prix: data.prix,
              duree: data.duree
            };

            // Passer en step searching
            showStep('searching');
            showSearchingView('searching'); // ← remplace l'ancien showStep
            document.getElementById('sh-title').textContent = 'Recherche en cours';
            startPolling(data.course_id);

          } catch (e) {
            showToast('Erreur réseau, réessayez', 'error');
            btn.disabled = false;
            btn.innerHTML = '🚖 Confirmer ma course';
          }
        }

        // ── Polling du statut ─────────────────────────────
        function startPolling(courseId) {
          clearInterval(S.pollInterval);
          S.pollInterval = setInterval(() => pollStatus(courseId), 4000);
          pollStatus(courseId); // immédiat
        }

        // ── Marqueur chauffeur live ───────────────────────
        let chauffeurMarker = null;
        let livePolyline = null;

        function placeDriverMarker(lat, lng, nom) {
          if (chauffeurMarker) {
            chauffeurMarker.setPosition({
              lat,
              lng
            });
          } else {
            chauffeurMarker = new google.maps.Marker({
              position: {
                lat,
                lng
              },
              map,
              icon: {
                path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                scale: 7,
                fillColor: '#f5cb17',
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: '#0a0a0a',
                rotation: 0,
              },
              title: nom || 'Chauffeur',
              zIndex: 100,
            });
          }
        }

        function placeDestMarker(lat, lng, adresse) {
          destMarker?.setMap(null);
          destMarker = new google.maps.Marker({
            position: {
              lat,
              lng
            },
            map,
            icon: {
              path: google.maps.SymbolPath.CIRCLE,
              scale: 9,
              fillColor: '#dc2626',
              fillOpacity: 1,
              strokeWeight: 3,
              strokeColor: '#fff',
            },
            title: adresse || 'Destination',
          });
        }

        // ── Itinéraire live (origin → destination) ────────
        function drawLiveRoute(origin, destination) {
          if (!directionsService) return;
          directionsService.route({
            origin,
            destination,
            travelMode: google.maps.TravelMode.DRIVING,
          }, (resp, status) => {
            if (status === 'OK') {
              if (!livePolyline) {
                livePolyline = new google.maps.DirectionsRenderer({
                  map,
                  suppressMarkers: true,
                  polylineOptions: {
                    strokeColor: '#f5cb17',
                    strokeWeight: 5,
                    strokeOpacity: .85,
                  },
                });
              }
              livePolyline.setDirections(resp);
            }
          });
        }

        // ── Adapter la vue carte aux points ──────────────
        function fitMapBounds(points) {
          if (!map || !points.length) return;
          const bounds = new google.maps.LatLngBounds();
          points.forEach(p => bounds.extend(new google.maps.LatLng(p.lat, p.lng)));
          if (S.userPos) bounds.extend(new google.maps.LatLng(S.userPos.lat, S.userPos.lng));
          map.fitBounds(bounds, {
            top: 80,
            bottom: 220,
            left: 20,
            right: 20
          });
        }

        // ── Nettoyer les marqueurs live ───────────────────
        function clearLiveMarkers() {
          chauffeurMarker?.setMap(null);
          chauffeurMarker = null;
          livePolyline?.setMap(null);
          livePolyline = null;
          destMarker?.setMap(null);
          destMarker = null;
          driverMarkers.forEach(m => m.setMap(null));
          driverMarkers = [];
        }

        // ── Remplir la vue "en-route" ─────────────────────
        function populateTrackingUI(course) {
          const ch = course.chauffeur;
          if (!ch) return;
          document.getElementById('track-prix').textContent = Number(course.prix).toLocaleString('fr-FR') + ' FCFA';
          document.getElementById('track-avatar').textContent = ch.nom.charAt(0).toUpperCase();
          document.getElementById('track-nom').textContent = ch.nom;
          document.getElementById('track-note').textContent = '⭐ ' + ch.note;
          document.getElementById('track-vehicule').textContent = ch.vehicule + ' · ' + (ch.couleur || '');
          document.getElementById('track-call').href = 'tel:' + ch.tel;
          document.getElementById('track-depart').textContent = course.depart_adresse || 'Votre position';
          document.getElementById('track-arrivee').textContent = course.arrivee_adresse || 'Destination';
          // ETA approximatif : distance chauffeur→client / 20km/h
          const distKm = haversineM(ch.lat, ch.lng, course.depart_lat, course.depart_lng) / 1000;
          const etaMin = Math.max(1, Math.round(distKm / 20 * 60));
          document.getElementById('track-eta').textContent = '~' + etaMin + ' min';
        }

        // ── Remplir la vue "in-ride" ──────────────────────
        function populateInRideUI(course) {
          const ch = course.chauffeur;
          if (!ch) return;
          document.getElementById('inride-prix').textContent = Number(course.prix).toLocaleString('fr-FR') + ' FCFA';
          document.getElementById('inride-avatar').textContent = ch.nom.charAt(0).toUpperCase();
          document.getElementById('inride-nom').textContent = ch.nom;
          document.getElementById('inride-vehicule').textContent = ch.vehicule + ' · ' + (ch.immatriculation || '');
          document.getElementById('inride-call').href = 'tel:' + ch.tel;
          document.getElementById('inride-depart').textContent = course.depart_adresse || 'Départ';
          document.getElementById('inride-arrivee').textContent = course.arrivee_adresse || 'Destination';
          updateInRideEta(ch, course);
        }

        function updateInRideEta(ch, course) {
          const distKm = haversineM(ch.lat, ch.lng, course.arrivee_lat, course.arrivee_lng) / 1000;
          const etaMin = Math.max(1, Math.round(distKm / 20 * 60));
          document.getElementById('inride-eta').textContent = '~' + etaMin + ' min';
        }

        // Remplace l'ancienne pollStatus() :
        async function pollStatus(courseId) {
          try {
            const res = await fetch(`${auth}booking.php?action=status&course_id=${courseId}`);
            const data = await res.json();
            if (data.code !== 0) return;

            const {
              statut,
              chauffeur
            } = data.course;

            // Mettre à jour marqueurs chauffeurs proches (seulement en attente)
            if (statut === 'en_attente') {
              renderDriversOnMap(data.chauffeurs_proches || []);
            }

            // Transitions d'état
            if (statut === 'acceptee' && chauffeur) {
              // Chauffeur vient d'accepter → passer en vue en-route
              showSearchingView('en-route');
              document.getElementById('sh-title').textContent = 'Chauffeur en route';
              populateTrackingUI(data.course);
              // Mettre à jour position chauffeur en temps réel
              placeDriverMarker(chauffeur.lat, chauffeur.lng, chauffeur.nom);
              drawLiveRoute({
                lat: chauffeur.lat,
                lng: chauffeur.lng
              }, {
                lat: data.course.depart_lat,
                lng: data.course.depart_lng
              });
              // Centrer la carte entre chauffeur et client
              fitMapBounds([{
                  lat: chauffeur.lat,
                  lng: chauffeur.lng
                },
                {
                  lat: data.course.depart_lat,
                  lng: data.course.depart_lng
                },
              ]);
            }

            if (statut === 'en_cours' && chauffeur) {
              // Client à bord → vue in-ride
              showSearchingView('in-ride');
              document.getElementById('sh-title').textContent = 'En route vers la destination';
              populateInRideUI(data.course);
              placeDriverMarker(chauffeur.lat, chauffeur.lng, chauffeur.nom);
              drawLiveRoute({
                lat: chauffeur.lat,
                lng: chauffeur.lng
              }, {
                lat: data.course.arrivee_lat,
                lng: data.course.arrivee_lng
              });
              // Afficher aussi le marqueur destination
              placeDestMarker(data.course.arrivee_lat, data.course.arrivee_lng, data.course.arrivee_adresse);
              fitMapBounds([{
                  lat: chauffeur.lat,
                  lng: chauffeur.lng
                },
                {
                  lat: data.course.arrivee_lat,
                  lng: data.course.arrivee_lng
                },
              ]);
              updateInRideEta(chauffeur, data.course);
            }

            if (statut === 'terminee') {
              clearInterval(S.pollInterval);
              clearLiveMarkers();
              closeSheet();
              S.currentCourse = null;
              showToast('🎉 Course terminée ! Merci d\'avoir utilisé MonTaxi', 'ok');
            }

            if (statut === 'annulee') {
              clearInterval(S.pollInterval);
              clearLiveMarkers();
              closeSheet();
              S.currentCourse = null;
              showToast('Course annulée', 'err');
            }

          } catch (e) {}
        }

        function renderDriversOnMap(drivers) {
          // Supprimer anciens marqueurs
          driverMarkers.forEach(m => m.setMap(null));
          driverMarkers = [];

          drivers.forEach(d => {
            const m = new google.maps.Marker({
              position: {
                lat: d.lat,
                lng: d.lng
              },
              map,
              icon: {
                path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                scale: 5,
                fillColor: '#f5cb17',
                fillOpacity: 1,
                strokeWeight: 1.5,
                strokeColor: '#0a0a0a',
                rotation: 0,
              },
              title: d.nom,
              zIndex: 50,
            });
            driverMarkers.push(m);
          });
        }

        function showDriverFound(chauffeur) {
          const found = document.getElementById('driver-found');
          found.style.display = 'block';
          document.getElementById('df-avatar').textContent = chauffeur.nom.charAt(0).toUpperCase();
          document.getElementById('df-name').textContent = chauffeur.nom;
          document.getElementById('df-note').textContent = '⭐ ' + chauffeur.note;
          document.getElementById('df-vehicule').textContent = chauffeur.vehicule + ' · ' + chauffeur.couleur;
          document.getElementById('df-eta').textContent = '~' + S.currentCourse.duree + ' min';
          document.getElementById('searching-title').textContent = 'Chauffeur trouvé !';
          document.getElementById('searching-sub').textContent = 'Votre chauffeur est en route vers vous.';
          document.getElementById('btn-cancel-ride').style.display = 'none';
        }

        async function cancelCourse() {
          if (!S.currentCourse) {
            closeSheet();
            return;
          }
          try {
            const res = await fetch(`${auth}booking.php?action=cancel`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                course_id: S.currentCourse.id
              }),
            });
            const data = await res.json();
            clearInterval(S.pollInterval);
            driverMarkers.forEach(m => m.setMap(null));
            driverMarkers = [];
            destMarker?.setMap(null);
            S.currentCourse = null;
            closeSheet();
            showToast(data.code === 0 ? 'Course annulée' : data.message, data.code === 0 ? 'success' : 'error');
          } catch (e) {
            showToast('Erreur réseau', 'error');
          }
        }

        // ══════════════════════════════════════════════════
        // HISTORIQUE
        // ══════════════════════════════════════════════════
        async function loadHistory() {
          document.getElementById('hist-list').innerHTML = '<div style="text-align:center;padding:40px;color:var(--doux)">Chargement…</div>';
          try {
            const res = await fetch(`${auth}booking.php?action=history`);
            const data = await res.json();
            if (data.code !== 0 || !data.courses.length) {
              document.getElementById('hist-list').innerHTML = '<div style="text-align:center;padding:40px;color:var(--doux)">Aucune course pour le moment.</div>';
              return;
            }
            const statutLabel = {
              terminee: '✅ Terminée',
              annulee: '❌ Annulée',
              en_attente: '⏳ En attente',
              acceptee: '🚖 En route',
              en_cours: '🚦 En cours'
            };
            document.getElementById('hist-list').innerHTML = data.courses.map(c => {
              const prixAffiche = Number(c.prix_final || c.prix_estime).toLocaleString('fr-FR');
              const cancelBtn = c.statut === 'en_attente' ?
                `<button class="btn-cancel-hist" onclick="cancelFromHistory(${c.id})">
         ❌ Annuler cette course
       </button>` :
                '';

              return `
    <div class="hist-card" id="hcard-${c.id}">
      <div class="hc-header">
        <span class="hc-statut ${c.statut}">${statutLabel[c.statut] || c.statut}</span>
        <span class="hc-date">${formatDate(c.cree_le)}</span>
      </div>
      <div class="hc-route">
        <div class="hc-pt"><span class="hc-pt-ico">🟢</span>${c.depart_adresse || 'Départ'}</div>
        <div class="hc-dash"></div>
        <div class="hc-pt"><span class="hc-pt-ico">🔴</span>${c.arrivee_adresse || 'Destination'}</div>
      </div>
      <div class="hc-footer">
        <span class="hc-plan">${c.nom_plan || 'Classique'} · ${c.type_vehicule}</span>
        <span class="hc-prix">${prixAffiche} FCFA</span>
      </div>
      ${cancelBtn}
    </div>`;
            }).join('');
          } catch (e) {
            document.getElementById('hist-list').innerHTML = '<div style="text-align:center;padding:40px;color:var(--doux)">Erreur de chargement.</div>';
          }
        }

        async function cancelFromHistory(courseId) {
          if (!confirm('Annuler cette course ?')) return;
          try {
            const res = await fetch(`${auth}booking.php?action=cancel`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                course_id: courseId
              }),
            });
            const data = await res.json();
            if (data.code === 0) {
              // Mettre à jour la carte visuellement sans recharger
              const card = document.getElementById('hcard-' + courseId);
              if (card) {
                card.querySelector('.hc-statut').className = 'hc-statut annulee';
                card.querySelector('.hc-statut').textContent = '❌ Annulée';
                card.querySelector('.btn-cancel-hist')?.remove();
              }
              showToast('Course annulée', 'ok');
            } else {
              showToast(data.message, 'err');
            }
          } catch (e) {
            showToast('Erreur réseau', 'err');
          }
        }

        // ══════════════════════════════════════════════════
        // UTILS
        // ══════════════════════════════════════════════════
        function haversineM(lat1, lng1, lat2, lng2) {
          const R = 6371000,
            dL = (lat2 - lat1) * Math.PI / 180,
            dG = (lng2 - lng1) * Math.PI / 180;
          const a = Math.sin(dL / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dG / 2) ** 2;
          return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function escQ(s) {
          return (s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        function escH(s) {
          const d = document.createElement('div');
          d.textContent = s;
          return d.innerHTML;
        }

        function highlight(text, query) {
          if (!query) return escH(text);
          const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
          return escH(text).replace(re, '<strong>$1</strong>');
        }

        function formatDate(d) {
          if (!d) return '';
          const dt = new Date(d);
          return dt.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
          }) + ' ' + dt.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
          });
        }

        let toastTimer;

        function showToast(msg, type = '') {
          const t = document.getElementById('toast');
          t.textContent = msg;
          t.className = 'toast' + (type ? ' ' + type : '');
          void t.offsetWidth;
          t.classList.add('show');
          clearTimeout(toastTimer);
          toastTimer = setTimeout(() => t.classList.remove('show'), 3200);
        }

        // Init par défaut
        document.addEventListener('DOMContentLoaded', () => {
          // Map initialisée via callback Google
        });
      </script>
      <!-- ══ MODAL PROFIL ══ -->
      <div class="overlay" id="modal-profil" onclick="maybeCloseModal('modal-profil',event)">
        <div class="sheet">
          <div class="sheet-handle"></div>
          <div class="sheet-head">
            <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem">Mon profil</div>
            <button onclick="closeModal('modal-profil')" style="background:var(--gris-bg);border:1px solid var(--gris-clair);border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem">✕</button>
          </div>
          <div class="sheet-scroll">

            <!-- Photo -->
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:22px">
              <div style="width:72px;height:72px;border-radius:50%;background:var(--noir);color:var(--or);
          display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;
          font-weight:800;font-size:1.6rem;overflow:hidden;flex-shrink:0;border:3px solid var(--gris-clair)"
                id="modal-avatar-preview">
              </div>
              <div style="display:flex;flex-direction:column;gap:8px">
                <label style="padding:8px 16px;border-radius:50px;font-size:.8rem;font-weight:500;
            background:var(--blanc);border:1.5px solid var(--gris-clair);cursor:pointer;
            display:inline-flex;align-items:center;gap:6px">
                  📷 Changer la photo
                  <input type="file" id="photo-input" accept="image/jpeg,image/png,image/webp"
                    style="display:none" onchange="previewPhoto(this)">
                </label>
                <button onclick="deletePhoto()" style="padding:8px 16px;border-radius:50px;font-size:.8rem;
            background:var(--blanc);border:1.5px solid #fecaca;color:var(--rouge);cursor:pointer">
                  🗑 Supprimer la photo
                </button>
              </div>
            </div>

            <div id="profil-alert" class="modal-alert"></div>
            <div id="profil-success" class="modal-success"></div>

            <!-- Nom -->
            <div class="frow">
              <label class="flabel">Nom complet</label>
              <input class="finput" id="input-noms" type="text"
                value="<?= htmlspecialchars($clientNom) ?>" placeholder="Votre nom complet" />
            </div>

            <!-- Email désactivé -->
            <div class="frow">
              <label class="flabel">Email</label>
              <input class="finput" type="email"
                value="<?= htmlspecialchars($clientEmail) ?>" disabled />
              <div class="fhint">L'email ne peut pas être modifié ici.</div>
            </div>

            <button class="btn-save" id="btn-save-profil" onclick="saveProfil()">
              💾 Enregistrer
            </button>
          </div>
        </div>
      </div>

      <!-- ══ MODAL SÉCURITÉ ══ -->
      <div class="overlay" id="modal-securite" onclick="maybeCloseModal('modal-securite',event)">
        <div class="sheet">
          <div class="sheet-handle"></div>
          <div class="sheet-head">
            <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem">Sécurité</div>
            <button onclick="closeModal('modal-securite')" style="background:var(--gris-bg);border:1px solid var(--gris-clair);border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem">✕</button>
          </div>
          <div class="sheet-scroll">

            <div id="secu-alert" class="modal-alert"></div>
            <div id="secu-success" class="modal-success"></div>

            <div class="frow">
              <label class="flabel">Mot de passe actuel</label>
              <input class="finput" id="input-old-mdp" type="password" placeholder="••••••••" />
            </div>
            <div class="frow">
              <label class="flabel">Nouveau mot de passe</label>
              <input class="finput" id="input-new-mdp" type="password"
                placeholder="Minimum 8 caractères" oninput="checkStrength(this.value)" />
              <!-- Barre de force -->
              <div style="display:flex;gap:4px;margin-top:8px" id="strength-bars">
                <div style="flex:1;height:3px;border-radius:2px;background:var(--gris-clair)" id="sb1"></div>
                <div style="flex:1;height:3px;border-radius:2px;background:var(--gris-clair)" id="sb2"></div>
                <div style="flex:1;height:3px;border-radius:2px;background:var(--gris-clair)" id="sb3"></div>
                <div style="flex:1;height:3px;border-radius:2px;background:var(--gris-clair)" id="sb4"></div>
              </div>
              <div class="fhint" id="strength-label"></div>
            </div>
            <div class="frow">
              <label class="flabel">Confirmer le nouveau mot de passe</label>
              <input class="finput" id="input-confirm-mdp" type="password" placeholder="••••••••" />
            </div>

            <button class="btn-save" id="btn-save-secu" onclick="savePassword()">
              🔒 Changer le mot de passe
            </button>
          </div>
        </div>
      </div>
      <style>
        /* ── MODALS PARAMÈTRES ── */
        .modal-alert {
          background: #fef2f2;
          border: 1px solid #fecaca;
          border-radius: 12px;
          padding: 10px 14px;
          font-size: .8rem;
          color: var(--rouge);
          margin-bottom: 14px;
          display: none
        }

        .modal-alert.show {
          display: block
        }

        .modal-success {
          background: #f0fdf4;
          border: 1px solid #bbf7d0;
          border-radius: 12px;
          padding: 10px 14px;
          font-size: .8rem;
          color: #15803d;
          margin-bottom: 14px;
          display: none
        }

        .modal-success.show {
          display: block
        }

        .frow {
          margin-bottom: 16px
        }

        .flabel {
          font-size: .72rem;
          font-weight: 600;
          color: var(--texte-doux);
          text-transform: uppercase;
          letter-spacing: .05em;
          margin-bottom: 6px;
          display: block
        }

        .finput {
          width: 100%;
          padding: 13px 14px;
          border-radius: 12px;
          border: 2px solid #0002;
          font-family: Po02;
          font-size: .95rem;
          background: var(--gris-bg);
          outline: none;
          transition: border-color .2s
        }

        .finput:focus {
          border-color: var(--or)
        }

        .finput:disabled {
          opacity: .5;
          cursor: not-allowed
        }

        .fhint {
          font-size: .72rem;
          color: var(--texte-doux);
          margin-top: 4px
        }

        .btn-save {
          width: 100%;
          padding: 14px;
          border: none;
          border-radius: 14px;
          background: var(--noir);
          color: var(--blanc);
          font-family: Po02;
          font-size: .95rem;
          font-weight: 600;
          cursor: pointer;
          transition: all .22s;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px
        }

        .btn-save:hover {
          background: #222;
          transform: translateY(-1px)
        }

        .btn-save:disabled {
          background: #ccc;
          cursor: not-allowed;
          transform: none
        }
      </style>

      <script>
        // ══════════════════════════════════════════════════
        // PARAMÈTRES — PROFIL & SÉCURITÉ
        // ══════════════════════════════════════════════════

        // État local photo (avant save)
        let pendingPhotoFile = null;

        function openModalProfil() {
          pendingPhotoFile = null;
          // Remplir la preview avatar
          refreshModalAvatar();
          document.getElementById('modal-profil').classList.add('open');
        }

        function openModalSecurite() {
          // Reset champs
          ['input-old-mdp', 'input-new-mdp', 'input-confirm-mdp'].forEach(id => {
            document.getElementById(id).value = '';
          });
          resetStrength();
          hideMessages('secu');
          document.getElementById('modal-securite').classList.add('open');
        }

        function closeModal(id) {
          document.getElementById(id).classList.remove('open');
        }

        function maybeCloseModal(id, e) {
          if (e.target === document.getElementById(id)) closeModal(id);
        }

        // ── Preview photo locale ──────────────────────────
        function previewPhoto(input) {
          if (!input.files[0]) return;
          pendingPhotoFile = input.files[0];
          const url = URL.createObjectURL(pendingPhotoFile);
          setAvatarPreview(document.getElementById('modal-avatar-preview'), url, null);
        }

        function refreshModalAvatar() {
          const el = document.getElementById('modal-avatar-preview');
          const pic = '<?= addslashes($clientPic) ?>';
          const ini = '<?= $initiale ?>';
          setAvatarPreview(el, pic || null, ini);
        }

        function setAvatarPreview(container, url, initiale) {
          if (url) {
            container.innerHTML = `<img src="${url}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
          } else {
            container.style.background = 'var(--noir)';
            container.innerHTML = `<span style="color:var(--or);font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem">${initiale || '?'}</span>`;
          }
        }

        // ── Sauvegarder le profil ─────────────────────────
        async function saveProfil() {
          const btn = document.getElementById('btn-save-profil');
          const noms = document.getElementById('input-noms').value.trim();
          hideMessages('profil');

          if (noms.length < 2) {
            showModalMsg('profil', 'error', 'Le nom est trop court.');
            return;
          }

          btn.disabled = true;
          btn.innerHTML = '<span class="spin">⟳</span> Enregistrement…';

          try {
            // 1. Upload photo si modifiée
            if (pendingPhotoFile) {
              const fd = new FormData();
              fd.append('photo', pendingPhotoFile);
              const r = await fetch(`${auth}profil.php?action=update_photo`, {
                method: 'POST',
                body: fd
              });
              const d = await r.json();
              if (d.code !== 0) {
                showModalMsg('profil', 'error', d.message);
                btn.disabled = false;
                btn.innerHTML = '💾 Enregistrer';
                return;
              }
              // Mettre à jour les avatars sur la page
              updateAllAvatars(d.url, null);
            }

            // 2. Mettre à jour le nom
            const r2 = await fetch(`${auth}profil.php?action=update_infos`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                noms
              }),
            });
            const d2 = await r2.json();
            if (d2.code !== 0) {
              showModalMsg('profil', 'error', d2.message);
              btn.disabled = false;
              btn.innerHTML = '💾 Enregistrer';
              return;
            }

            // Mettre à jour les noms affichés
            document.getElementById('pp-nom')?.textContent && (document.getElementById('pp-nom').textContent = noms);
            showModalMsg('profil', 'success', 'Profil mis à jour !');
            pendingPhotoFile = null;

          } catch (e) {
            showModalMsg('profil', 'error', 'Erreur réseau.');
          }

          btn.disabled = false;
          btn.innerHTML = '💾 Enregistrer';
        }

        // ── Supprimer photo ───────────────────────────────
        async function deletePhoto() {
          if (!confirm('Supprimer votre photo de profil ?')) return;
          const r = await fetch(`${auth}profil.php?action=delete_photo`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: '{}',
          });
          const d = await r.json();
          if (d.code === 0) {
            updateAllAvatars(null, '<?= $initiale ?>');
            showModalMsg('profil', 'success', 'Photo supprimée.');
            pendingPhotoFile = null;
          }
        }

        function updateAllAvatars(url, initiale) {
          const ini = initiale || document.getElementById('pp-nom')?.textContent?.charAt(0)?.toUpperCase() || '?';
          // Header avatar
          const hav = document.getElementById('header-avatar') || document.querySelector('.avatar-btn');
          if (hav) hav.innerHTML = url ? `<img src="${url}" style="width:100%;height:100%;object-fit:cover">` : ini;
          // Profile card
          const pav = document.querySelector('.pc-avatar');
          if (pav) pav.innerHTML = url ? `<img src="${url}" style="width:100%;height:100%;object-fit:cover">` : ini;
        }

        // ── Changer mot de passe ──────────────────────────
        async function savePassword() {
          const btn = document.getElementById('btn-save-secu');
          const ancien = document.getElementById('input-old-mdp').value;
          const nouveau = document.getElementById('input-new-mdp').value;
          const confirm = document.getElementById('input-confirm-mdp').value;
          hideMessages('secu');

          if (!ancien || !nouveau || !confirm) {
            showModalMsg('secu', 'error', 'Tous les champs sont requis.');
            return;
          }
          if (nouveau.length < 8) {
            showModalMsg('secu', 'error', 'Minimum 8 caractères.');
            return;
          }
          if (nouveau !== confirm) {
            showModalMsg('secu', 'error', 'Les mots de passe ne correspondent pas.');
            return;
          }

          btn.disabled = true;
          btn.innerHTML = '<span class="spin">⟳</span> Vérification…';

          try {
            const r = await fetch(`${auth}profil.php?action=change_password`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                ancien_mdp: ancien,
                nouveau_mdp: nouveau,
                confirmer_mdp: confirm
              }),
            });
            const d = await r.json();
            if (d.code !== 0) {
              showModalMsg('secu', 'error', d.message);
            } else {
              showModalMsg('secu', 'success', d.message);
              ['input-old-mdp', 'input-new-mdp', 'input-confirm-mdp'].forEach(id => {
                document.getElementById(id).value = '';
              });
              resetStrength();
            }
          } catch (e) {
            showModalMsg('secu', 'error', 'Erreur réseau.');
          }

          btn.disabled = false;
          btn.innerHTML = '🔒 Changer le mot de passe';
        }

        // ── Indicateur force mot de passe ────────────────
        function checkStrength(val) {
          let score = 0;
          if (val.length >= 8) score++;
          if (/[A-Z]/.test(val)) score++;
          if (/[0-9]/.test(val)) score++;
          if (/[^A-Za-z0-9]/.test(val)) score++;
          const colors = ['', '#ef4444', '#f97316', '#3b82f6', '#16a34a'];
          const labels = ['', 'Faible', 'Moyen', 'Fort', 'Très fort'];
          for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById('sb' + i);
            bar.style.background = i <= score ? colors[score] : 'var(--gris-clair)';
          }
          document.getElementById('strength-label').textContent = val.length ? labels[score] : '';
        }

        function resetStrength() {
          for (let i = 1; i <= 4; i++)
            document.getElementById('sb' + i).style.background = 'var(--gris-clair)';
          document.getElementById('strength-label').textContent = '';
        }

        // ── Helpers messages ──────────────────────────────
        function showModalMsg(prefix, type, msg) {
          const id = type === 'error' ? prefix + '-alert' : prefix + '-success';
          const el = document.getElementById(id);
          if (!el) return;
          el.textContent = msg;
          el.classList.add('show');
        }

        function hideMessages(prefix) {
          [prefix + '-alert', prefix + '-success'].forEach(id => {
            document.getElementById(id)?.classList.remove('show');
          });
        }
      </script>
    <?php else: ?>
      <div class="auth-overlay" id="authOverlay">
        <div class="auth-container">
          <div class="auth-header">
            <img src="<?= $img ?>logo2.png" alt="Mon Taxi CM" class="auth-logo">
            <h2 id="authTitle">Connexion</h2>
          </div>

          <div class="auth-slider" id="authSlider">
            <div class="auth-page" id="loginPage">
              <form novalidate id="formLogin">
                <label for="usernameConnect">Votre email</label>
                <div class="input-group">
                  <span class="mio">email</span>
                  <input type="email" name="email" placeholder="Email" id="usernameConnect">
                </div>

                <label for="pass">Mot de passe</label>
                <div class="input-group">
                  <span class="mio">lock</span>
                  <input type="password" name="password" placeholder="Mot de passe" id="pass">
                </div>

                <button type="submit" class="btn-primary">Se connecter</button>

                <div class="auth-options">
                  <span onclick="toggleForgot(true)" class="link-text">Mot de passe oublié ?</span>
                </div>
              </form>

              <div class="separator"><i></i><span>OU</span><i></i></div>

              <button class="btn-google" onclick="openSuccess('Fonctionnalité en cours de déploiement (API Google Cloud)')">
                <img src="<?= $img ?>google.png" alt="Google">
                Continuer avec Google
              </button>

              <p class="switch-text">Pas de compte ? <strong onclick="slideAuth('signup')">S'inscrire</strong></p>
            </div>

            <div class="auth-page" id="signupPage">
              <form novalidate id="formSignup">
                <label for="names">Votre nom complet</label>
                <div class="input-group">
                  <i class="mio">person</i>
                  <input type="text" id="names" name="nom" placeholder="Noms complets">
                </div>

                <label for="username">Votre adresse email</label>
                <div class="input-group">
                  <i class="mio">email</i>
                  <input type="email" name="email" placeholder="Email" required>
                </div>

                <label for="password">Créer un mot de passe (min: 6 caractères)</label>
                <div class="input-group">
                  <i class="mio">lock</i>
                  <input type="password" name="password" placeholder="Mot de passe" id="password">
                </div>

                <button type="submit" class="btn-primary">CRÉER MON COMPTE</button>
              </form>
              <div class="separator"><i></i><span>OU</span><i></i></div>

              <button class="btn-google" onclick="openSuccess('Fonctionnalité en cours de déploiement (API Google Cloud)')">
                <img src="<?= $img ?>google.png" alt="Google">
                S'inscrire avec Google
              </button>

              <p class="switch-text">Déjà inscrit ? <strong onclick="slideAuth('login')">Se connecter</strong></p>
            </div>
          </div>

          <div id="forgotSection" class="forgot-panel">
            <div class="forgot-content">
              <span class="close-forgot mio" onclick="toggleForgot(false)">close</span>
              <h3>Récupération</h3>
              <p class="info">
                Vous avez oublié votre mot de passe ? <br>Si oui, veuillez entrer votre adresse email et nous vous aiderons à réinitialiser votre compte.
              </p>
              <div id="forgotStep1">
                <label>Entrez votre email</label>
                <div class="input-group">
                  <i class="mio">contact_support</i>
                  <input type="text" id="forgotInput" placeholder="Email">
                </div>
                <button type="button" class="btn-secondary" onclick="verifyForgot()">Vérifier</button>
              </div>
              <div id="forgotStep2" style="margin-top: 20px; display: none">
                <label>Entrez le code à 6 chiffres reçu depuis votre mail <span class="pri emailAdress"></span></label>
                <div class="input-group">
                  <input type="text" maxlength="6" class="otp-input" placeholder="000000">
                </div>
                <button type="button" class="btn-primary">VALIDER LE CODE</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <?php include($inc . 'popupsBox.php'); ?>

  <script src="<?= $js ?>functions.js"></script>
  <script src="<?= $js ?>all.js"></script>
  <script src="<?= $js ?>biblio.js"></script>
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
</body>

</html>