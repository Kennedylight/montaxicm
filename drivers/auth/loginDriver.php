<?php
// /auth/login.php — Connexion chauffeur avec 2FA optionnelle
$isDashboard = false;
include('../inc/main.php');

header('Content-Type: application/json; charset=utf-8');

require_once './vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$action = $input['action'] ?? 'login'; // login | verify_2fa

// ══════════════════════════════════════════════════════════════
// ÉTAPE 1 — Vérification identifiant + mot de passe
// ══════════════════════════════════════════════════════════════
if ($action === 'login') {
  $identifier = trim($input['identifier']    ?? '');
  $motDePasse = trim($input['mot_de_passe']  ?? '');

  if (!$identifier || !$motDePasse) {
    echo json_encode(['code' => 1, 'message' => 'Identifiant et mot de passe requis.']);
    exit;
  }

  // Chercher par email OU téléphone
  $stmt = $bdd->prepare("
        SELECT id, prenom, nom, email, telephone, mot_de_passe,
               statut, totp_enabled, totp_secret
        FROM chauffeurs
        WHERE email = ? OR telephone = ?
        LIMIT 1
    ");
  $stmt->execute([$identifier, $identifier]);
  $chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$chauffeur || !password_verify($motDePasse, $chauffeur['mot_de_passe'])) {
    echo json_encode(['code' => 1, 'message' => 'Identifiant ou mot de passe incorrect.']);
    exit;
  }

  if ($chauffeur['statut'] === 'suspendu') {
    echo json_encode(['code' => 1, 'message' => 'Compte suspendu. Contactez le support.']);
    exit;
  }

  // ── 2FA activée → on ne crée pas encore la session ───────
  if ($chauffeur['totp_enabled']) {
    // Stocker l'ID en session temporaire (pas encore connecté)
    session_regenerate_id(true);
    $_SESSION['2fa_pending_id']     = (int) $chauffeur['id'];
    $_SESSION['2fa_pending_prenom'] = $chauffeur['prenom'];
    $_SESSION['2fa_pending_nom']    = $chauffeur['nom'];
    $_SESSION['2fa_pending_statut'] = $chauffeur['statut'];

    echo json_encode([
      'code'       => 0,
      'requires_2fa' => true,
      'message'    => 'Code 2FA requis.',
    ]);
    exit;
  }

  // ── Pas de 2FA → session directe ─────────────────────────
  session_regenerate_id(true);
  $_SESSION['id']     = (int) $chauffeur['id'];
  $_SESSION['statut'] = $chauffeur['statut'];
  $_SESSION['prenom'] = $chauffeur['prenom'];
  $_SESSION['nom']    = $chauffeur['nom'];

  $bdd->prepare("UPDATE chauffeurs SET derniere_connexion = ? WHERE id = ?")
    ->execute([$dt, $chauffeur['id']]);

  echo json_encode([
    'code'         => 0,
    'requires_2fa' => false,
    'message'      => 'Connexion réussie.',
    'statut'       => $chauffeur['statut'],
    'prenom'       => $chauffeur['prenom'],
    'nom'          => $chauffeur['nom'],
  ]);
  exit;
}

// ══════════════════════════════════════════════════════════════
// ÉTAPE 2 — Vérification du code TOTP
// ══════════════════════════════════════════════════════════════
if ($action === 'verify_2fa') {
  $code = preg_replace('/\s/', '', $input['code'] ?? '');

  // Vérifier qu'on a bien une session 2FA en attente
  if (empty($_SESSION['2fa_pending_id'])) {
    echo json_encode(['code' => 1, 'message' => 'Session expirée. Reconnectez-vous.']);
    exit;
  }
  if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['code' => 1, 'message' => 'Code invalide (6 chiffres requis).']);
    exit;
  }

  $pendingId = (int) $_SESSION['2fa_pending_id'];

  // Récupérer le secret TOTP
  $stmt = $bdd->prepare("SELECT totp_secret FROM chauffeurs WHERE id = ?");
  $stmt->execute([$pendingId]);
  $secret = $stmt->fetchColumn();

  if (!$secret) {
    echo json_encode(['code' => 1, 'message' => 'Configuration 2FA introuvable.']);
    exit;
  }

  $g2fa  = new Google2FA();
  $valid = $g2fa->verifyKey($secret, $code, 1);

  if (!$valid) {
    echo json_encode(['code' => 1, 'message' => 'Code incorrect ou expiré. Réessayez.']);
    exit;
  }

  // Code valide → créer la vraie session
  $prenom = $_SESSION['2fa_pending_prenom'];
  $nom    = $_SESSION['2fa_pending_nom'];
  $statut = $_SESSION['2fa_pending_statut'];

  // Nettoyer les données temporaires
  unset(
    $_SESSION['2fa_pending_id'],
    $_SESSION['2fa_pending_prenom'],
    $_SESSION['2fa_pending_nom'],
    $_SESSION['2fa_pending_statut']
  );

  session_regenerate_id(true);
  $_SESSION['id']     = $pendingId;
  $_SESSION['statut'] = $statut;
  $_SESSION['prenom'] = $prenom;
  $_SESSION['nom']    = $nom;

  $bdd->prepare("UPDATE chauffeurs SET derniere_connexion = ? WHERE id = ?")
    ->execute([$dt, $pendingId]);

  echo json_encode([
    'code'    => 0,
    'message' => 'Connexion réussie.',
    'statut'  => $statut,
    'prenom'  => $prenom,
    'nom'     => $nom,
  ]);
  exit;
}

echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
