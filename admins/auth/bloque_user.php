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

session_start();

require_once '../inc/main.php';  // ← $bdd est ton PDO

$id = (int)($_POST['id'] ?? 0);

if ($id < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID utilisateur invalide'
    ]);
    exit;
}

// Vérification admin connecté (tu l'avais déjà mais je le mets au début pour sortir tôt)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

try {
    // 1. Récupérer l'utilisateur actuel (un seul fetch)
    $stmt = $bdd->prepare("
        SELECT id, statut 
        FROM clients 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non trouvé'
        ]);
        exit;
    }

    // 2. Déterminer le nouveau statut (toggle simple)
    $nouveauStatut = ($user['statut'] === 'bloque') ? 'actif' : 'bloque';

    // 3. Mise à jour
    $update = $bdd->prepare("
        UPDATE clients 
        SET statut = ? 
        WHERE id = ?
    ");
    
    $success = $update->execute([$nouveauStatut, $id]);

    if ($success && $update->rowCount() > 0) {
        echo json_encode([
            'success'     => true,
            'message'     => $nouveauStatut === 'bloque' ? 'Utilisateur bloqué' : 'Utilisateur débloqué',
            'modified_id' => $id,
            'new_status'  => $nouveauStatut   // ← très utile côté front pour mise à jour visuelle
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune modification effectuée (peut-être déjà au bon statut)'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}

exit;