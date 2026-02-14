<?php
// ============================================================
// /auth/rides.php — Gestion des courses
// Actions : nearby | accept | decline | update_position
//           go_online | go_offline | picked_up | finish | cancel
// ============================================================

$isDashboard = true;
include('../inc/main.php');

header('Content-Type: application/json; charset=utf-8');

// ── Authentification ─────────────────────────────────────────
if (!isset($_SESSION['id'])) {
  echo json_encode(['code' => 1, 'message' => 'Non authentifié.']);
  exit;
}

// ── Vérifier que le chauffeur est actif (KYC approuvé) ───────
$stmt = $bdd->prepare("SELECT id, statut FROM chauffeurs WHERE id = ?");
$stmt->execute([$_SESSION['id']]);
$ch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ch || $ch['statut'] !== 'actif') {
  echo json_encode(['code' => 1, 'message' => 'Votre compte n\'est pas encore actif. Veuillez compléter le KYC.']);
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
  case 'nearby':
    // ──────────────────────────────────────────────────────────
    $lat = filter_var($_GET['lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_GET['lng'] ?? null, FILTER_VALIDATE_FLOAT);

    if ($lat === false || $lng === false) {
      echo json_encode(['code' => 1, 'message' => 'Coordonnées GPS invalides.']);
      exit;
    }

    // Courses en attente à moins de 1 km — filtre boîte puis Haversine exact
    $stmt = $bdd->prepare("
            SELECT
                c.id,
                c.depart_latitude, c.depart_longitude,
                c.depart_adresse,  c.arrivee_adresse,
                c.arrivee_latitude, c.arrivee_longitude,
                c.prix_estime, c.duree_minutes, c.est_covoiturage,
                cl.prenom AS client_prenom, cl.nom AS client_nom,
                cl.telephone AS client_tel,
                COALESCE(
                    (SELECT AVG(e.note)
                     FROM evaluations e
                     JOIN courses co ON e.course_id = co.id
                     WHERE co.client_id = c.client_id
                       AND e.evaluateur_type = 'chauffeur'),
                    5.0
                ) AS client_note,
                (
                    6371 * ACOS(
                        COS(RADIANS(:lat)) * COS(RADIANS(c.depart_latitude))
                        * COS(RADIANS(c.depart_longitude) - RADIANS(:lng))
                        + SIN(RADIANS(:lat)) * SIN(RADIANS(c.depart_latitude))
                    )
                ) AS distance_km
            FROM courses c
            JOIN clients cl ON c.client_id = cl.id
            WHERE c.statut = 'en_attente'
              AND c.chauffeur_id IS NULL
              AND ABS(c.depart_latitude  - :lat) < 0.02
              AND ABS(c.depart_longitude - :lng) < 0.02
            HAVING distance_km <= 1.0
            ORDER BY distance_km ASC
            LIMIT 10
        ");
    $stmt->execute([':lat' => $lat, ':lng' => $lng]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = array_map(function ($c) {
      return [
        'id'              => (int)   $c['id'],
        'client'          =>          $c['client_prenom'] . ' ' . $c['client_nom'],
        'tel'             =>          $c['client_tel'],
        'note'            => round((float) $c['client_note'], 1),
        'depart'          =>          $c['depart_adresse']   ?? 'Position client',
        'arrivee'         =>          $c['arrivee_adresse']  ?? 'Destination',
        'depart_lat'      => (float)  $c['depart_latitude'],
        'depart_lng'      => (float)  $c['depart_longitude'],
        'arrivee_lat'     => (float)  $c['arrivee_latitude'],
        'arrivee_lng'     => (float)  $c['arrivee_longitude'],
        'prix'            => (int)    $c['prix_estime'],
        'duree'           => ($c['duree_minutes'] ?? 5) . ' min',
        'dist'            =>          round($c['distance_km'], 1) . ' km',
        'est_covoiturage' => (bool)   $c['est_covoiturage'],
      ];
    }, $courses);

    echo json_encode(['code' => 0, 'message' => 'OK', 'rides' => $result]);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'accept':
    // ──────────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
      exit;
    }

    $rideId = (int) ($input['ride_id'] ?? 0);
    if (!$rideId) {
      echo json_encode(['code' => 1, 'message' => 'ride_id requis.']);
      exit;
    }

    // Transaction pour garantir l'atomicité (deux chauffeurs ne peuvent pas accepter en même temps)
    $bdd->beginTransaction();
    try {
      $stmt = $bdd->prepare("
                SELECT id FROM courses
                WHERE id = ? AND statut = 'en_attente' AND chauffeur_id IS NULL
                FOR UPDATE
            ");
      $stmt->execute([$rideId]);

      if (!$stmt->fetch()) {
        $bdd->rollBack();
        echo json_encode(['code' => 1, 'message' => 'Cette course n\'est plus disponible.']);
        exit;
      }

      $bdd->prepare("
                UPDATE courses
                SET statut = 'acceptee', chauffeur_id = ?, acceptee_le = ?
                WHERE id = ?
            ")->execute([$chauffeurId, $dt, $rideId]);

      $bdd->commit();
    } catch (Exception $e) {
      $bdd->rollBack();
      echo json_encode(['code' => 1, 'message' => 'Erreur lors de l\'acceptation.']);
      exit;
    }

    echo json_encode(['code' => 0, 'message' => 'Course acceptée !', 'ride_id' => $rideId]);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'decline':
    // ──────────────────────────────────────────────────────────
    // Rien à enregistrer en BDD pour un simple refus
    echo json_encode(['code' => 0, 'message' => 'Course refusée.']);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'picked_up':
    // ──────────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
      exit;
    }

    $rideId = (int) ($input['ride_id'] ?? 0);
    if (!$rideId) {
      echo json_encode(['code' => 1, 'message' => 'ride_id requis.']);
      exit;
    }

    $bdd->prepare("
            UPDATE courses
            SET statut = 'en_cours', prise_en_charge_le = ?
            WHERE id = ? AND chauffeur_id = ?
        ")->execute([$dt, $rideId, $chauffeurId]);

    echo json_encode(['code' => 0, 'message' => 'Client pris en charge.']);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'finish':
    // ──────────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
      exit;
    }

    $rideId = (int) ($input['ride_id'] ?? 0);
    if (!$rideId) {
      echo json_encode(['code' => 1, 'message' => 'ride_id requis.']);
      exit;
    }

    // Vérifier que la course appartient bien à ce chauffeur
    $stmt = $bdd->prepare("
            SELECT id, prix_final, prix_estime
            FROM courses
            WHERE id = ? AND chauffeur_id = ? AND statut = 'en_cours'
        ");
    $stmt->execute([$rideId, $chauffeurId]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ride) {
      echo json_encode(['code' => 1, 'message' => 'Course introuvable ou déjà terminée.']);
      exit;
    }

    $bdd->prepare("
            UPDATE courses SET statut = 'terminee', terminee_le = ? WHERE id = ?
        ")->execute([$dt, $rideId]);

    $bdd->prepare("
            UPDATE chauffeurs SET nombre_courses = nombre_courses + 1 WHERE id = ?
        ")->execute([$chauffeurId]);

    echo json_encode(['code' => 0, 'message' => 'Course terminée avec succès !']);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'cancel':
    // ──────────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
      exit;
    }

    $rideId = (int) ($input['ride_id'] ?? 0);
    if (!$rideId) {
      echo json_encode(['code' => 1, 'message' => 'ride_id requis.']);
      exit;
    }

    $bdd->prepare("
            UPDATE courses
            SET statut = 'annulee', chauffeur_id = NULL
            WHERE id = ? AND chauffeur_id = ? AND statut IN ('acceptee', 'en_cours')
        ")->execute([$rideId, $chauffeurId]);

    echo json_encode(['code' => 0, 'message' => 'Course annulée.']);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'update_position':
    // ──────────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
      exit;
    }

    $lat = filter_var($input['lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $lng = filter_var($input['lng'] ?? null, FILTER_VALIDATE_FLOAT);

    if ($lat === false || $lng === false) {
      echo json_encode(['code' => 1, 'message' => 'Coordonnées invalides.']);
      exit;
    }

    $bdd->prepare("
            UPDATE chauffeurs SET latitude = ?, longitude = ? WHERE id = ?
        ")->execute([$lat, $lng, $chauffeurId]);

    echo json_encode(['code' => 0, 'message' => 'Position mise à jour.']);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'go_online':
    $lat = filter_var($input['lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $lng = filter_var($input['lng'] ?? null, FILTER_VALIDATE_FLOAT);

    $sql = "UPDATE chauffeurs SET en_ligne = 1" .
      ($lat !== false && $lng !== false ? ", latitude = ?, longitude = ?" : "") .
      " WHERE id = ?";

    $params = ($lat !== false && $lng !== false)
      ? [$lat, $lng, $chauffeurId]
      : [$chauffeurId];

    $bdd->prepare($sql)->execute($params);
    echo json_encode(['code' => 0, 'message' => 'En ligne.']);
    exit;

    // ──────────────────────────────────────────────────────────
  case 'go_offline':
    // ──────────────────────────────────────────────────────────
    $bdd->prepare("UPDATE chauffeurs SET en_ligne = 0 WHERE id = ?")
      ->execute([$chauffeurId]);
    echo json_encode(['code' => 0, 'message' => 'Vous êtes maintenant hors ligne.']);
    exit;

    // ──────────────────────────────────────────────────────────
  default:
    // ──────────────────────────────────────────────────────────
    echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
    exit;
}
