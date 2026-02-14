<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once '../inc/main.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validation
if (empty($data['nom_plan'])) {
    echo json_encode(['success' => false, 'message' => 'Nom du plan requis']);
    exit;
}

if (empty($data['slug'])) {
    echo json_encode(['success' => false, 'message' => 'Slug requis']);
    exit;
}

if (empty($data['facteur']) && $data['facteur'] !== 0) {
    echo json_encode(['success' => false, 'message' => 'Facteur requis']);
    exit;
}

$nom_plan = trim($data['nom_plan']);
$slug = trim($data['slug']);
$facteur = (float)$data['facteur'];
$position = isset($data['position']) ? (int)$data['position'] : 1;
$actif = isset($data['actif']) ? (int)$data['actif'] : 1;
$id = !empty($data['id']) ? (int)$data['id'] : null;

// Validation métier
if ($facteur <= 0) {
    echo json_encode(['success' => false, 'message' => 'Le facteur doit être supérieur à 0']);
    exit;
}

if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
    echo json_encode(['success' => false, 'message' => 'Le slug ne doit contenir que des lettres minuscules, chiffres, tirets et underscores']);
    exit;
}

// Vérifier l'unicité du slug
$slug_check_sql = "SELECT id FROM plans_facteurs WHERE slug = :slug";
if ($id) {
    $slug_check_sql .= " AND id != :id";
}

$slug_check_stmt = $bdd->prepare($slug_check_sql);
$slug_check_stmt->bindParam(':slug', $slug);
if ($id) {
    $slug_check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
}
$slug_check_stmt->execute();

if ($slug_check_stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Ce slug est déjà utilisé']);
    exit;
}

try {
    if ($id) {
        // Mise à jour
        $sql = "UPDATE plans_facteurs 
                SET nom_plan = :nom_plan, 
                    slug = :slug, 
                    facteur = :facteur, 
                    position = :position,
                    actif = :actif 
                WHERE id = :id";
        
        $stmt = $bdd->prepare($sql);
        $stmt->bindParam(':nom_plan', $nom_plan);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':facteur', $facteur);
        $stmt->bindParam(':position', $position, PDO::PARAM_INT);
        $stmt->bindParam(':actif', $actif, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Plan mis à jour avec succès']);
    } else {
        // Insertion
        $sql = "INSERT INTO plans_facteurs (nom_plan, slug, facteur, position, actif) 
                VALUES (:nom_plan, :slug, :facteur, :position, :actif)";
        
        $stmt = $bdd->prepare($sql);
        $stmt->bindParam(':nom_plan', $nom_plan);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':facteur', $facteur);
        $stmt->bindParam(':position', $position, PDO::PARAM_INT);
        $stmt->bindParam(':actif', $actif, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Plan ajouté avec succès', 'id' => $bdd->lastInsertId()]);
    }
} catch (PDOException $e) {
    error_log("Erreur plan_save: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
}