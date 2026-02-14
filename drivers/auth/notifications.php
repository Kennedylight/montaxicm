<?php

// ============================================================
// /auth/notifications.php — Notifications chauffeur
// Actions : list | count | read
// ============================================================

include('../inc/main.php');

header('Content-Type: application/json; charset=utf-8');

// ── Authentification ─────────────────────────────────────────
if (!isset($_SESSION['id'])) {
    echo json_encode(['code' => 1, 'message' => 'Non authentifié.']);
    exit;
}

$chauffeurId = (int) $_SESSION['id'];
$action      = $_GET['action'] ?? '';

// ── Helper : lire le body JSON ────────────────────────────────
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
}

switch ($action) {

    // ──────────────────────────────────────────────────────────
    case 'list':
        // ──────────────────────────────────────────────────────────
        $stmt = $bdd->prepare("
            SELECT id, type, titre, message, lue, cree_le
            FROM notifications
            WHERE chauffeur_id = ?
            ORDER BY cree_le DESC
            LIMIT 20
        ");
        $stmt->execute([$chauffeurId]);
        $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'code'          => 0,
            'message'       => 'OK',
            'notifications' => $notifs,
            'total'         => count($notifs),
        ]);
        exit;

        // ──────────────────────────────────────────────────────────
    case 'count':
        // ──────────────────────────────────────────────────────────
        $stmt = $bdd->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE chauffeur_id = ? AND lue = 0
        ");
        $stmt->execute([$chauffeurId]);

        echo json_encode([
            'code'    => 0,
            'message' => 'OK',
            'unread'  => (int) $stmt->fetchColumn(),
        ]);
        exit;

        // ──────────────────────────────────────────────────────────
    case 'read':
        // ──────────────────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
            exit;
        }

        $notifId = (int) ($input['notif_id'] ?? 0);

        if ($notifId) {
            // Marquer une seule notification comme lue
            $bdd->prepare("
                UPDATE notifications SET lue = 1
                WHERE id = ? AND chauffeur_id = ?
            ")->execute([$notifId, $chauffeurId]);
        } else {
            // Marquer toutes comme lues
            $bdd->prepare("
                UPDATE notifications SET lue = 1
                WHERE chauffeur_id = ?
            ")->execute([$chauffeurId]);
        }

        echo json_encode(['code' => 0, 'message' => 'Notification(s) marquée(s) comme lue(s).']);
        exit;

        // ──────────────────────────────────────────────────────────
    default:
        // ──────────────────────────────────────────────────────────
        echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
        exit;
}
