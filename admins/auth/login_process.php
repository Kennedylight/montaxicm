<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Ne pas afficher les erreurs en production (juste pour debug ici)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../inc/main.php'); // ta connexion PDO ou mysqli

$response = [
    'success' => false,
    'message' => 'Erreur inconnue'
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Méthode non autorisée';
    echo json_encode($response);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $response['message'] = 'Veuillez remplir tous les champs';
    echo json_encode($response);
    exit;
}

try {
    // Exemple avec PDO — adapte selon ta config dans main.php
    $stmt = $bdd->prepare("SELECT id, email, mot_de_passe, `role` 
                           FROM administrateurs
                           WHERE email = ? AND `role` = 'super_admin'
                           LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['mot_de_passe'])) {
        // Connexion réussie
        $_SESSION['admin_logged_in']  = true;
        $_SESSION['id']         = $admin['id'];
        $_SESSION['admin_email']   = $admin['email'];
        $_SESSION['name']   = $admin['nom'] ?? 'Admin';

        $_SESSION['admin_role']       = $admin['role'] ?? 'admin';

        $response = [
            'success' => true,
            'message' => 'Connexion réussie',
            'redirect' => '/admins'
        ];
    } else {
        $response['message'] = 'Identifiants incorrects';
    }
} catch (Exception $e) {
    $response['message'] = 'Erreur serveur : ' . $e->getMessage();
}

echo json_encode($response);
exit;