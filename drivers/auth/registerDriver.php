<?php
$isDashboard = true;
include('../inc/main.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
  exit;
}

$input     = json_decode(file_get_contents('php://input'), true) ?? [];
$prenom    = trim($input['prenom'] ?? '');
$nom       = trim($input['nom'] ?? '');
$email     = trim(strtolower($input['email'] ?? ''));
$telephone = trim($input['telephone'] ?? '');
$mdp       = $input['mot_de_passe'] ?? '';

// Validation serveur
$errors = [];
if (strlen($prenom) < 2)                              $errors[] = 'Prénom invalide.';
if (strlen($nom) < 2)                                 $errors[] = 'Nom invalide.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))       $errors[] = 'Email invalide.';
if (!preg_match('/^\+237[0-9]{9}$/', $telephone))     $errors[] = 'Numéro camerounais invalide.';
if (strlen($mdp) < 8)                                 $errors[] = 'Mot de passe : minimum 8 caractères.';

if ($errors) {
  echo json_encode(['code' => 1, 'message' => implode(' ', $errors)]);
  exit;
}

// Unicité
$stmt = $bdd->prepare("SELECT id FROM chauffeurs WHERE email = ? OR telephone = ? LIMIT 1");
$stmt->execute([$email, $telephone]);
if ($stmt->fetch()) {
  echo json_encode(['code' => 1, 'message' => 'Email ou téléphone déjà utilisé.']);
  exit;
}

$hash = password_hash($mdp, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $bdd->prepare("
    INSERT INTO chauffeurs (nom, prenom, email, telephone, mot_de_passe, statut, cree_le)
    VALUES (?, ?, ?, ?, ?, 'inactif', ?)
");
$stmt->execute([$nom, $prenom, $email, $telephone, $hash, $dt]);
$id = $bdd->lastInsertId();

session_regenerate_id(true);
$_SESSION['id']     = $id;
$_SESSION['statut'] = 'inactif';
$_SESSION['prenom'] = $prenom;
$_SESSION['nom']    = $nom;

echo json_encode([
  'code'    => 0,
  'message' => 'Compte créé ! Complétez votre KYC.',
  'statut'  => 'inactif',
]);
