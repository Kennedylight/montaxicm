<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ]);
    exit;
}

require_once '../inc/main.php';

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID utilisateur invalide'
    ]);
    exit;
}

try {
    $check = $bdd->prepare("SELECT id FROM clients WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non trouvé'
        ]);
        exit;
    }

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
        exit;
    }
    

    // Suppression effective
    $stmt = $bdd->prepare("DELETE FROM clients WHERE id = ?");
    $success = $stmt->execute([$id]);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès',
            'deleted_id' => $id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Échec de la suppression'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}