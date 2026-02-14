<?php
// ============================================================
// /auth/stats.php — Statistiques du chauffeur connecté
// Actions : today | history
// ============================================================

$isDashboard = true;
include('../inc/main.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
  echo json_encode(['code' => 1, 'message' => 'Non authentifié.']);
  exit;
}

$chauffeurId = (int) $_SESSION['id'];
$action      = $_GET['action'] ?? 'today';

switch ($action) {

  // ──────────────────────────────────────────────────────────
  case 'today':
    // Stats du jour : gains, nombre de courses, note moyenne
    // ──────────────────────────────────────────────────────────
    $stmt = $bdd->prepare("
            SELECT
                COUNT(*)                          AS nb_courses,
                COALESCE(SUM(prix_final), 0)      AS gains_bruts,
                COALESCE(SUM(
                    CASE WHEN prix_final IS NOT NULL
                    THEN ROUND(prix_final * 0.87)
                    ELSE 0 END
                ), 0)                             AS gains_nets
            FROM courses
            WHERE chauffeur_id = ?
              AND DATE(terminee_le) = ?
              AND statut = 'terminee'
        ");
    $stmt->execute([$chauffeurId, $dd]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Note moyenne globale
    $stmtNote = $bdd->prepare("
            SELECT COALESCE(AVG(e.note), 0) AS note_moyenne, COUNT(e.id) AS nb_avis
            FROM evaluations e
            JOIN courses c ON e.course_id = c.id
            WHERE c.chauffeur_id = ?
              AND e.evaluateur_type = 'client'
        ");
    $stmtNote->execute([$chauffeurId]);
    $noteData = $stmtNote->fetch(PDO::FETCH_ASSOC);

    // Course en cours éventuelle (pour restaurer l'état si refresh)
    $stmtActive = $bdd->prepare("
            SELECT c.id, c.statut,
                   c.depart_adresse, c.arrivee_adresse,
                   c.depart_latitude, c.depart_longitude,
                   c.arrivee_latitude, c.arrivee_longitude,
                   c.prix_estime,
                   cl.prenom AS client_prenom, cl.nom AS client_nom,
                   cl.telephone AS client_tel,
                   COALESCE(
                       (SELECT AVG(e2.note) FROM evaluations e2
                        JOIN courses c2 ON e2.course_id = c2.id
                        WHERE c2.client_id = c.client_id
                          AND e2.evaluateur_type = 'chauffeur'),
                       5.0
                   ) AS client_note
            FROM courses c
            JOIN clients cl ON c.client_id = cl.id
            WHERE c.chauffeur_id = ?
              AND c.statut IN ('acceptee', 'en_cours')
            ORDER BY c.id DESC
            LIMIT 1
        ");
    $stmtActive->execute([$chauffeurId]);
    $activeCourse = $stmtActive->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
      'code'    => 0,
      'message' => 'OK',
      'today'   => [
        'nb_courses'   => (int)   $stats['nb_courses'],
        'gains_bruts'  => (int)   $stats['gains_bruts'],
        'gains_nets'   => (int)   $stats['gains_nets'],
        'note_moyenne' => round((float) $noteData['note_moyenne'], 1),
        'nb_avis'      => (int)   $noteData['nb_avis'],
      ],
      'active_course' => $activeCourse ? [
        'id'         => (int)   $activeCourse['id'],
        'statut'     => $activeCourse['statut'],
        'client'     => $activeCourse['client_prenom'] . ' ' . $activeCourse['client_nom'],
        'tel'        => $activeCourse['client_tel'],
        'note'       => round((float) $activeCourse['client_note'], 1),
        'depart'     => $activeCourse['depart_adresse'] ?? 'Position client',
        'arrivee'    => $activeCourse['arrivee_adresse'] ?? 'Destination',
        'depart_lat' => (float) $activeCourse['depart_latitude'],
        'depart_lng' => (float) $activeCourse['depart_longitude'],
        'arrivee_lat' => (float) $activeCourse['arrivee_latitude'],
        'arrivee_lng' => (float) $activeCourse['arrivee_longitude'],
        'prix'       => (int)   $activeCourse['prix_estime'],
      ] : null,
    ]);
    break;

  // ──────────────────────────────────────────────────────────
  case 'history':
    // 7 derniers jours : pour un mini graphique ou historique
    // ──────────────────────────────────────────────────────────
    $stmt = $bdd->prepare("
            SELECT
                DATE(terminee_le)                 AS jour,
                COUNT(*)                          AS nb_courses,
                COALESCE(SUM(prix_final), 0)      AS gains_bruts
            FROM courses
            WHERE chauffeur_id = ?
              AND statut = 'terminee'
              AND terminee_le >= DATE_SUB(?, INTERVAL 7 DAY)
            GROUP BY DATE(terminee_le)
            ORDER BY jour ASC
        ");
    $stmt->execute([$chauffeurId, $dd]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['code' => 0, 'message' => 'OK', 'history' => $history]);
    break;

  default:
    echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
    exit;
}
