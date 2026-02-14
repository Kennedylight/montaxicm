<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../inc/main.php'; 

try {
    $query = "
        SELECT 
            id,
            noms,
            telephone,
            email,
            created_at,
            statut
        FROM div_clients        
      
        ORDER BY created_at DESC
        LIMIT 300
    ";

    $stmt = $bdd->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($clients as &$client) {
        $client['created_at'] = formatDateHuman($client['created_at'], true);
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