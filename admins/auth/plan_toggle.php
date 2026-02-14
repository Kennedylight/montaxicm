<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once '../inc/main.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID requis']);
    exit;
}

$id = (int)$data['id'];
$actif = isset($data['actif']) ? (int)$data['actif'] : 0;

try {
    $sql = "UPDATE plans_facteurs SET actif = :actif WHERE id = :id";
    $stmt = $bdd->prepare($sql);
    $stmt->bindParam(':actif', $actif, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
} catch (PDOException $e) {
    error_log("Erreur plan_toggle: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
}