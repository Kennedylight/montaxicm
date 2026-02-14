<?php
$isDashboard = true;
include('../inc/main.php');

// Non connecté → /sign
if (!isset($_SESSION['id'])) {
  header('Location: /sign?mode=login');
  exit;
}

// Vérifier statut KYC
$stmt = $bdd->prepare("SELECT statut FROM kyc WHERE chauffeur_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['id']]);
$kyc = $stmt->fetch(PDO::FETCH_ASSOC);

// KYC déjà soumis ou approuvé → inutile de revenir ici
if ($kyc && in_array($kyc['statut'], ['soumis', 'en_cours', 'approuve'])) {
  header('Location: /app');
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MonTaxi — Vérification d'identité (KYC)</title>
  <link rel="stylesheet" href="<?= $css ?>polices.css">
  <link rel="shortcut icon" href="<?= $img ?>fav.png" type="image/x-icon">
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
      min-height: 100vh
    }

    /* ── TOP BAR ── */
    .topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      background: rgba(255, 255, 255, .94);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(0, 0, 0, .06);
      padding: 16px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .logo {
      font-family: Po01;
      font-size: 1.2rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      color: var(--texte)
    }

    .logo img {
      width: 180px;
    }

    .logo-badge {
      background: var(--noir);
      color: var(--or);
      border-radius: 6px;
      padding: 2px 8px;
      font-size: .8rem
    }

    .topbar-right {
      font-size: .82rem;
      color: var(--texte-doux)
    }

    /* ── PROGRESS BAR ── */
    .progress-wrap {
      position: fixed;
      top: 61px;
      left: 0;
      right: 0;
      z-index: 99;
      background: var(--blanc);
      border-bottom: 1px solid var(--gris-clair);
      padding: 16px 40px;
    }

    .progress-steps {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0;
      max-width: 600px;
      margin: 0 auto
    }

    .ps {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      position: relative;
      z-index: 1;
      min-width: 80px
    }

    .ps-dot {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 2px solid var(--gris-clair);
      background: var(--blanc);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .85rem;
      font-weight: 700;
      color: var(--texte-doux);
      transition: all .3s;
    }

    .ps.active .ps-dot {
      border-color: var(--noir);
      background: var(--noir);
      color: var(--blanc)
    }

    .ps.done .ps-dot {
      border-color: var(--succes);
      background: var(--succes);
      color: var(--blanc)
    }

    .ps-label {
      font-size: .68rem;
      color: var(--texte-doux);
      text-align: center;
      white-space: nowrap
    }

    .ps.active .ps-label {
      color: var(--texte);
      font-weight: 600
    }

    .ps-line {
      flex: 1;
      height: 2px;
      background: var(--gris-clair);
      transition: background .3s;
      margin-bottom: 20px
    }

    .ps-line.done {
      background: var(--succes)
    }

    /* ── MAIN ── */
    main {
      max-width: 640px;
      margin: 0 auto;
      padding: 180px 24px 80px;
    }

    .kyc-card {
      background: var(--blanc);
      border-radius: 20px;
      border: 1px solid var(--gris-clair);
      padding: 40px;
    }

    .step-wrap {
      display: none
    }

    .step-wrap.active {
      display: block
    }

    .step-header {
      margin-bottom: 28px
    }

    .step-tag {
      display: inline-block;
      background: var(--gris-bg);
      border: 1px solid var(--gris-clair);
      border-radius: 50px;
      padding: 4px 14px;
      font-size: .73rem;
      font-weight: 600;
      color: var(--texte-doux);
      margin-bottom: 10px;
    }

    .step-title {
      font-family: Po01;
      font-size: 1.5rem;
      font-weight: 800;
      margin-bottom: 8px
    }

    .step-sub {
      font-size: .88rem;
      color: var(--texte-doux);
      line-height: 1.6
    }

    /* ── UPLOAD ZONE ── */
    .upload-zone {
      border: 2px dashed var(--gris-clair);
      border-radius: 14px;
      padding: 32px 20px;
      text-align: center;
      cursor: pointer;
      transition: border-color .2s, background .2s;
      position: relative;
      overflow: hidden;
    }

    .upload-zone:hover {
      border-color: var(--texte);
      background: var(--gris-bg)
    }

    .upload-zone.has-file {
      border-color: var(--succes);
      border-style: solid;
      background: #f0fdf4
    }

    .upload-zone.error {
      border-color: var(--erreur)
    }

    .upload-icon {
      font-size: 2.2rem;
      margin-bottom: 10px;
      display: block
    }

    .upload-title {
      font-weight: 600;
      font-size: .95rem;
      margin-bottom: 4px
    }

    .upload-sub {
      font-size: .78rem;
      color: var(--texte-doux)
    }

    .upload-zone input[type=file] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }

    .upload-preview {
      width: 100%;
      max-height: 180px;
      object-fit: cover;
      border-radius: 10px;
      margin-top: 12px;
      display: none;
    }

    .upload-preview.show {
      display: block
    }

    .upload-filename {
      font-size: .78rem;
      color: var(--succes);
      font-weight: 600;
      margin-top: 8px;
      display: none
    }

    .upload-filename.show {
      display: block
    }

    .camera-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 12px;
      padding: 9px 20px;
      background: var(--noir);
      color: var(--blanc);
      border: none;
      border-radius: 50px;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      font-size: .82rem;
      font-weight: 500;
      transition: background .2s;
    }

    .camera-btn:hover {
      background: #222
    }

    /* Camera modal */
    .camera-modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .9);
      z-index: 200;
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 16px;
    }

    .camera-modal.open {
      display: flex
    }

    .camera-modal video {
      width: 100%;
      max-width: 400px;
      border-radius: 12px
    }

    .camera-modal-btns {
      display: flex;
      gap: 12px
    }

    .cam-snap {
      padding: 12px 32px;
      background: var(--or);
      color: var(--noir);
      border: none;
      border-radius: 50px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
    }

    .cam-close {
      padding: 12px 20px;
      background: rgba(255, 255, 255, .15);
      color: var(--blanc);
      border: 1px solid rgba(255, 255, 255, .3);
      border-radius: 50px;
      cursor: pointer;
    }

    /* ── FIELDS ── */
    .field {
      margin-bottom: 18px
    }

    .field label {
      display: block;
      font-size: .82rem;
      font-weight: 500;
      margin-bottom: 7px
    }

    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px
    }

    .input-wrap {
      position: relative
    }

    .input-icon {
      position: absolute;
      left: 13px;
      top: 50%;
      transform: translateY(-50%);
      font-size: .95rem;
      color: var(--texte-doux);
      pointer-events: none
    }

    .input-wrap input,
    .input-wrap select {
      width: 100%;
      padding: 13px 14px 13px 40px;
      border: 1.5px solid var(--gris-clair);
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem;
      background: var(--blanc);
      color: var(--texte);
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      appearance: none;
      font-family: Po02 !important;
    }

    .input-wrap input:focus,
    .input-wrap select:focus {
      border-color: var(--texte);
      box-shadow: 0 0 0 3px rgba(13, 13, 13, .07)
    }

    .input-wrap input.error,
    .input-wrap select.error {
      border-color: var(--erreur)
    }

    .input-wrap input.valid,
    .input-wrap select.valid {
      border-color: var(--succes)
    }

    .no-icon input,
    .no-icon select {
      padding-left: 14px !important
    }

    .field-error {
      font-size: .75rem;
      color: var(--erreur);
      margin-top: 5px;
      display: none
    }

    .field-error.show {
      display: block
    }

    .field-hint {
      font-size: .75rem;
      color: var(--texte-doux);
      margin-top: 5px
    }

    /* ── MAP ── */
    #kyc-map {
      height: 280px;
      border-radius: 14px;
      border: 1.5px solid var(--gris-clair);
      margin-top: 16px;
      overflow: hidden;
    }

    .map-hint {
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--gris-bg);
      border-radius: 10px;
      padding: 10px 14px;
      margin-top: 10px;
      font-size: .8rem;
      color: var(--texte-doux);
    }

    /* ── ALERT ── */
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
      display: flex
    }

    .alert-error {
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fecaca
    }

    .alert-success {
      background: #f0fdf4;
      color: #166534;
      border: 1px solid #bbf7d0
    }

    /* ── BUTTONS ── */
    .btn-row {
      display: flex;
      gap: 12px;
      margin-top: 28px
    }

    button {
      font-family: Po02 !important;
    }

    .btn-next {
      flex: 2;
      padding: 14px;
      background: var(--noir);
      color: var(--blanc);
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, transform .2s, box-shadow .2s;
    }

    .btn-next:hover:not(:disabled) {
      background: #222;
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, .14)
    }

    .btn-next:disabled {
      opacity: .5;
      cursor: not-allowed
    }

    .btn-back {
      flex: 1;
      padding: 13px;
      background: transparent;
      border: 1.5px solid var(--gris-clair);
      color: var(--texte);
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: .9rem;
      cursor: pointer;
      transition: all .2s;
    }

    .btn-back:hover {
      border-color: var(--texte)
    }

    .btn-submit-kyc {
      width: 100%;
      padding: 15px;
      background: var(--or);
      color: var(--noir);
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background .2s, transform .2s, box-shadow .2s;
      margin-top: 28px;
    }

    .btn-submit-kyc:hover:not(:disabled) {
      background: #b8943f;
      transform: translateY(-1px);
      box-shadow: 0 10px 28px rgba(201, 168, 76, .35)
    }

    .btn-submit-kyc:disabled {
      opacity: .55;
      cursor: not-allowed
    }

    .btn-submit-kyc.loading::after {
      content: '';
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(0, 0, 0, .3);
      border-top-color: var(--noir);
      border-radius: 50%;
      animation: spin .6s linear infinite;
      margin-left: 8px;
      vertical-align: middle;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg)
      }
    }

    /* ── SUCCESS SCREEN ── */
    .success-screen {
      display: none;
      text-align: center;
      padding: 20px 0
    }

    .success-screen.show {
      display: block
    }

    .success-icon {
      font-size: 4rem;
      margin-bottom: 20px;
      animation: pop .4s ease
    }

    @keyframes pop {
      from {
        transform: scale(0)
      }

      to {
        transform: scale(1)
      }
    }

    .success-title {
      font-family: Po01;
      font-size: 1.8rem;
      font-weight: 800;
      margin-bottom: 12px
    }

    .success-sub {
      font-size: .95rem;
      color: var(--texte-doux);
      line-height: 1.7;
      margin-bottom: 28px
    }

    .info-box {
      background: var(--gris-bg);
      border-radius: 14px;
      padding: 20px;
      border: 1px solid var(--gris-clair);
      text-align: left;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .info-row {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: .88rem
    }

    .info-ico {
      font-size: 1.2rem
    }

    /* ── CNI TWO SIDES ── */
    .cni-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px
    }

    .cni-label {
      font-size: .78rem;
      font-weight: 600;
      color: var(--texte-doux);
      margin-bottom: 8px;
      text-align: center
    }

    @media(max-width:640px) {
      .topbar {
        padding: 14px 20px
      }

      .progress-wrap {
        padding: 12px 16px
      }

      .ps-label {
        display: none
      }

      main {
        padding: 160px 16px 60px
      }

      .kyc-card {
        padding: 24px 20px
      }

      .field-row,
      .cni-grid {
        grid-template-columns: 1fr
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

  <!-- TOP BAR -->
  <div class="topbar">
    <a href="index.html" class="logo">
      <img src="<?= $img ?>logo2.png">
    </a>
    <div class="topbar-right">Vérification d'identité</div>
  </div>

  <!-- PROGRESS -->
  <div class="progress-wrap">
    <div class="progress-steps">
      <div class="ps active" id="ps-1">
        <div class="ps-dot">📷</div>
        <div class="ps-label">Photo</div>
      </div>
      <div class="ps-line" id="pl-1"></div>
      <div class="ps" id="ps-2">
        <div class="ps-dot">🪪</div>
        <div class="ps-label">Identité</div>
      </div>
      <div class="ps-line" id="pl-2"></div>
      <div class="ps" id="ps-3">
        <div class="ps-dot">🏠</div>
        <div class="ps-label">Domicile</div>
      </div>
      <div class="ps-line" id="pl-3"></div>
      <div class="ps" id="ps-4">
        <div class="ps-dot">🗺️</div>
        <div class="ps-label">Localisation</div>
      </div>
    </div>
  </div>

  <!-- MAIN -->
  <main>
    <div class="kyc-card">

      <!-- ALERT -->
      <div class="alert alert-error" id="kyc-alert">⚠️ <span id="kyc-alert-msg"></span></div>

      <!-- ══════════════════════ ÉTAPE 1 : Photo ══════════════════════ -->
      <div class="step-wrap active" id="kyc-step-1">
        <div class="step-header">
          <div class="step-tag">Étape 1 / 4</div>
          <h2 class="step-title">Votre photo</h2>
          <p class="step-sub">Prenez une photo nette de votre visage. Elle sera utilisée pour identifier votre profil auprès des clients.</p>
        </div>

        <div class="upload-zone" id="zone-photo" onclick="triggerInput('input-photo')">
          <span class="upload-icon">🤳</span>
          <div class="upload-title">Cliquez pour importer votre photo</div>
          <div class="upload-sub">JPG, PNG — max 5 Mo</div>
          <input type="file" id="input-photo" accept="image/*" onchange="handleFile('input-photo','zone-photo','preview-photo','fname-photo')" />
          <img id="preview-photo" class="upload-preview" alt="Aperçu photo" />
          <div class="upload-filename" id="fname-photo"></div>
        </div>

        <div style="text-align:center">
          <button class="camera-btn" onclick="openCamera('photo')">📷 Prendre une photo</button>
        </div>

        <div class="btn-row">
          <button class="btn-next" onclick="nextStep(1)">Continuer →</button>
        </div>
      </div>

      <!-- ══════════════════════ ÉTAPE 2 : CNI ══════════════════════ -->
      <div class="step-wrap" id="kyc-step-2">
        <div class="step-header">
          <div class="step-tag">Étape 2 / 4</div>
          <h2 class="step-title">Carte nationale d'identité</h2>
          <p class="step-sub">Importez les deux faces de votre CNI camerounaise. Les photos doivent être lisibles et non expirées.</p>
        </div>

        <div class="cni-grid">
          <div>
            <div class="cni-label">Recto (Face avant)</div>
            <div class="upload-zone" id="zone-cni-r" onclick="triggerInput('input-cni-r')">
              <span class="upload-icon">🪪</span>
              <div class="upload-title">Face avant</div>
              <div class="upload-sub">JPG, PNG, PDF</div>
              <input type="file" id="input-cni-r" accept="image/*,.pdf" onchange="handleFile('input-cni-r','zone-cni-r','preview-cni-r','fname-cni-r')" />
              <img id="preview-cni-r" class="upload-preview" alt="" />
              <div class="upload-filename" id="fname-cni-r"></div>
            </div>
            <button class="camera-btn" style="width:100%;justify-content:center;margin-top:8px" onclick="openCamera('cni-r')">📷 Photographier</button>
          </div>
          <div>
            <div class="cni-label">Verso (Face arrière)</div>
            <div class="upload-zone" id="zone-cni-v" onclick="triggerInput('input-cni-v')">
              <span class="upload-icon">🪪</span>
              <div class="upload-title">Face arrière</div>
              <div class="upload-sub">JPG, PNG, PDF</div>
              <input type="file" id="input-cni-v" accept="image/*,.pdf" onchange="handleFile('input-cni-v','zone-cni-v','preview-cni-v','fname-cni-v')" />
              <img id="preview-cni-v" class="upload-preview" alt="" />
              <div class="upload-filename" id="fname-cni-v"></div>
            </div>
            <button class="camera-btn" style="width:100%;justify-content:center;margin-top:8px" onclick="openCamera('cni-v')">📷 Photographier</button>
          </div>
        </div>

        <div style="margin-top:20px">
          <div class="field-row">
            <div class="field">
              <label>Numéro CNI</label>
              <div class="input-wrap">
                <span class="input-icon">🔢</span>
                <input type="text" id="cni-numero" placeholder="Ex: CM1234567" />
              </div>
            </div>
            <div class="field">
              <label>Date d'expiration</label>
              <div class="input-wrap">
                <span class="input-icon">📅</span>
                <input type="date" id="cni-expiry" />
              </div>
            </div>
          </div>
        </div>

        <div class="btn-row">
          <button class="btn-back" onclick="prevStep(2)">← Retour</button>
          <button class="btn-next" onclick="nextStep(2)">Continuer →</button>
        </div>
      </div>

      <!-- ══════════════════════ ÉTAPE 3 : Domicile ══════════════════════ -->
      <div class="step-wrap" id="kyc-step-3">
        <div class="step-header">
          <div class="step-tag">Étape 3 / 4</div>
          <h2 class="step-title">Lieu de résidence</h2>
          <p class="step-sub">Renseignez votre adresse de domicile. Ces informations restent confidentielles.</p>
        </div>

        <div class="field">
          <label>Pays *</label>
          <div class="input-wrap">
            <span class="input-icon">🌍</span>
            <select id="kyc-pays">
              <option value="Cameroun" selected>Cameroun 🇨🇲</option>
              <option value="Nigeria">Nigeria</option>
              <option value="Tchad">Tchad</option>
              <option value="RCA">RCA</option>
            </select>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label>Ville *</label>
            <div class="input-wrap">
              <span class="input-icon">🏙️</span>
              <select id="kyc-ville">
                <option value="">Sélectionnez</option>
                <option value="Yaoundé">Yaoundé</option>
                <option value="Douala">Douala</option>
                <option value="Bafoussam">Bafoussam</option>
                <option value="Garoua">Garoua</option>
                <option value="Bamenda">Bamenda</option>
                <option value="Maroua">Maroua</option>
                <option value="Ngaoundéré">Ngaoundéré</option>
                <option value="Bertoua">Bertoua</option>
                <option value="Ebolowa">Ebolowa</option>
                <option value="Kribi">Kribi</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
            <div class="field-error" id="err-ville">Ville requise.</div>
          </div>
          <div class="field">
            <label>Quartier *</label>
            <div class="input-wrap">
              <span class="input-icon">🏘️</span>
              <input type="text" id="kyc-quartier" placeholder="Ex: Bastos, Akwa..." />
            </div>
            <div class="field-error" id="err-quartier">Quartier requis.</div>
          </div>
        </div>

        <div class="field">
          <label>Adresse complète</label>
          <div class="input-wrap no-icon">
            <input type="text" id="kyc-adresse" placeholder="Rue, numéro, repère..." />
          </div>
          <div class="field-hint">Aidez-nous à vous localiser précisément (optionnel).</div>
        </div>

        <div class="btn-row">
          <button class="btn-back" onclick="prevStep(3)">← Retour</button>
          <button class="btn-next" onclick="nextStep(3)">Continuer →</button>
        </div>
      </div>

      <!-- ══════════════════════ ÉTAPE 4 : Carte ══════════════════════ -->
      <div class="step-wrap" id="kyc-step-4">
        <div class="step-header">
          <div class="step-tag">Étape 4 / 4</div>
          <h2 class="step-title">Plan de localisation</h2>
          <p class="step-sub">Épinglez votre domicile sur la carte pour faciliter les vérifications. Faites glisser le marqueur pour ajuster.</p>
        </div>

        <div id="kyc-map"></div>

        <div class="map-hint">
          📍 <span id="map-coords-label">Déplacez le marqueur sur votre domicile</span>
        </div>

        <div class="field-row" style="margin-top:16px">
          <div class="field">
            <label>Latitude</label>
            <div class="input-wrap no-icon">
              <input type="number" id="kyc-lat" step="any" placeholder="3.8480" readonly />
            </div>
          </div>
          <div class="field">
            <label>Longitude</label>
            <div class="input-wrap no-icon">
              <input type="number" id="kyc-lng" step="any" placeholder="11.5021" readonly />
            </div>
          </div>
        </div>

        <div class="btn-row">
          <button class="btn-back" onclick="prevStep(4)">← Retour</button>
        </div>

        <button class="btn-submit-kyc" id="submit-kyc-btn" onclick="submitKYC()">
          ✅ Soumettre mon dossier KYC
        </button>
      </div>

      <!-- ══════════════════════ SUCCÈS ══════════════════════ -->
      <div class="success-screen" id="kyc-success">
        <div class="success-icon">🎉</div>
        <h2 class="success-title">Dossier envoyé !</h2>
        <p class="success-sub">Votre dossier KYC a été soumis avec succès. Notre équipe va le vérifier sous <strong>24 à 48 heures</strong>. Vous serez notifié dès qu'il sera approuvé.</p>

        <div class="info-box">
          <div class="info-row">
            <span class="info-ico">📧</span>
            <span>Vous recevrez un email de confirmation dès la validation.</span>
          </div>
          <div class="info-row">
            <span class="info-ico">⏳</span>
            <span>Délai moyen de vérification : <strong>24h</strong> ouvrées.</span>
          </div>
          <div class="info-row">
            <span class="info-ico">🚖</span>
            <span>Une fois approuvé, vous pourrez commencer à accepter des courses.</span>
          </div>
        </div>

        <div style="margin-top:24px">
          <a href="/" style="display:inline-block;padding:13px 32px;background:var(--noir);color:var(--blanc);border-radius:12px;text-decoration:none;font-weight:600">
            Retour à l'accueil
          </a>
        </div>
      </div>

    </div>
  </main>

  <!-- ── CAMERA MODAL ── -->
  <div class="camera-modal" id="camera-modal">
    <video id="cam-video" autoplay playsinline></video>
    <div class="camera-modal-btns">
      <button class="cam-snap" onclick="snapPhoto()">📸 Capturer</button>
      <button class="cam-close" onclick="closeCamera()">✕ Annuler</button>
    </div>
    <canvas id="cam-canvas" style="display:none"></canvas>
  </div>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCrbY593deLu2Oic6xjs2BLN1UHmi2rBnQ&libraries=places"></script>
  <script>
    // ────────────────────────────────────────────
    // STATE
    // ────────────────────────────────────────────
    let currentStep = 1;
    let kycMap = null;
    let kycMarker = null;
    let domLat = 3.8480,
      domLng = 11.5021; // Yaoundé par défaut
    let camTarget = null;
    let camStream = null;

    const FILES = {
      photo: null,
      'cni-r': null,
      'cni-v': null
    };

    // ────────────────────────────────────────────
    // UTILS
    // ────────────────────────────────────────────
    function $(id) {
      return document.getElementById(id)
    }

    function showErr(id, msg) {
      const e = $(id);
      if (msg) e.textContent = msg;
      e.classList.add('show')
    }

    function hideErr(id) {
      $(id).classList.remove('show')
    }

    function setInputState(id, s) {
      const el = $(id);
      el.classList.remove('error', 'valid');
      if (s) el.classList.add(s)
    }

    function showAlert(msg) {
      $('kyc-alert-msg').textContent = msg;
      $('kyc-alert').classList.add('show');
      setTimeout(() => $('kyc-alert').classList.remove('show'), 5000);
    }

    // ────────────────────────────────────────────
    // FILE UPLOAD
    // ────────────────────────────────────────────
    function triggerInput(id) {
      $(id).click()
    }

    function handleFile(inputId, zoneId, previewId, fnameId) {
      const input = $(inputId);
      const file = input.files[0];
      if (!file) return;

      const key = inputId.replace('input-', '');
      FILES[key] = file;

      const zone = $(zoneId);
      zone.classList.add('has-file');
      zone.classList.remove('error');

      const fname = $(fnameId);
      fname.textContent = '✓ ' + file.name;
      fname.classList.add('show');

      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
          const prev = $(previewId);
          prev.src = e.target.result;
          prev.classList.add('show');
        };
        reader.readAsDataURL(file);
      }
    }

    // ────────────────────────────────────────────
    // CAMERA
    // ────────────────────────────────────────────
    async function openCamera(target) {
      camTarget = target;
      try {
        camStream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: 'environment'
          }
        });
        $('cam-video').srcObject = camStream;
        $('camera-modal').classList.add('open');
      } catch (e) {
        showAlert('Impossible d\'accéder à la caméra. Vérifiez les permissions.');
      }
    }

    function snapPhoto() {
      const video = $('cam-video');
      const canvas = $('cam-canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas.getContext('2d').drawImage(video, 0, 0);

      canvas.toBlob(blob => {
        const file = new File([blob], `${camTarget}.jpg`, {
          type: 'image/jpeg'
        });
        const key = camTarget;

        // Map camera targets to input IDs
        const map = {
          photo: 'input-photo',
          'cni-r': 'input-cni-r',
          'cni-v': 'input-cni-v'
        };
        const zoneMap = {
          photo: 'zone-photo',
          'cni-r': 'zone-cni-r',
          'cni-v': 'zone-cni-v'
        };
        const prevMap = {
          photo: 'preview-photo',
          'cni-r': 'preview-cni-r',
          'cni-v': 'preview-cni-v'
        };
        const fnameMap = {
          photo: 'fname-photo',
          'cni-r': 'fname-cni-r',
          'cni-v': 'fname-cni-v'
        };

        FILES[key] = file;

        // Update UI
        const zone = $(zoneMap[key]);
        zone.classList.add('has-file');
        $(fnameMap[key]).textContent = '📷 Photo prise';
        $(fnameMap[key]).classList.add('show');

        const reader = new FileReader();
        reader.onload = e => {
          const p = $(prevMap[key]);
          p.src = e.target.result;
          p.classList.add('show');
        };
        reader.readAsDataURL(file);

        closeCamera();
      }, 'image/jpeg', 0.85);
    }

    function closeCamera() {
      if (camStream) {
        camStream.getTracks().forEach(t => t.stop());
        camStream = null;
      }
      $('camera-modal').classList.remove('open');
    }

    // ────────────────────────────────────────────
    // STEP NAVIGATION
    // ────────────────────────────────────────────
    function nextStep(from) {
      if (!validateStep(from)) return;

      // Hide current
      $(`kyc-step-${from}`).classList.remove('active');
      const to = from + 1;
      $(`kyc-step-${to}`).classList.add('active');
      currentStep = to;

      updateProgress(to);
      if (to === 4) initMap();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }

    function prevStep(from) {
      $(`kyc-step-${from}`).classList.remove('active');
      const to = from - 1;
      $(`kyc-step-${to}`).classList.add('active');
      currentStep = to;
      updateProgress(to);
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }

    function updateProgress(step) {
      for (let i = 1; i <= 4; i++) {
        const ps = $(`ps-${i}`);
        ps.classList.remove('active', 'done');
        if (i < step) ps.classList.add('done');
        else if (i === step) ps.classList.add('active');
      }
      for (let i = 1; i <= 3; i++) {
        const pl = $(`pl-${i}`);
        pl.className = 'ps-line' + (i < step ? ' done' : '');
      }
    }

    // ────────────────────────────────────────────
    // VALIDATION PAR ÉTAPE
    // ────────────────────────────────────────────
    function validateStep(step) {
      if (step === 1) {
        if (!FILES.photo) {
          $('zone-photo').classList.add('error');
          showAlert('Veuillez ajouter votre photo avant de continuer.');
          return false;
        }
        $('zone-photo').classList.remove('error');
        return true;
      }

      if (step === 2) {
        let ok = true;
        if (!FILES['cni-r']) {
          $('zone-cni-r').classList.add('error');
          ok = false;
        }
        if (!FILES['cni-v']) {
          $('zone-cni-v').classList.add('error');
          ok = false;
        }
        if (!ok) {
          showAlert('Importez les deux faces de votre CNI.');
          return false;
        }
        return true;
      }

      if (step === 3) {
        let ok = true;
        if (!$('kyc-ville').value) {
          showErr('err-ville', 'Ville requise.');
          ok = false;
        } else hideErr('err-ville');
        if (!$('kyc-quartier').value.trim()) {
          showErr('err-quartier', 'Quartier requis.');
          ok = false;
        } else hideErr('err-quartier');
        return ok;
      }

      return true;
    }

    // ────────────────────────────────────────────
    // CARTE LEAFLET
    // ────────────────────────────────────────────
    function initMap() {
      if (kycMap) return;

      // Initialisation Google Maps
      kycMap = new google.maps.Map(document.getElementById("kyc-map"), {
        center: {
          lat: domLat,
          lng: domLng
        },
        zoom: 15,
        disableDefaultUI: true,
        clickableIcons: false
      });

      // Marqueur
      kycMarker = new google.maps.Marker({
        position: {
          lat: domLat,
          lng: domLng
        },
        map: kycMap,
        draggable: true,
        title: "Votre domicile"
      });

      // Événement fin de drag
      kycMarker.addListener('dragend', function(e) {
        updatePosition(e.latLng.lat(), e.latLng.lng());
      });

      // Événement clic sur la carte
      kycMap.addListener('click', function(e) {
        kycMarker.setPosition(e.latLng);
        updatePosition(e.latLng.lat(), e.latLng.lng());
      });

      // Try geolocation
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
          const lat = pos.coords.latitude,
            lng = pos.coords.longitude;

          kycMap.setCenter({
            lat,
            lng
          });
          kycMarker.setPosition({
            lat,
            lng
          });
          updatePosition(lat, lng);
        }, () => {
          updateCoords(domLat, domLng);
        });
      } else {
        updateCoords(domLat, domLng);
      }
    }

    function updatePosition(lat, lng) {
      domLat = lat;
      domLng = lng;
      updateCoords(lat, lng);
    }

    function updateCoords(lat, lng) {
      $('kyc-lat').value = lat.toFixed(6);
      $('kyc-lng').value = lng.toFixed(6);
      $('map-coords-label').textContent = `Position : ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    }

    // ────────────────────────────────────────────
    // SUBMIT KYC
    // ────────────────────────────────────────────
    async function submitKYC() {
      if (!$('kyc-lat').value) {
        showAlert('Veuillez positionner votre domicile sur la carte.');
        return;
      }

      const btn = $('submit-kyc-btn');
      btn.disabled = true;
      btn.classList.add('loading');
      btn.textContent = 'Envoi en cours...';

      try {
        const formData = new FormData();
        formData.append('photo_chauffeur', FILES.photo);
        formData.append('cni_recto', FILES['cni-r']);
        formData.append('cni_verso', FILES['cni-v']);
        formData.append('cni_numero', $('cni-numero').value.trim());
        formData.append('cni_expiry', $('cni-expiry').value);
        formData.append('pays', $('kyc-pays').value);
        formData.append('ville', $('kyc-ville').value);
        formData.append('quartier', $('kyc-quartier').value.trim());
        formData.append('adresse', $('kyc-adresse').value.trim());
        formData.append('lat', domLat);
        formData.append('lng', domLng);

        const res = await fetch('/auth/kyc.php?action=submit', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.code === 0) {
          // Masquer toutes les étapes, afficher succès
          document.querySelectorAll('.step-wrap').forEach(s => s.classList.remove('active'));
          $('kyc-alert').classList.remove('show');
          $('kyc-success').classList.add('show');
          // Mettre progress à done
          for (let i = 1; i <= 4; i++) {
            $(`ps-${i}`).classList.remove('active');
            $(`ps-${i}`).classList.add('done');
          }
        } else {
          showAlert(data.message || 'Erreur lors de la soumission. Réessayez.');
          btn.disabled = false;
          btn.classList.remove('loading');
          btn.textContent = '✅ Soumettre mon dossier KYC';
        }
      } catch (e) {
        showAlert('Erreur réseau. Vérifiez votre connexion.');
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.textContent = '✅ Soumettre mon dossier KYC';
      }
    }
  </script>
</body>

</html>