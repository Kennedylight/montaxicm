<?php

require_once './vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;
// /auth/profil_chauffeur.php
$isDashboard = true;
include('../inc/main.php');
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
  echo json_encode(['code' => 1, 'message' => 'Non authentifié.']);
  exit;
}

$chauffeurId = (int) $_SESSION['id'];
$action      = $_GET['action'] ?? '';
$input       = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

switch ($action) {

  // ── Modifier nom / prénom ─────────────────────────────────
  case 'update_infos':
    $prenom = trim($input['prenom'] ?? '');
    $nom    = trim($input['nom']    ?? '');
    if (strlen($prenom) < 2 || strlen($nom) < 2) {
      echo json_encode(['code' => 1, 'message' => 'Prénom et nom requis (min. 2 caractères).']);
      exit;
    }
    $bdd->prepare("UPDATE chauffeurs SET prenom = ?, nom = ? WHERE id = ?")
      ->execute([$prenom, $nom, $chauffeurId]);
    $_SESSION['prenom'] = $prenom;
    $_SESSION['nom']    = $nom;
    echo json_encode(['code' => 0, 'message' => 'Profil mis à jour.', 'prenom' => $prenom, 'nom' => $nom]);
    exit;

    // ── Upload photo ──────────────────────────────────────────
  case 'update_photo':
    if (empty($_FILES['photo'])) {
      echo json_encode(['code' => 1, 'message' => 'Fichier manquant.']);
      exit;
    }
    $file    = $_FILES['photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $mime    = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if ($file['error'] !== UPLOAD_ERR_OK) {
      echo json_encode(['code' => 1, 'message' => 'Erreur upload.']);
      exit;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
      echo json_encode(['code' => 1, 'message' => 'Max 3 Mo.']);
      exit;
    }
    if (!in_array($mime, $allowed)) {
      echo json_encode(['code' => 1, 'message' => 'Format non autorisé.']);
      exit;
    }
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/chauffeurs/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    // Supprimer ancienne photo
    $old = $bdd->prepare("SELECT photo_profil FROM chauffeurs WHERE id = ?");
    $old->execute([$chauffeurId]);
    $oldPic = $old->fetchColumn();
    if ($oldPic && strpos($oldPic, '/uploads/chauffeurs/') === 0 && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldPic)) {
      unlink($_SERVER['DOCUMENT_ROOT'] . $oldPic);
    }
    $filename = 'chauffeur_' . $chauffeurId . '_' . uniqid() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $dir . $filename);
    $url = '/uploads/chauffeurs/' . $filename;
    $bdd->prepare("UPDATE chauffeurs SET photo_profil = ? WHERE id = ?")->execute([$url, $chauffeurId]);
    echo json_encode(['code' => 0, 'message' => 'Photo mise à jour.', 'url' => $url]);
    exit;

    // ── Supprimer photo ───────────────────────────────────────
  case 'delete_photo':
    $old = $bdd->prepare("SELECT photo_profil FROM chauffeurs WHERE id = ?");
    $old->execute([$chauffeurId]);
    $oldPic = $old->fetchColumn();
    if ($oldPic && strpos($oldPic, '/uploads/chauffeurs/') === 0) {
      $p = $_SERVER['DOCUMENT_ROOT'] . $oldPic;
      if (file_exists($p)) unlink($p);
    }
    $bdd->prepare("UPDATE chauffeurs SET photo_profil = NULL WHERE id = ?")->execute([$chauffeurId]);
    echo json_encode(['code' => 0, 'message' => 'Photo supprimée.']);
    exit;

    // ── Changer mot de passe ──────────────────────────────────
  case 'change_password':
    $ancien  = $input['ancien_mdp']    ?? '';
    $nouveau = $input['nouveau_mdp']   ?? '';
    $confirm = $input['confirmer_mdp'] ?? '';
    if (!$ancien || !$nouveau || !$confirm) {
      echo json_encode(['code' => 1, 'message' => 'Tous les champs sont requis.']);
      exit;
    }
    if (strlen($nouveau) < 8) {
      echo json_encode(['code' => 1, 'message' => 'Minimum 8 caractères.']);
      exit;
    }
    if ($nouveau !== $confirm) {
      echo json_encode(['code' => 1, 'message' => 'Les mots de passe ne correspondent pas.']);
      exit;
    }
    $stmt = $bdd->prepare("SELECT mot_de_passe FROM chauffeurs WHERE id = ?");
    $stmt->execute([$chauffeurId]);
    if (!password_verify($ancien, $stmt->fetchColumn())) {
      echo json_encode(['code' => 1, 'message' => 'Mot de passe actuel incorrect.']);
      exit;
    }
    $bdd->prepare("UPDATE chauffeurs SET mot_de_passe = ? WHERE id = ?")
      ->execute([password_hash($nouveau, PASSWORD_BCRYPT, ['cost' => 12]), $chauffeurId]);
    echo json_encode(['code' => 0, 'message' => 'Mot de passe modifié.']);
    exit;

    // ── Générer secret 2FA ────────────────────────────────────
    // ── Générer secret 2FA ────────────────────────────────────
  case '2fa_setup':
    $g2fa   = new Google2FA();
    $secret = $g2fa->generateSecretKey(32);

    // Stocker en session (pas encore activé en BDD)
    $_SESSION['2fa_pending_secret'] = $secret;

    // Récupérer l'email du chauffeur pour le label
    $stmt = $bdd->prepare("SELECT email FROM chauffeurs WHERE id = ?");
    $stmt->execute([$chauffeurId]);
    $email = $stmt->fetchColumn() ?: 'chauffeur@montaxi';

    $otpUrl = $g2fa->getQRCodeUrl('MonTaxi', $email, $secret);
    // QR code via api.qrserver.com (pas besoin de clé Google)
    $qrUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpUrl);

    echo json_encode([
      'code'    => 0,
      'secret'  => $secret,
      'qr_url'  => $qrUrl,
      'otp_url' => $otpUrl,
    ]);
    exit;

    // ── Activer 2FA après vérification du code ────────────────
  case '2fa_activate':
    $code   = preg_replace('/\s/', '', $input['code'] ?? '');
    $secret = $_SESSION['2fa_pending_secret'] ?? '';

    if (!$secret) {
      echo json_encode(['code' => 1, 'message' => 'Session expirée, recommencez.']);
      exit;
    }
    if (!preg_match('/^\d{6}$/', $code)) {
      echo json_encode(['code' => 1, 'message' => 'Code invalide (6 chiffres requis).']);
      exit;
    }

    $g2fa = new Google2FA();
    // window=1 = tolérance ±30s (1 intervalle de chaque côté)
    $valid = $g2fa->verifyKey($secret, $code, 1);

    if (!$valid) {
      echo json_encode(['code' => 1, 'message' => 'Code incorrect. Vérifiez votre application.']);
      exit;
    }

    $bdd->prepare("UPDATE chauffeurs SET totp_secret = ?, totp_enabled = 1 WHERE id = ?")
      ->execute([$secret, $chauffeurId]);
    unset($_SESSION['2fa_pending_secret']);

    echo json_encode(['code' => 0, 'message' => 'Double authentification activée.']);
    exit;

    // ── Désactiver 2FA ────────────────────────────────────────
  case '2fa_disable':
    $code = preg_replace('/\s/', '', $input['code'] ?? '');

    $stmt = $bdd->prepare("SELECT totp_secret FROM chauffeurs WHERE id = ?");
    $stmt->execute([$chauffeurId]);
    $secret = $stmt->fetchColumn();

    if (!$secret) {
      echo json_encode(['code' => 1, 'message' => '2FA non configurée.']);
      exit;
    }
    if (!preg_match('/^\d{6}$/', $code)) {
      echo json_encode(['code' => 1, 'message' => 'Code invalide.']);
      exit;
    }

    $g2fa  = new Google2FA();
    $valid = $g2fa->verifyKey($secret, $code, 1);

    if (!$valid) {
      echo json_encode(['code' => 1, 'message' => 'Code incorrect.']);
      exit;
    }

    $bdd->prepare("UPDATE chauffeurs SET totp_secret = NULL, totp_enabled = 0 WHERE id = ?")
      ->execute([$chauffeurId]);

    echo json_encode(['code' => 0, 'message' => 'Double authentification désactivée.']);
    exit;
  default:
    echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
    exit;
}
