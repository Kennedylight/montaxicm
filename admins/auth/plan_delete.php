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

try {
    $sql = "DELETE FROM plans_facteurs WHERE id = :id";
    $stmt = $bdd->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Plan supprimé']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Plan introuvable']);
    }
} catch (PDOException $e) {
    error_log("Erreur plan_delete: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
}