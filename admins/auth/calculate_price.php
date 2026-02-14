<?php
/**
 * API de calcul de tarif
 * 
 * Cette API peut être utilisée :
 * - Dans le simulateur du dashboard admin
 * - Dans votre application mobile/web pour calculer les prix des courses
 * 
 * Paramètres :
 * - distance : distance en mètres (GET ou POST)
 * - plan : slug du plan tarifaire (GET ou POST)
 * 
 * Retourne :
 * - success : true/false
 * - data : objet avec tranche, plan et prix_final
 * - message : message d'erreur si applicable
 */

header('Content-Type: application/json');
require_once '../inc/main.php';

// Récupérer les paramètres (GET ou POST)
$distance = null;
$plan_slug = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $distance = isset($_GET['distance']) ? (int)$_GET['distance'] : null;
    $plan_slug = isset($_GET['plan']) ? trim($_GET['plan']) : null;
} else {
    $data = json_decode(file_get_contents('php://input'), true);
    $distance = isset($data['distance']) ? (int)$data['distance'] : null;
    $plan_slug = isset($data['plan']) ? trim($data['plan']) : null;
}

// Validation
if ($distance === null || $distance <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Distance invalide. Veuillez fournir une distance en mètres.'
    ]);
    exit;
}

if (empty($plan_slug)) {
    echo json_encode([
        'success' => false,
        'message' => 'Plan tarifaire requis'
    ]);
    exit;
}

try {
    // Trouver la tranche correspondante
    $grille_sql = "SELECT * FROM grille_tarifs 
                   WHERE dist_min <= :distance 
                   AND dist_max >= :distance 
                   AND actif = 1 
                   LIMIT 1";
    
    $grille_stmt = $bdd->prepare($grille_sql);
    $grille_stmt->bindParam(':distance', $distance, PDO::PARAM_INT);
    $grille_stmt->execute();
    $tranche = $grille_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tranche) {
        echo json_encode([
            'success' => false,
            'message' => 'Aucune tranche tarifaire active ne correspond à cette distance'
        ]);
        exit;
    }
    
    // Récupérer le plan
    $plan_sql = "SELECT * FROM plans_facteurs 
                 WHERE slug = :slug 
                 AND actif = 1 
                 LIMIT 1";
    
    $plan_stmt = $bdd->prepare($plan_sql);
    $plan_stmt->bindParam(':slug', $plan_slug);
    $plan_stmt->execute();
    $plan = $plan_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        echo json_encode([
            'success' => false,
            'message' => 'Plan tarifaire introuvable ou inactif'
        ]);
        exit;
    }
    
    // Calculer le prix final
    $prix_final = (int)round($tranche['prix_base'] * $plan['facteur']);
    
    // Retourner le résultat
    echo json_encode([
        'success' => true,
        'data' => [
            'distance' => $distance,
            'tranche' => [
                'id' => $tranche['id'],
                'dist_min' => $tranche['dist_min'],
                'dist_max' => $tranche['dist_max'],
                'prix_base' => $tranche['prix_base']
            ],
            'plan' => [
                'id' => $plan['id'],
                'nom_plan' => $plan['nom_plan'],
                'slug' => $plan['slug'],
                'facteur' => (float)$plan['facteur']
            ],
            'prix_final' => $prix_final,
            'devise' => 'FCFA'
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur calculate_price: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du calcul du tarif'
    ]);
}