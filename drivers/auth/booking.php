<?php

include('../inc/main.php');
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['id'])) {
  echo json_encode(['code' => 1, 'message' => 'Non authentifié.']);
  exit;
}
$clientId = (int)$_SESSION['id'];
$action   = $_GET['action'] ?? '';
$input    = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') $input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
  case 'tarifs':
    $grille = $bdd->query("SELECT id,dist_min,dist_max,prix_base FROM grille_tarifs WHERE actif=1 ORDER BY dist_min ASC")->fetchAll(PDO::FETCH_ASSOC);
    $plans  = $bdd->query("SELECT id,nom_plan,slug,facteur FROM plans_facteurs WHERE actif=1 ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['code' => 0, 'grille' => $grille, 'plans' => $plans]);
    exit;

  case 'estimate':
    $distM = (int)($input['distance_m'] ?? 0);
    $planSlug = trim($input['plan_slug'] ?? 'classique');
    if ($distM <= 0) {
      echo json_encode(['code' => 1, 'message' => 'Distance invalide.']);
      exit;
    }
    if ($distM > 9000) {
      echo json_encode(['code' => 1, 'message' => 'Distance trop grande (max 9 km).']);
      exit;
    }
    $sg = $bdd->prepare("SELECT prix_base FROM grille_tarifs WHERE actif=1 AND dist_min<=? AND dist_max>=? LIMIT 1");
    $sg->execute([$distM, $distM]);
    $tranche = $sg->fetch(PDO::FETCH_ASSOC);
    $sp = $bdd->prepare("SELECT id,nom_plan,facteur FROM plans_facteurs WHERE slug=? AND actif=1 LIMIT 1");
    $sp->execute([$planSlug]);
    $plan = $sp->fetch(PDO::FETCH_ASSOC);
    if (!$tranche || !$plan) {
      echo json_encode(['code' => 1, 'message' => 'Tarif introuvable.']);
      exit;
    }
    $prix = (int)ceil($tranche['prix_base'] * $plan['facteur']);
    echo json_encode(['code' => 0, 'prix' => $prix, 'prix_base' => (int)$tranche['prix_base'], 'facteur' => (float)$plan['facteur'], 'plan' => $plan['nom_plan'], 'plan_id' => (int)$plan['id']]);
    exit;

  case 'create':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['code' => 1, 'message' => 'Méthode non autorisée.']);
      exit;
    }
    $dLat = filter_var($input['depart_lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $dLng = filter_var($input['depart_lng'] ?? null, FILTER_VALIDATE_FLOAT);
    $aLat = filter_var($input['arrivee_lat'] ?? null, FILTER_VALIDATE_FLOAT);
    $aLng = filter_var($input['arrivee_lng'] ?? null, FILTER_VALIDATE_FLOAT);
    $dAdr = trim($input['depart_adresse'] ?? '');
    $aAdr = trim($input['arrivee_adresse'] ?? '');
    $distM = (int)($input['distance_m'] ?? 0);
    $planSlug = trim($input['plan_slug'] ?? 'classique');
    $typeV = in_array($input['type_vehicule'] ?? 'taxi', ['taxi', 'moto']) ? $input['type_vehicule'] : 'taxi';
    $nbPl = max(1, min(6, (int)($input['nb_places'] ?? 1)));
    if ($dLat === false || $dLng === false || $aLat === false || $aLng === false) {
      echo json_encode(['code' => 1, 'message' => 'Coordonnées invalides.']);
      exit;
    }
    if ($distM <= 0 || $distM > 9000) {
      echo json_encode(['code' => 1, 'message' => 'Distance invalide ou trop grande.']);
      exit;
    }
    $sc = $bdd->prepare("SELECT id FROM courses WHERE client_id=? AND statut IN('en_attente','acceptee','en_cours') LIMIT 1");
    $sc->execute([$clientId]);
    if ($sc->fetch()) {
      echo json_encode(['code' => 1, 'message' => 'Vous avez déjà une course en cours.']);
      exit;
    }
    $sg = $bdd->prepare("SELECT prix_base FROM grille_tarifs WHERE actif=1 AND dist_min<=? AND dist_max>=? LIMIT 1");
    $sg->execute([$distM, $distM]);
    $tranche = $sg->fetch(PDO::FETCH_ASSOC);
    $sp = $bdd->prepare("SELECT id,facteur FROM plans_facteurs WHERE slug=? AND actif=1 LIMIT 1");
    $sp->execute([$planSlug]);
    $plan = $sp->fetch(PDO::FETCH_ASSOC);
    if (!$tranche || !$plan) {
      echo json_encode(['code' => 1, 'message' => 'Tarif introuvable.']);
      exit;
    }
    $prix = (int)ceil($tranche['prix_base'] * $plan['facteur']);
    $duree = (int)ceil(($distM / 1000) / 20 * 60);
    $si = $bdd->prepare("INSERT INTO courses (client_id,statut,depart_latitude,depart_longitude,depart_adresse,arrivee_latitude,arrivee_longitude,arrivee_adresse,prix_estime,duree_minutes,est_covoiturage,plan_id,type_vehicule,nb_places,cree_le) VALUES (?,'en_attente',?,?,?,?,?,?,?,?,0,?,?,?,?)");
    $si->execute([$clientId, $dLat, $dLng, $dAdr, $aLat, $aLng, $aAdr, $prix, $duree, $plan['id'], $typeV, $nbPl, $dt]);
    echo json_encode(['code' => 0, 'message' => 'Course créée.', 'course_id' => (int)$bdd->lastInsertId(), 'prix' => $prix, 'duree' => $duree]);
    exit;

  case 'status':
    $courseId = (int)($_GET['course_id'] ?? 0);
    if (!$courseId) {
      echo json_encode(['code' => 1, 'message' => 'course_id requis.']);
      exit;
    }
    // REMPLACE la requête existante du case 'status' par :
    $s = $bdd->prepare("
    SELECT c.*,
           ch.prenom AS cpn, ch.nom AS cn, ch.telephone AS ctel,
           ch.latitude AS clat, ch.longitude AS clng,
           ch.note_moyenne AS cnote,
           v.marque, v.modele, v.couleur, v.immatriculation
    FROM courses c
    LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
    LEFT JOIN vehicules  v  ON v.chauffeur_id = ch.id AND v.actif = 1
    WHERE c.id = ? AND c.client_id = ?
    LIMIT 1
");
    $s->execute([$courseId, $clientId]);
    $c = $s->fetch(PDO::FETCH_ASSOC);
    if (!$c) {
      echo json_encode(['code' => 1, 'message' => 'Course introuvable.']);
      exit;
    }
    $sc = $bdd->prepare("SELECT ch.id,ch.prenom,ch.latitude,ch.longitude,(6371000*ACOS(COS(RADIANS(:lat))*COS(RADIANS(ch.latitude))*COS(RADIANS(ch.longitude)-RADIANS(:lng))+SIN(RADIANS(:lat))*SIN(RADIANS(ch.latitude)))) AS distance_m FROM chauffeurs ch WHERE ch.en_ligne=1 AND ch.statut='actif' AND ABS(ch.latitude-:lat)<0.02 AND ABS(ch.longitude-:lng)<0.02 HAVING distance_m<=1000 ORDER BY distance_m ASC LIMIT 8");
    $sc->execute([':lat' => $c['depart_latitude'], ':lng' => $c['depart_longitude']]);
    $proches = $sc->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
      'code'   => 0,
      'statut' => $c['statut'],
      'course' => [
        'id'              => (int) $c['id'],
        'statut'          => $c['statut'],
        'prix'            => (int) $c['prix_estime'],
        'duree'           => (int) $c['duree_minutes'],
        'depart_lat'      => (float) $c['depart_latitude'],
        'depart_lng'      => (float) $c['depart_longitude'],
        'depart_adresse'  => $c['depart_adresse'],
        'arrivee_lat'     => (float) $c['arrivee_latitude'],
        'arrivee_lng'     => (float) $c['arrivee_longitude'],
        'arrivee_adresse' => $c['arrivee_adresse'],
        'chauffeur'       => $c['chauffeur_id'] ? [
          'nom'             => $c['cpn'] . ' ' . $c['cn'],
          'tel'             => $c['ctel'],
          'note'            => round((float) $c['cnote'], 1),
          'lat'             => (float) $c['clat'],   // position live
          'lng'             => (float) $c['clng'],   // position live
          'vehicule'        => $c['marque'] . ' ' . $c['modele'],
          'couleur'         => $c['couleur'],
          'immatriculation' => $c['immatriculation'],
        ] : null,
      ],
      'chauffeurs_proches' => array_map(fn($x) => [
        'id'   => (int)   $x['id'],
        'nom'  =>         $x['prenom'],
        'lat'  => (float) $x['latitude'],
        'lng'  => (float) $x['longitude'],
        'dist' => (int)   $x['distance_m'],
      ], $proches),
    ]);
    exit;

  case 'cancel':
    $courseId = (int)($input['course_id'] ?? 0);
    $s = $bdd->prepare("UPDATE courses SET statut='annulee' WHERE id=? AND client_id=? AND statut='en_attente'");
    $s->execute([$courseId, $clientId]);
    echo json_encode($s->rowCount() ? ['code' => 0, 'message' => 'Course annulée.'] : ['code' => 1, 'message' => "Impossible d'annuler."]);
    exit;

  case 'active':
    // Renvoie la course active du client (en_attente, acceptee, en_cours)
    $s = $bdd->prepare("
        SELECT c.id, c.statut,
               c.depart_latitude, c.depart_longitude, c.depart_adresse,
               c.arrivee_latitude, c.arrivee_longitude, c.arrivee_adresse,
               c.prix_estime, c.duree_minutes,
               ch.prenom AS cpn, ch.nom AS cn, ch.telephone AS ctel,
               ch.latitude AS clat, ch.longitude AS clng,
               ch.note_moyenne AS cnote,
               v.marque, v.modele, v.couleur, v.immatriculation
        FROM courses c
        LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
        LEFT JOIN vehicules  v  ON v.chauffeur_id = ch.id AND v.actif = 1
        WHERE c.client_id = ?
          AND c.statut IN ('en_attente', 'acceptee', 'en_cours')
        ORDER BY c.id DESC
        LIMIT 1
    ");
    $s->execute([$clientId]);
    $c = $s->fetch(PDO::FETCH_ASSOC);

    if (!$c) {
      echo json_encode(['code' => 0, 'course' => null]);
      exit;
    }

    echo json_encode([
      'code'   => 0,
      'course' => [
        'id'              => (int)   $c['id'],
        'statut'          =>         $c['statut'],
        'prix'            => (int)   $c['prix_estime'],
        'duree'           => (int)   $c['duree_minutes'],
        'depart_lat'      => (float) $c['depart_latitude'],
        'depart_lng'      => (float) $c['depart_longitude'],
        'depart_adresse'  =>         $c['depart_adresse'],
        'arrivee_lat'     => (float) $c['arrivee_latitude'],
        'arrivee_lng'     => (float) $c['arrivee_longitude'],
        'arrivee_adresse' =>         $c['arrivee_adresse'],
        'chauffeur'       => $c['chauffeur_id'] ?? null ? [
          'nom'             => $c['cpn'] . ' ' . $c['cn'],
          'tel'             => $c['ctel'],
          'note'            => round((float) $c['cnote'], 1),
          'lat'             => (float) $c['clat'],
          'lng'             => (float) $c['clng'],
          'vehicule'        => $c['marque'] . ' ' . $c['modele'],
          'couleur'         => $c['couleur'],
          'immatriculation' => $c['immatriculation'],
        ] : null,
      ],
    ]);
    exit;

  case 'history':
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 15;
    $offset = ($page - 1) * $limit;
    $s = $bdd->prepare("SELECT c.id,c.statut,c.depart_adresse,c.arrivee_adresse,c.prix_estime,c.prix_final,c.duree_minutes,c.type_vehicule,c.nb_places,c.cree_le,c.terminee_le,pf.nom_plan,ch.prenom AS cpn,ch.nom AS cn,COALESCE(e.note,0) AS ma_note FROM courses c LEFT JOIN plans_facteurs pf ON c.plan_id=pf.id LEFT JOIN chauffeurs ch ON c.chauffeur_id=ch.id LEFT JOIN evaluations e ON e.course_id=c.id AND e.evaluateur_type='client' WHERE c.client_id=? ORDER BY c.id DESC LIMIT $limit OFFSET $offset");
    $s->execute([$clientId]);
    $st = $bdd->prepare("SELECT COUNT(*) FROM courses WHERE client_id=?");
    $st->execute([$clientId]);
    echo json_encode(['code' => 0, 'courses' => $s->fetchAll(PDO::FETCH_ASSOC), 'total' => (int)$st->fetchColumn(), 'page' => $page]);
    exit;

  default:
    echo json_encode(['code' => 1, 'message' => 'Action inconnue.']);
    exit;
}
