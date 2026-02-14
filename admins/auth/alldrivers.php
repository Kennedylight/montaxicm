<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../inc/main.php';

try {
    $query = "
        SELECT 
            id,
            nom,
            prenom,
            telephone,
            email,
            cree_le,
            statut
        FROM chauffeurs        
      
        ORDER BY cree_le DESC
        LIMIT 300
    ";

    $stmt = $bdd->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($clients as &$client) {
        $client['cree_le'] = formatDateHuman($client['cree_le'], true);
    }

    echo json_encode([
        'success' => true,
        'total'   => count($clients),
        'data'    => $clients
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
exit;
