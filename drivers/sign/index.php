<?php

include('../inc/main.php');

// Si déjà connecté → redirection vers /app
if (isset($_SESSION['id'])) {
  header('Location: /app');
  exit;
}

// Lire le mode depuis l'URL (?mode=login ou ?mode=register)
$mode = (isset($_GET['mode']) && $_GET['mode'] === 'login') ? 'login' : 'register';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MonTaxi — Connexion / Inscription</title>
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

    button,
    input,
    textarea,
    select {
      font-family: Po02 !important;
    }

    :root {
      --noir: #0d0d0d;
      --blanc: #ffffff;
      --gris-bg: #f5f4f0;
      --gris-clair: #e8e6e1;
      --or: #c9a84c;
      --texte: #1a1a1a;
      --texte-doux: #6b6b6b;
      --erreur: #ef4444;
      --succes: #22c55e;
      --radius: 16px;
    }

    body {
      font-family: Po02;
      background: var(--gris-bg);
      color: var(--texte);
      min-height: 100vh;
      display: flex;
      align-items: stretch;
    }

    /* ── SPLIT LAYOUT ── */
    .auth-left {
      flex: 0 0 42%;
      background: var(--noir);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      padding: 48px;
    }

    .auth-left::before {
      content: '';
      position: absolute;
      bottom: -100px;
      right: -100px;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(201, 168, 76, .2) 0%, transparent 65%);
      pointer-events: none;
    }

    .auth-logo {
      font-family: Po01;
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--blanc);
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      margin-bottom: auto;
    }

    .auth-logo img {
      width: 180px;
      filter: invert(25%)
    }

    .auth-logo-badge {
      background: var(--or);
      color: var(--noir);
      border-radius: 8px;
      padding: 3px 10px;
      font-size: .85rem;
    }

    .auth-left-content {
      position: relative;
      z-index: 1;
    }

    .auth-left-title {
      font-family: Po01;
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--blanc);
      line-height: 1.2;
      margin-bottom: 16px;
    }

    .auth-left-title span {
      color: var(--or);
    }

    .auth-left-sub {
      color: rgba(255, 255, 255, .55);
      font-size: .95rem;
      line-height: 1.7;
      margin-bottom: 36px;
    }

    .auth-stats {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .auth-stat {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .auth-stat-icon {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, .08);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .auth-stat-text strong {
      display: block;
      color: var(--blanc);
      font-size: .9rem;
    }

    .auth-stat-text span {
      color: rgba(255, 255, 255, .45);
      font-size: .8rem;
    }

    /* ── RIGHT / FORM ── */
    .auth-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px 40px;
      overflow-y: auto;
    }

    .auth-card {
      width: 100%;
      max-width: 440px;
    }

    .auth-tabs {
      display: flex;
      gap: 4px;
      background: var(--gris-clair);
      border-radius: 12px;
      padding: 4px;
      margin-bottom: 36px;
    }

    .auth-tab {
      flex: 1;
      text-align: center;
      padding: 10px;
      border-radius: 9px;
      font-size: .9rem;
      font-weight: 500;
      cursor: pointer;
      transition: background .2s, color .2s, box-shadow .2s;
      color: var(--texte-doux);
      background: transparent;
      border: none;
      font-family: 'DM Sans', sans-serif;
    }

    .auth-tab.active {
      background: var(--blanc);
      color: var(--texte);
      box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    }

    /* ── FORM STEPS ── */
    .form-panel {
      display: none;
    }

    .form-panel.active {
      display: block;
    }

    .form-title {
      font-family: Po01;
      font-size: 1.6rem;
      font-weight: 800;
      margin-bottom: 6px;
    }

    .form-subtitle {
      font-size: .9rem;
      color: var(--texte-doux);
      margin-bottom: 28px;
    }

    /* Progress indicator (inscription) */
    .step-indicator {
      display: flex;
      align-items: center;
      gap: 0;
      margin-bottom: 28px;
    }

    .step-dot {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .8rem;
      font-weight: 700;
      background: var(--gris-clair);
      color: var(--texte-doux);
      transition: all .3s;
      flex-shrink: 0;
      border: 2px solid transparent;
    }

    .step-dot.active {
      background: var(--noir);
      color: var(--blanc);
    }

    .step-dot.done {
      background: var(--succes);
      color: var(--blanc);
    }

    .step-line {
      flex: 1;
      height: 2px;
      background: var(--gris-clair);
      transition: background .3s;
    }

    .step-line.done {
      background: var(--succes);
    }

    .step-label {
      display: flex;
      justify-content: space-between;
      margin-top: 6px;
      margin-bottom: 24px;
    }

    .step-label span {
      font-size: .72rem;
      color: var(--texte-doux);
    }

    .step-label span.active {
      color: var(--texte);
      font-weight: 600;
    }

    /* Fields */
    .field {
      margin-bottom: 18px;
    }

    .field label {
      display: block;
      font-size: .82rem;
      font-weight: 500;
      margin-bottom: 7px;
      color: var(--texte);
    }

    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .input-wrap {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1rem;
      color: var(--texte-doux);
      pointer-events: none;
    }

    .input-wrap input {
      width: 100%;
      padding: 13px 14px 13px 42px;
      border: 1.5px solid var(--gris-clair);
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem;
      background: var(--blanc);
      color: var(--texte);
      transition: border-color .2s, box-shadow .2s;
      outline: none;
    }

    .input-wrap input:focus {
      border-color: var(--texte);
      box-shadow: 0 0 0 3px rgba(13, 13, 13, .07);
    }

    .input-wrap input.error {
      border-color: var(--erreur);
    }

    .input-wrap input.valid {
      border-color: var(--succes);
    }

    .input-wrap.no-icon input {
      padding-left: 14px;
    }

    .toggle-pwd {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 1rem;
      color: var(--texte-doux);
      background: none;
      border: none;
      transition: color .2s;
    }

    .toggle-pwd:hover {
      color: var(--texte);
    }

    .field-error {
      font-size: .75rem;
      color: var(--erreur);
      margin-top: 5px;
      display: none;
    }

    .field-error.show {
      display: block;
    }

    /* Phone field */
    .phone-wrap {
      display: flex;
      gap: 0;
    }

    .phone-prefix {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 0 14px;
      background: var(--gris-bg);
      border: 1.5px solid var(--gris-clair);
      border-right: none;
      border-radius: 12px 0 0 12px;
      font-size: .9rem;
      color: var(--texte-doux);
      white-space: nowrap;
      flex-shrink: 0;
    }

    .phone-wrap input {
      border-radius: 0 12px 12px 0 !important;
      padding-left: 14px !important;
    }

    /* Password strength */
    .pwd-strength {
      margin-top: 8px;
      display: none;
    }

    .pwd-strength.show {
      display: block;
    }

    .pwd-bars {
      display: flex;
      gap: 4px;
      margin-bottom: 4px;
    }

    .pwd-bar {
      flex: 1;
      height: 4px;
      border-radius: 2px;
      background: var(--gris-clair);
      transition: background .3s;
    }

    .pwd-bar.weak {
      background: #ef4444;
    }

    .pwd-bar.medium {
      background: #f59e0b;
    }

    .pwd-bar.strong {
      background: #22c55e;
    }

    .pwd-strength-label {
      font-size: .72rem;
      color: var(--texte-doux);
    }

    /* CGU */
    .cgu-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 22px;
    }

    .cgu-row input[type=checkbox] {
      margin-top: 3px;
      cursor: pointer;
    }

    .cgu-row label {
      font-size: .82rem;
      color: var(--texte-doux);
      cursor: pointer;
      line-height: 1.5;
    }

    .cgu-row a {
      color: var(--texte);
      font-weight: 500;
    }

    /* Buttons */
    .btn-submit {
      width: 100%;
      padding: 14px;
      background: var(--noir);
      color: var(--blanc);
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .25s, transform .2s, box-shadow .2s;
    }

    .btn-submit:hover:not(:disabled) {
      background: #222;
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, .14);
    }

    .btn-submit:disabled {
      opacity: .55;
      cursor: not-allowed;
    }

    .btn-submit.loading::after {
      content: '';
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, .4);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .6s linear infinite;
      margin-left: 8px;
      vertical-align: middle;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .btn-back {
      background: transparent;
      border: 1.5px solid var(--gris-clair);
      color: var(--texte);
      border-radius: 12px;
      padding: 13px;
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem;
      cursor: pointer;
      transition: all .2s;
      flex: 1;
    }

    .btn-back:hover {
      border-color: var(--texte);
    }

    .btn-row {
      display: flex;
      gap: 12px;
    }

    .btn-row .btn-submit {
      flex: 2;
    }

    /* Alert */
    .alert {
      display: none;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: .85rem;
      font-weight: 500;
      margin-bottom: 18px;
      align-items: center;
      gap: 8px;
    }

    .alert.show {
      display: flex;
    }

    .alert-error {
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fecaca;
    }

    .alert-success {
      background: #f0fdf4;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 20px 0;
      color: var(--texte-doux);
      font-size: .8rem;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--gris-clair);
    }

    .auth-switch {
      text-align: center;
      margin-top: 22px;
      font-size: .85rem;
      color: var(--texte-doux);
    }

    .auth-switch a {
      color: var(--texte);
      font-weight: 600;
      text-decoration: none;
    }

    .auth-switch a:hover {
      text-decoration: underline;
    }

    /* Forgot */
    .forgot-link {
      text-align: right;
      margin-top: -10px;
      margin-bottom: 18px;
    }

    .forgot-link a {
      font-size: .8rem;
      color: var(--texte-doux);
      text-decoration: none;
    }

    .forgot-link a:hover {
      color: var(--texte);
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 800px) {
      body {
        flex-direction: column;
      }

      .auth-left {
        display: none;
      }

      .auth-right {
        padding: 32px 20px;
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

  <!-- LEFT PANEL -->
  <div class="auth-left">
    <a href="/" class="auth-logo">
      <img src="<?= $img ?>logo2">
    </a>

    <div class="auth-left-content">
      <h2 class="auth-left-title">Gagnez plus,<br><span>chaque jour.</span></h2>
      <p class="auth-left-sub">Rejoignez la communauté des chauffeurs MonTaxi et transformez votre véhicule en source de revenus stable.</p>

      <div class="auth-stats">
        <div class="auth-stat">
          <div class="auth-stat-icon">💰</div>
          <div class="auth-stat-text">
            <strong>Commission 13% seulement</strong>
            <span>La plus basse du marché camerounais</span>
          </div>
        </div>
        <div class="auth-stat">
          <div class="auth-stat-icon">🛡️</div>
          <div class="auth-stat-text">
            <strong>KYC validé sous 24h</strong>
            <span>Commencez à travailler rapidement</span>
          </div>
        </div>
        <div class="auth-stat">
          <div class="auth-stat-icon">🗺️</div>
          <div class="auth-stat-text">
            <strong>Courses à proximité</strong>
            <span>Uniquement dans votre rayon d'1 km</span>
          </div>
        </div>
        <div class="auth-stat">
          <div class="auth-stat-icon">🕐</div>
          <div class="auth-stat-text">
            <strong>Horaires 100% libres</strong>
            <span>Travaillez quand vous le souhaitez</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-right">
    <div class="auth-card">

      <!-- TABS -->
      <div class="auth-tabs">
        <button class="auth-tab" id="tab-login" onclick="switchTab('login')">Connexion</button>
        <button class="auth-tab active" id="tab-register" onclick="switchTab('register')">Inscription</button>
      </div>

      <!-- ======================== CONNEXION ======================== -->
      <div class="form-panel" id="panel-login">
        <h2 class="form-title">Bon retour 👋</h2>
        <p class="form-subtitle">Connectez-vous pour accéder à votre espace chauffeur.</p>

        <div class="alert alert-error" id="login-alert">⚠️ <span id="login-alert-msg"></span></div>

        <div class="field">
          <label>Téléphone ou Email</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" id="login-identifier" placeholder="Email ou +237 6XX XXX XXX" autocomplete="username" />
          </div>
          <div class="field-error" id="login-identifier-err">Ce champ est requis.</div>
        </div>

        <div class="field">
          <label>Mot de passe</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="login-pwd" placeholder="Votre mot de passe" autocomplete="current-password" />
            <button class="toggle-pwd" type="button" onclick="togglePwd('login-pwd', this)">👁️</button>
          </div>
          <div class="field-error" id="login-pwd-err">Mot de passe requis.</div>
        </div>

        <div class="forgot-link"><a href="#">Mot de passe oublié ?</a></div>

        <button class="btn-submit" id="login-btn" onclick="handleLogin()">Se connecter</button>

        <div class="auth-switch">
          Pas encore de compte ? <a href="#" onclick="switchTab('register')">S'inscrire gratuitement</a>
        </div>
      </div>

      <!-- ── Étape 2FA (cachée par défaut) ── -->
      <div id="step-2fa" style="display:none">
        <div style="text-align:center;margin-bottom:24px">
          <div style="font-size:2.5rem;margin-bottom:12px">🛡️</div>
          <div style="font-family:Po01;font-size:1.1rem;font-weight:700;margin-bottom:6px">
            Vérification en deux étapes
          </div>
          <div style="font-size:.85rem;color:var(--texte-doux);line-height:1.6">
            Ouvrez votre application d'authentification<br>
            et entrez le code à 6 chiffres.
          </div>
        </div>

        <div id="2fa-error" style="display:none;background:#fef2f2;border:1px solid #fecaca;
    border-radius:12px;padding:10px 14px;font-size:.82rem;color:#dc2626;margin-bottom:14px"></div>

        <input
          id="input-2fa"
          type="text"
          inputmode="numeric"
          maxlength="6"
          placeholder="000 000"
          autocomplete="one-time-code"
          oninput="if(this.value.replace(/\s/g,'').length===6) submit2FA()"
          style="width:100%;padding:18px;text-align:center;font-size:1.8rem;
      letter-spacing:.4em;border-radius:16px;border:2px solid var(--gris-clair);
      background:var(--gris-bg);outline:none;font-family:Po01;margin-bottom:16px;
      transition:border-color .2s"
          onfocus="this.style.borderColor='var(--noir)'"
          onblur="this.style.borderColor='var(--gris-clair)'" />

        <button id="btn-2fa" onclick="submit2FA()" style="width:100%;padding:14px;border:none;
    border-radius:14px;background:var(--noir);color:var(--blanc);font-family:Po02;
    font-size:.95rem;font-weight:600;cursor:pointer;margin-bottom:12px;transition:all .2s">
          Valider
        </button>

        <button onclick="back2Login()" style="width:100%;padding:12px;border:1.5px solid var(--gris-clair);
    border-radius:14px;background:transparent;font-family:Po02;font-size:.88rem;cursor:pointer;
    color:var(--texte-doux)">
          ← Retour
        </button>
      </div>

      <!-- ======================== INSCRIPTION ======================== -->
      <div class="form-panel active" id="panel-register">

        <!-- STEP INDICATOR -->
        <div class="step-indicator">
          <div class="step-dot active" id="sdot-1">1</div>
          <div class="step-line" id="sline-1"></div>
          <div class="step-dot" id="sdot-2">2</div>
        </div>
        <div class="step-label">
          <span class="active" id="slabel-1">Informations personnelles</span>
          <span id="slabel-2">Mot de passe</span>
        </div>

        <div class="alert alert-error" id="reg-alert">⚠️ <span id="reg-alert-msg"></span></div>
        <div class="alert alert-success" id="reg-success">✅ <span id="reg-success-msg"></span></div>

        <!-- ── ÉTAPE 1 : Infos perso ── -->
        <div id="reg-step-1">
          <h2 class="form-title">Créez votre compte</h2>
          <p class="form-subtitle">Étape 1 sur 2 — Renseignez vos informations personnelles.</p>

          <div class="field-row">
            <div class="field">
              <label>Prénom *</label>
              <div class="input-wrap">
                <span class="input-icon">✏️</span>
                <input type="text" id="reg-prenom" placeholder="Jean" autocomplete="given-name" />
              </div>
              <div class="field-error" id="reg-prenom-err">Prénom requis.</div>
            </div>
            <div class="field">
              <label>Nom *</label>
              <div class="input-wrap">
                <span class="input-icon">✏️</span>
                <input type="text" id="reg-nom" placeholder="Mbarga" autocomplete="family-name" />
              </div>
              <div class="field-error" id="reg-nom-err">Nom requis.</div>
            </div>
          </div>

          <div class="field">
            <label>Email *</label>
            <div class="input-wrap">
              <span class="input-icon">📧</span>
              <input type="email" id="reg-email" placeholder="jean.mbarga@email.com" autocomplete="email" />
            </div>
            <div class="field-error" id="reg-email-err">Adresse email invalide.</div>
          </div>

          <div class="field">
            <label>Téléphone *</label>
            <div class="phone-wrap">
              <div class="phone-prefix">🇨🇲 +237</div>
              <div class="input-wrap" style="flex:1">
                <input type="tel" id="reg-tel" placeholder="6 XX XXX XXX" autocomplete="tel" class="no-icon" />
              </div>
            </div>
            <div class="field-error" id="reg-tel-err">Numéro Cameroun invalide (ex: 6XXXXXXXX).</div>
          </div>

          <button class="btn-submit" onclick="goToStep2()">Continuer →</button>

          <div class="auth-switch">
            Déjà inscrit ? <a href="#" onclick="switchTab('login')">Se connecter</a>
          </div>
        </div>

        <!-- ── ÉTAPE 2 : Mot de passe ── -->
        <div id="reg-step-2" style="display:none">
          <h2 class="form-title">Sécurisez votre compte</h2>
          <p class="form-subtitle">Étape 2 sur 2 — Choisissez un mot de passe solide.</p>

          <div class="field">
            <label>Mot de passe *</label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="reg-pwd" placeholder="Minimum 8 caractères" autocomplete="new-password" oninput="checkPwdStrength(this.value)" />
              <button class="toggle-pwd" type="button" onclick="togglePwd('reg-pwd', this)">👁️</button>
            </div>
            <div class="field-error" id="reg-pwd-err">Mot de passe trop faible (min. 8 caractères).</div>
            <div class="pwd-strength" id="pwd-strength">
              <div class="pwd-bars">
                <div class="pwd-bar" id="pb1"></div>
                <div class="pwd-bar" id="pb2"></div>
                <div class="pwd-bar" id="pb3"></div>
                <div class="pwd-bar" id="pb4"></div>
              </div>
              <div class="pwd-strength-label" id="pwd-label">—</div>
            </div>
          </div>

          <div class="field">
            <label>Confirmer le mot de passe *</label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="reg-pwd2" placeholder="Répétez le mot de passe" autocomplete="new-password" />
              <button class="toggle-pwd" type="button" onclick="togglePwd('reg-pwd2', this)">👁️</button>
            </div>
            <div class="field-error" id="reg-pwd2-err">Les mots de passe ne correspondent pas.</div>
          </div>

          <div class="cgu-row">
            <input type="checkbox" id="cgu-check" />
            <label for="cgu-check">J'accepte les <a href="#">Conditions générales d'utilisation</a> et la <a href="#">Politique de confidentialité</a> de MonTaxi.</label>
          </div>
          <div class="field-error" id="reg-cgu-err" style="margin-top:-10px;margin-bottom:12px">Vous devez accepter les CGU.</div>

          <div class="btn-row">
            <button class="btn-back" onclick="goToStep1()">← Retour</button>
            <button class="btn-submit" id="reg-btn" onclick="handleRegister()">Créer mon compte</button>
          </div>
        </div>

      </div>
      <!-- /panel-register -->

    </div>
  </div>

  <script>
    function $(id) {
      return document.getElementById(id);
    }

    function show2FAStep() {
      // Cacher le formulaire de login, montrer l'étape 2FA
      document.getElementById('panel-login').style.display = 'none'; // adapte l'id à ton formulaire
      document.getElementById('step-2fa').style.display = '';
      setTimeout(() => document.getElementById('input-2fa').focus(), 200);
    }

    function back2Login() {
      document.getElementById('step-2fa').style.display = 'none';
      document.getElementById('panel-login').style.display = ''; // adapte l'id à ton formulaire
      document.getElementById('input-2fa').value = '';
      set2FAError('');
    }

    function showErr(id, msg) {
      const el = $(id);
      if (msg) el.textContent = msg;
      el.classList.add('show');
    }

    function hideErr(id) {
      $(id).classList.remove('show');
    }

    function setInputState(id, state) {
      const inp = $(id);
      inp.classList.remove('error', 'valid');
      if (state) inp.classList.add(state);
    }

    function showAlert(id, msg, type = 'error') {
      const el = $(id);
      el.querySelector('span').textContent = msg;
      el.classList.add('show');
      setTimeout(() => el.classList.remove('show'), 5000);
    }

    function togglePwd(inputId, btn) {
      const inp = $(inputId);
      if (inp.type === 'password') {
        inp.type = 'text';
        btn.textContent = '🙈';
      } else {
        inp.type = 'password';
        btn.textContent = '👁️';
      }
    }

    function setLoading(btnId, loading) {
      const btn = $(btnId);
      btn.disabled = loading;
      btn.classList.toggle('loading', loading);
      if (!loading) btn.classList.remove('loading');
    }

    // ────────────────────────────────────────────
    // TABS
    // ────────────────────────────────────────────
    function switchTab(tab) {
      $('tab-login').classList.toggle('active', tab === 'login');
      $('tab-register').classList.toggle('active', tab === 'register');
      $('panel-login').classList.toggle('active', tab === 'login');
      $('panel-register').classList.toggle('active', tab === 'register');
    }

    // Init from URL param

    // APRÈS — on utilise la variable PHP déjà calculée côté serveur
    (function() {
      switchTab('<?= $mode ?>');
    })();

    // ────────────────────────────────────────────
    // PASSWORD STRENGTH
    // ────────────────────────────────────────────
    function checkPwdStrength(val) {
      const el = $('pwd-strength');
      if (!val) {
        el.classList.remove('show');
        return;
      }
      el.classList.add('show');

      let score = 0;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      const bars = ['pb1', 'pb2', 'pb3', 'pb4'];
      const cls = score <= 1 ? 'weak' : score <= 2 ? 'medium' : 'strong';
      const labels = ['', 'Faible', 'Faible', 'Moyen', 'Fort'];

      bars.forEach((b, i) => {
        const bar = $(b);
        bar.className = 'pwd-bar';
        if (i < score) bar.classList.add(cls);
      });
      $('pwd-label').textContent = labels[score] || '';
    }

    // ────────────────────────────────────────────
    // STEP NAVIGATION
    // ────────────────────────────────────────────
    function goToStep2() {
      let valid = true;

      const prenom = $('reg-prenom').value.trim();
      const nom = $('reg-nom').value.trim();
      const email = $('reg-email').value.trim();
      const tel = $('reg-tel').value.replace(/\s/g, '');

      // Prénom
      if (!prenom) {
        showErr('reg-prenom-err', 'Prénom requis.');
        setInputState('reg-prenom', 'error');
        valid = false;
      } else {
        hideErr('reg-prenom-err');
        setInputState('reg-prenom', 'valid');
      }

      // Nom
      if (!nom) {
        showErr('reg-nom-err', 'Nom requis.');
        setInputState('reg-nom', 'error');
        valid = false;
      } else {
        hideErr('reg-nom-err');
        setInputState('reg-nom', 'valid');
      }

      // Email
      const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRx.test(email)) {
        showErr('reg-email-err', 'Adresse email invalide.');
        setInputState('reg-email', 'error');
        valid = false;
      } else {
        hideErr('reg-email-err');
        setInputState('reg-email', 'valid');
      }

      // Téléphone Cameroun : 6XXXXXXXX ou 2XXXXXXXX (9 chiffres)
      const telRx = /^[0-9]{9}$/;
      if (!telRx.test(tel)) {
        showErr('reg-tel-err', 'Format invalide. Ex : 677123456');
        setInputState('reg-tel', 'error');
        valid = false;
      } else {
        hideErr('reg-tel-err');
        setInputState('reg-tel', 'valid');
      }

      if (!valid) return;

      // Go step 2
      $('reg-step-1').style.display = 'none';
      $('reg-step-2').style.display = 'block';
      updateStepIndicator(2);
    }

    function goToStep1() {
      $('reg-step-2').style.display = 'none';
      $('reg-step-1').style.display = 'block';
      updateStepIndicator(1);
    }

    function updateStepIndicator(step) {
      const d1 = $('sdot-1'),
        d2 = $('sdot-2');
      const l1 = $('sline-1');
      const lb1 = $('slabel-1'),
        lb2 = $('slabel-2');

      if (step === 1) {
        d1.className = 'step-dot active';
        d1.textContent = '1';
        d2.className = 'step-dot';
        l1.className = 'step-line';
        lb1.className = 'active';
        lb2.className = '';
      } else {
        d1.className = 'step-dot done';
        d1.textContent = '✓';
        d2.className = 'step-dot active';
        l1.className = 'step-line done';
        lb1.className = '';
        lb2.className = 'active';
      }
    }

    // ────────────────────────────────────────────
    // INSCRIPTION
    // ────────────────────────────────────────────
    async function handleRegister() {
      let valid = true;

      const pwd = $('reg-pwd').value;
      const pwd2 = $('reg-pwd2').value;
      const cgu = $('cgu-check').checked;

      // Mot de passe
      if (pwd.length < 8) {
        showErr('reg-pwd-err', 'Minimum 8 caractères.');
        setInputState('reg-pwd', 'error');
        valid = false;
      } else {
        hideErr('reg-pwd-err');
        setInputState('reg-pwd', 'valid');
      }

      // Confirmation
      if (pwd !== pwd2) {
        showErr('reg-pwd2-err', 'Les mots de passe ne correspondent pas.');
        setInputState('reg-pwd2', 'error');
        valid = false;
      } else {
        hideErr('reg-pwd2-err');
        if (pwd2) setInputState('reg-pwd2', 'valid');
      }

      // CGU
      if (!cgu) {
        showErr('reg-cgu-err', 'Vous devez accepter les CGU.');
        valid = false;
      } else {
        hideErr('reg-cgu-err');
      }

      if (!valid) return;

      const payload = {
        prenom: $('reg-prenom').value.trim(),
        nom: $('reg-nom').value.trim(),
        email: $('reg-email').value.trim(),
        telephone: '+237' + $('reg-tel').value.replace(/\s/g, ''),
        mot_de_passe: pwd
      };

      setLoading('reg-btn', true);

      try {
        const res = await fetch('/auth/registerDriver.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.code === 0) {
          // Succès
          $('reg-success').querySelector('span').textContent = data.message || 'Compte créé ! Vous allez être redirigé...';
          $('reg-success').classList.add('show');
          setTimeout(() => {
            location.href = '/app';
          }, 2000);
        } else {
          showAlert('reg-alert', data.message || 'Une erreur est survenue.');
        }
      } catch (e) {
        showAlert('reg-alert', 'Erreur de connexion. Vérifiez votre réseau.');
      } finally {
        setLoading('reg-btn', false);
      }
    }

    // ────────────────────────────────────────────
    // CONNEXION
    // ────────────────────────────────────────────
    async function handleLogin() {
      let valid = true;

      const identifier = $('login-identifier').value.trim();
      const pwd = $('login-pwd').value;

      if (!identifier) {
        showErr('login-identifier-err', 'Ce champ est requis.');
        setInputState('login-identifier', 'error');
        valid = false;
      } else {
        hideErr('login-identifier-err');
        setInputState('login-identifier', 'valid');
      }

      if (!pwd) {
        showErr('login-pwd-err', 'Mot de passe requis.');
        setInputState('login-pwd', 'error');
        valid = false;
      } else {
        hideErr('login-pwd-err');
        setInputState('login-pwd', 'valid');
      }

      if (!valid) return;

      setLoading('login-btn', true);

      try {
        const res = await fetch('/auth/loginDriver.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'login',
            identifier,
            mot_de_passe: pwd
          })
        });
        const data = await res.json();

        if (data.code === 0) {
          if (data.requires_2fa) {
            // Afficher l'étape 2FA
            show2FAStep();
            btn.disabled = false;
            btn.textContent = 'Se connecter';
            return;
          }

          // Connexion directe → redirection
          // redirectAfterLogin(data.statut);


          // Redirection selon statut KYC
          if (data.statut === 'inactif') {
            window.location.href = '/kyc';
          } else if (data.statut === 'kyc_en_attente') {
            window.location.href = '/app';
          } else if (data.statut === 'actif') {
            window.location.href = '/app';
          } else {
            showAlert('login-alert', data.message || 'Statut de compte non reconnu.');
          }
        } else {
          showAlert('login-alert', data.message || 'Identifiants incorrects.');
        }
      } catch (e) {
        showAlert('login-alert', 'Erreur de connexion. Vérifiez votre réseau.');
      } finally {
        setLoading('login-btn', false);
      }
    }

    // ── Submit connexion ──────────────────────────────
    /* async function submitLogin(e) {
      // e.preventDefault();
      const btn = document.getElementById('login-btn');
      const identifier = document.getElementById('login-identifier').value.trim();
      const password = document.getElementById('login-password').value;

      setLoginError('');
      btn.disabled = true;
      btn.textContent = 'Connexion…';

      try {
        const res = await fetch('/auth/login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'login',
            identifier,
            mot_de_passe: password
          }),
        });
        const data = await res.json();

        if (data.code !== 0) {
          setLoginError(data.message);
          btn.disabled = false;
          btn.textContent = 'Se connecter';
          return;
        }

        if (data.requires_2fa) {
          // Afficher l'étape 2FA
          show2FAStep();
          btn.disabled = false;
          btn.textContent = 'Se connecter';
          return;
        }

        // Connexion directe → redirection
        redirectAfterLogin(data.statut);

      } catch (e) {
        setLoginError('Erreur réseau, réessayez.');
        btn.disabled = false;
        btn.textContent = 'Se connecter';
      }
    } */

    // ── Vérification code 2FA ─────────────────────────
    async function submit2FA(e) {
      e?.preventDefault();
      const btn = document.getElementById('btn-2fa');
      const code = document.getElementById('input-2fa').value.replace(/\s/g, '');

      set2FAError('');
      btn.disabled = true;
      btn.textContent = 'Vérification…';

      try {
        const res = await fetch('/auth/loginDriver.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'verify_2fa',
            code
          }),
        });
        const data = await res.json();

        if (data.code !== 0) {
          set2FAError(data.message);
          btn.disabled = false;
          btn.textContent = 'Valider';
          return;
        }

        redirectAfterLogin(data.statut);

      } catch (e) {
        set2FAError('Erreur réseau.');
        btn.disabled = false;
        btn.textContent = 'Valider';
      }
    }

    function redirectAfterLogin(statut) {
      if (statut === 'actif') window.location.href = '/app';
      else if (statut === 'kyc_en_attente') window.location.href = '/app';
      else window.location.href = '/kyc';
    }

    function setLoginError(msg) {
      const el = document.getElementById('login-error');
      if (el) {
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
      }
    }

    function set2FAError(msg) {
      const el = document.getElementById('2fa-error');
      if (el) {
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
      }
    }

    // ────────────────────────────────────────────
    // ENTER KEY SUPPORT
    // ────────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter') return;
      if ($('panel-login').classList.contains('active')) handleLogin();
      else if ($('reg-step-2').style.display !== 'none') handleRegister();
      else goToStep2();
    });
  </script>
</body>

</html>