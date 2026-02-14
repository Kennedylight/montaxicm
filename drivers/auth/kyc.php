<?php
$isDashboard = true;
include('../inc/main.php');

header('Content-Type: application/json; charset=utf-8');

// Auth
if (!isset($_SESSION['id'])) {
  echo json_encode(['code' => 1, 'message' => 'Non authentifié.']);
  exit;
}

$action = $_GET['action'] ?? '';

// ── SUBMIT ──────────────────────────────────────────
if ($action === 'submit') {

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
    exit;
  }

  // KYC déjà soumis ?
  $stmt = $bdd->prepare("SELECT id, statut FROM kyc WHERE chauffeur_id = ? ORDER BY id DESC LIMIT 1");
  $stmt->execute([$_SESSION['id']]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing && in_array($existing['statut'], ['soumis', 'en_cours', 'approuve'])) {
    echo json_encode(['code' => 1, 'message' => 'KYC déjà soumis ou approuvé.']);
    exit;
  }

  // Upload helper (inline, pas de dépendance externe)
  $uploadDir  = $_SERVER['DOCUMENT_ROOT'] . '/uploads/kyc/' . $_SESSION['id'] . '/';
  $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
  $maxSize    = 5 * 1024 * 1024;

  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  $uploadFile = function (string $key) use ($uploadDir, $allowedMimes, $maxSize): string {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK)
      throw new RuntimeException("Fichier manquant : $key");
    $file = $_FILES[$key];
    if ($file['size'] > $maxSize)
      throw new RuntimeException("Fichier trop volumineux (max 5 Mo).");
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $allowedMimes))
      throw new RuntimeException("Type non autorisé : $key");
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = $key . '_' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $name))
      throw new RuntimeException("Échec sauvegarde : $key");
    return 'uploads/kyc/' . $_SESSION['id'] . '/' . $name;
  };

  try {
    $photoPath = $uploadFile('photo_chauffeur');
    $cniRecto  = $uploadFile('cni_recto');
    $cniVerso  = $uploadFile('cni_verso');
  } catch (RuntimeException $e) {
    echo json_encode(['code' => 1, 'message' => $e->getMessage()]);
    exit;
  }

  $cniNumero = trim($_POST['cni_numero'] ?? '');
  $cniExpiry = $_POST['cni_expiry'] ?: null;
  $pays      = trim($_POST['pays'] ?? 'Cameroun');
  $ville     = trim($_POST['ville'] ?? '');
  $quartier  = trim($_POST['quartier'] ?? '');
  $adresse   = trim($_POST['adresse'] ?? '');
  $lat       = filter_var($_POST['lat'] ?? null, FILTER_VALIDATE_FLOAT);
  $lng       = filter_var($_POST['lng'] ?? null, FILTER_VALIDATE_FLOAT);

  if (!$ville || !$quartier) {
    echo json_encode(['code' => 1, 'message' => 'Ville et quartier requis.']);
    exit;
  }
  if ($lat === false || $lng === false) {
    echo json_encode(['code' => 1, 'message' => 'Position GPS invalide.']);
    exit;
  }

  $stmt = $bdd->prepare("
        INSERT INTO kyc
          (chauffeur_id, photo_chauffeur, cni_recto, cni_verso, cni_numero, cni_date_expiry,
           pays, ville, quartier, adresse_complete, domicile_latitude, domicile_longitude,
           statut, soumis_le)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'soumis', ?)
    ");
  $stmt->execute([
    $_SESSION['id'],
    $photoPath,
    $cniRecto,
    $cniVerso,
    $cniNumero ?: null,
    $cniExpiry,
    $pays,
    $ville,
    $quartier,
    $adresse ?: null,
    $lat,
    $lng,
    $dt
  ]);

  $bdd->prepare("UPDATE chauffeurs SET statut = 'kyc_en_attente' WHERE id = ?")
    ->execute([$_SESSION['id']]);
  $_SESSION['statut'] = 'kyc_en_attente';

  echo json_encode(['code' => 0, 'message' => 'KYC soumis avec succès.']);

  // ── STATUS ──────────────────────────────────────────
} elseif ($action === 'status') {

  $stmt = $bdd->prepare("
        SELECT id, statut, commentaire_admin, soumis_le, verifie_le
        FROM kyc WHERE chauffeur_id = ? ORDER BY id DESC LIMIT 1
    ");
  $stmt->execute([$_SESSION['id']]);
  $kyc = $stmt->fetch(PDO::FETCH_ASSOC);

  echo json_encode(['code' => 0, 'message' => 'OK', 'kyc' => $kyc ?: null]);
} else {
  echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
}
