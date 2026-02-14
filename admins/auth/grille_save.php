<?php
session_start();
header('Content-Type: application/json');


// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once '../inc/main.php';

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validation des données
if (empty($data['dist_min']) && $data['dist_min'] !== 0) {
    echo json_encode(['success' => false, 'message' => 'Distance minimale requise']);
    exit;
}

if (empty($data['dist_max'])) {
    echo json_encode(['success' => false, 'message' => 'Distance maximale requise']);
    exit;
}

if (empty($data['prix_base'])) {
    echo json_encode(['success' => false, 'message' => 'Prix de base requis']);
    exit;
}

$dist_min = (int)$data['dist_min'];
$dist_max = (int)$data['dist_max'];
$prix_base = (int)$data['prix_base'];
$actif = isset($data['actif']) ? (int)$data['actif'] : 1;
$id = !empty($data['id']) ? (int)$data['id'] : null;

// Validation métier
if ($dist_min >= $dist_max) {
    echo json_encode(['success' => false, 'message' => 'La distance minimale doit être inférieure à la distance maximale']);
    exit;
}

if ($prix_base <= 0) {
    echo json_encode(['success' => false, 'message' => 'Le prix de base doit être supérieur à 0']);
    exit;
}

// Vérifier les chevauchements de tranches
$overlap_sql = "SELECT id FROM grille_tarifs 
                WHERE ((dist_min <= :dist_min AND dist_max >= :dist_min) 
                   OR (dist_min <= :dist_max AND dist_max >= :dist_max)
                   OR (dist_min >= :dist_min AND dist_max <= :dist_max))";

if ($id) {
    $overlap_sql .= " AND id != :id";
}

$overlap_stmt = $bdd->prepare($overlap_sql);
$overlap_stmt->bindParam(':dist_min', $dist_min, PDO::PARAM_INT);
$overlap_stmt->bindParam(':dist_max', $dist_max, PDO::PARAM_INT);
if ($id) {
    $overlap_stmt->bindParam(':id', $id, PDO::PARAM_INT);
}
$overlap_stmt->execute();

if ($overlap_stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Cette tranche chevauche une tranche existante']);
    exit;
}

try {
    if ($id) {
        // Mise à jour
        $sql = "UPDATE grille_tarifs 
                SET dist_min = :dist_min, 
                    dist_max = :dist_max, 
                    prix_base = :prix_base, 
                    actif = :actif 
                WHERE id = :id";
        
        $stmt = $bdd->prepare($sql);
        $stmt->bindParam(':dist_min', $dist_min, PDO::PARAM_INT);
        $stmt->bindParam(':dist_max', $dist_max, PDO::PARAM_INT);
        $stmt->bindParam(':prix_base', $prix_base, PDO::PARAM_INT);
        $stmt->bindParam(':actif', $actif, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Tranche mise à jour avec succès']);
    } else {
        // Insertion
        $sql = "INSERT INTO grille_tarifs (dist_min, dist_max, prix_base, actif) 
                VALUES (:dist_min, :dist_max, :prix_base, :actif)";
        
        $stmt = $bdd->prepare($sql);
        $stmt->bindParam(':dist_min', $dist_min, PDO::PARAM_INT);
        $stmt->bindParam(':dist_max', $dist_max, PDO::PARAM_INT);
        $stmt->bindParam(':prix_base', $prix_base, PDO::PARAM_INT);
        $stmt->bindParam(':actif', $actif, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Tranche ajoutée avec succès', 'id' => $bdd->lastInsertId()]);
    }
} catch (PDOException $e) {
    error_log("Erreur grille_save: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
}