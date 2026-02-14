<?php
$isDashboard = true;
include('../inc/main.php');
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
  echo json_encode(['code' => 1, 'message' => 'Non authentifié.']);
  exit;
}

$clientId = (int) $_SESSION['id'];
$action   = $_GET['action'] ?? '';
$input    = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {

  // ── Modifier nom / prénom ─────────────────────────────────
  case 'update_infos':
    $noms = trim($input['noms'] ?? '');
    if (strlen($noms) < 3) {
      echo json_encode(['code' => 1, 'message' => 'Nom trop court.']);
      exit;
    }
    $bdd->prepare("UPDATE div_clients SET noms = ? WHERE id = ?")
      ->execute([$noms, $clientId]);

    $_SESSION['noms'] = $noms;

    echo json_encode(['code' => 0, 'message' => 'Profil mis à jour.', 'noms' => $noms]);
    exit;

    // ── Modifier photo de profil ──────────────────────────────
  case 'update_photo':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['photo'])) {
      echo json_encode(['code' => 1, 'message' => 'Fichier manquant.']);
      exit;
    }

    $file      = $_FILES['photo'];
    $maxSize   = 2 * 1024 * 1024; // 2 Mo
    $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
    $mime      = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
      echo json_encode(['code' => 1, 'message' => 'Erreur upload.']);
      exit;
    }
    if ($file['size'] > $maxSize) {
      echo json_encode(['code' => 1, 'message' => 'Image trop lourde (max 2 Mo).']);
      exit;
    }
    if (!in_array($mime, $allowed)) {
      echo json_encode(['code' => 1, 'message' => 'Format non autorisé (JPG, PNG, WEBP).']);
      exit;
    }

    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
    $dir  = $_SERVER['DOCUMENT_ROOT'] . '/uploads/clients/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    // Supprimer l'ancienne photo si locale
    $stmt = $bdd->prepare("SELECT pic FROM div_clients WHERE id = ?");
    $stmt->execute([$clientId]);
    $old = $stmt->fetchColumn();
    if ($old && strpos($old, '/uploads/clients/') === 0) {
      $oldPath = $_SERVER['DOCUMENT_ROOT'] . $old;
      if (file_exists($oldPath)) unlink($oldPath);
    }

    $filename = 'client_' . $clientId . '_' . uniqid() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $dir . $filename);

    $url = '/uploads/clients/' . $filename;
    $bdd->prepare("UPDATE div_clients SET pic = ? WHERE id = ?")->execute([$url, $clientId]);
    $_SESSION['pic'] = $url;

    echo json_encode(['code' => 0, 'message' => 'Photo mise à jour.', 'url' => $url]);
    exit;

    // ── Supprimer photo ───────────────────────────────────────
  case 'delete_photo':
    $stmt = $bdd->prepare("SELECT pic FROM div_clients WHERE id = ?");
    $stmt->execute([$clientId]);
    $old = $stmt->fetchColumn();
    if ($old && strpos($old, '/uploads/clients/') === 0) {
      $oldPath = $_SERVER['DOCUMENT_ROOT'] . $old;
      if (file_exists($oldPath)) unlink($oldPath);
    }
    $bdd->prepare("UPDATE div_clients SET pic = NULL WHERE id = ?")->execute([$clientId]);
    $_SESSION['pic'] = '';

    echo json_encode(['code' => 0, 'message' => 'Photo supprimée.', 'url' => '']);
    exit;

    // ── Changer mot de passe ──────────────────────────────────
  case 'change_password':
    $ancien  = $input['ancien_mdp']     ?? '';
    $nouveau = $input['nouveau_mdp']    ?? '';
    $confirm = $input['confirmer_mdp']  ?? '';

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

    $stmt = $bdd->prepare("SELECT pass FROM div_clients WHERE id = ?");
    $stmt->execute([$clientId]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($ancien, $hash)) {
      echo json_encode(['code' => 1, 'message' => 'Mot de passe actuel incorrect.']);
      exit;
    }

    $newHash = password_hash($nouveau, PASSWORD_BCRYPT, ['cost' => 12]);
    $bdd->prepare("UPDATE div_clients SET pass = ? WHERE id = ?")->execute([$newHash, $clientId]);

    echo json_encode(['code' => 0, 'message' => 'Mot de passe modifié avec succès.']);
    exit;

  default:
    echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
    exit;
}
