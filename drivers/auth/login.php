<?php
include('../inc/main.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['code' => 1, 'message' => 'Veuillez remplir tous les champs.']);
        exit;
    }

    try {
        // 1. Récupérer l'utilisateur
        $stmt = $bdd->prepare("SELECT * FROM div_clients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Vérification du mot de passe
        if ($user && password_verify($password, $user['pass'])) {
            
            // 3. Initialisation de la session
            $_SESSION['id'] = $user['id'];
            include($inc . 'loginCli.php');

            echo json_encode([
                'code' => 0, 
                'message' => 'Connexion réussie ! Redirection...',
                'user' => [
                    'nom' => $user['noms'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            // On ne précise pas si c'est l'email ou le mdp qui est faux pour la sécurité
            echo json_encode(['code' => 1, 'message' => 'Email ou mot de passe incorrect.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['code' => 500, 'message' => 'Erreur technique.']);
    }
}