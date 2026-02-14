<?php
// Inclusion de la connexion PDO ($bdd) et du démarrage de session
include('../inc/main.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = trim($_POST['nom'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  // 1. Validation de sécurité (Double vérification après le JS)
  if (strlen($nom) < 3) {
    echo json_encode(['code' => 1, 'message' => 'Le nom est trop court.']);
    exit;
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['code' => 1, 'message' => 'Format d\'email invalide.']);
    exit;
  }
  if (strlen($password) < 6) {
    echo json_encode(['code' => 1, 'message' => 'Le mot de passe doit faire au moins 6 caractères.']);
    exit;
  }

  try {
    // 2. Vérifier si l'email existe déjà
    $stmt = $bdd->prepare("SELECT id FROM div_clients WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
      echo json_encode(['code' => 1, 'message' => 'Cette adresse email est déjà utilisée.']);
      exit;
    }

    // 3. Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 4. Insertion dans la base de données
    $insert = $bdd->prepare("INSERT INTO div_clients (noms, email, pass, created_at, last_update) VALUES (?, ?, ?, ?, ?)");
    if ($insert->execute([$nom, $email, $hashedPassword, $dt, $dt])) {
      echo json_encode(['code' => 0, 'message' => 'Compte créé avec succès ! Connectez-vous.']);
    } else {
      echo json_encode(['code' => 2, 'message' => 'Erreur lors de l\'enregistrement.']);
    }
  } catch (PDOException $e) {
    echo json_encode(['code' => 500, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
  }
}
