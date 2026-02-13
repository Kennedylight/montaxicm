<?php

if (isset($branch)) include('./auth/vendor/autoload.php');
else if (isset($osh)) include($auth . 'vendor/autoload.php');
else include('drivers/auth/vendor/autoload.php');

use Jenssegers\Agent\Agent;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

function getIP($ip)
{
	$url = "https://free.freeipapi.com/api/json/$ip";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($ch);
	$data = json_decode($response, true);
	curl_close($ch);
	return $data;
}



/**
 * Formate le prix pour une meilleure lisibilité
 * @param float $price Prix en FCFA
 * @return string Prix formaté
 */
function formatPrice($price)
{
	// Formatage selon devise
	$formatted = number_format($price, 2, '.', ' ');
	return $formatted;
}


/**
 * Permet de télécharger un fichier image sur le serveur.
 * @param array $file Le fichier image à télécharger.
 * @param string|int $id L'identifiant du type d'image (pic pour photo de profil, 0 pour image principale, 1,2,3 pour images secondaires).
 */
function uploadFile($file, $id)
{
	$Filename = $file['name'];
	$textP = $id == 'pic' || $id == 'pic2' || $id == 'pic3' ? "votre photo de profil" : "votre véhicule";
	$AcceptExt = ['jpg', 'jpeg', 'png', 'webp', $id == 'pic' || $id == 'pic2' || $id == 'pic3' ? '' : 'gif'];
	$FileSize = $file['size'];
	$FileExt = strtolower(pathinfo($Filename, PATHINFO_EXTENSION));
	$maxSize = 2 * 1024;
	$maxText = "2Mo";
	$format = "JPG, JPEG, WEBP" . ($id == 'pic' || $id == 'pic2' || $id == 'pic3' ? "" : ", GIF") . " ou PNG";
	if ($FileSize > ($maxSize * 1024)) {
		die(json_encode([
			"code" => 1,
			"message" => "Veuillez choisir un fichier image de maximum $maxText de $textP pour continuer."
		]));
	}

	if (!in_array($FileExt, $AcceptExt)) {
		die(json_encode([
			"code" => 1,
			"message" => "Seuls les fichiers avec l'extension $format sont autorisés pour $textP."
		]));
	}

	$newFileName = '';
	if ($id == 'pic' || $id == 'pic2' || $id == 'pic3') $newFileName = "profile_pic_" . uniqid() . '.' . $FileExt;
	else $newFileName = "vehicule_" . uniqid() . '.' . $FileExt;

	$destination = '';

	if ($id == 'pic') $destination = '../assets/img/admins/' . $newFileName;
	else if ($id == 'pic2') $destination = '../assets/img/chauffeurs/' . $newFileName;
	else if ($id == 'pic3') $destination = '../assets/img/clients/' . $newFileName;
	else $destination = '../assets/img/drivers/' . $newFileName;

	if (!move_uploaded_file($file['tmp_name'], $destination)) {
		die(json_encode([
			"code" => 1,
			"message" => "Une erreur s'est produite lors du téléchargement de $textP. Veuillez réessayer plus tard."
		]));
	}

	return substr($destination, 3);
}


function nameDevice()
{
	// Bibliothèque utilisée : 
	$agent = new Agent();
	$agent->setUserAgent($_SERVER['HTTP_USER_AGENT']);

	// return ($agent->platform() ?? "Système d'exploitation inconnu") . ', ' . $agent->browser() . ' V(' . ($agent->version($agent->browser()) ?? 'inconnue') . ')';
	return ($agent->platform() ?? "Système d'exploitation inconnu") . ', ' . $agent->browser();
}

/**
 * Formate une date au format humain lisible en français.
 * @param string $datetime La date au format datetime (Y-m-d H:i:s).
 * @param bool $showOnlyDate Un booléen qui a true renvoie juste la date sans l'heure d'un datetime
 * @return string La date formatée au format humain.
 */
function formatDateHuman(string $datetime, bool $showOnlyDate = false): string
{
	$dt = new DateTime($datetime);
	$now = new DateTime();
	$today = $now->format('Y-m-d');
	$d = $dt->format('Y-m-d');

	if ($d === $today) {
		return $showOnlyDate ? ttt("aujourd'hui", "today") : ttt("aujourd'hui à ", "today at ") . $dt->format('H:i');
	}

	$yesterday = (clone $now)->modify('-1 day')->format('Y-m-d');
	if ($d === $yesterday) {
		return $showOnlyDate ? ttt("hier", "yesterday") : ttt("hier à ", "yesterday at ") . $dt->format('H:i');
	}

	$avantHier = (clone $now)->modify('-2 day')->format('Y-m-d');
	if ($d === $avantHier) {
		return $showOnlyDate ? ttt("avant-hier", "before yesterday") : ttt("avant-hier à ", "before yesterday at ") . $dt->format('H:i');
	}

	// Format localisé FR si possible
	if (class_exists('\IntlDateFormatter')) {
		$fmt = new \IntlDateFormatter(ttt('fr_FR', 'en_US'), \IntlDateFormatter::LONG, \IntlDateFormatter::SHORT);
		$showOnlyDate ? $fmt->setPattern("d MMM yyyy") : $fmt->setPattern(ttt("'le' d MMM yyyy 'à' HH:mm", "MMM d',' yyyy 'at' HH:mm"));
		return $fmt->format($dt);
	}

	// fallback
	return $showOnlyDate ? ttt('le ', '') . $dt->format(ttt('d M Y', 'M\, d Y')) : ttt('le ', '') . $dt->format(ttt('d M Y \à H:i', 'M d\, Y \at H:i'));
}


/**
 * Traduction selon langue
 * @param {string} fr Texte français
 * @param {string} en Texte anglais
 * @returns {string} Texte traduit
 */
function ttt($fr, $en)
{
	global $lang;

	if (!isset($lang)) $lang = 'fr';

	return $lang == 'fr' ? $fr : $en;
}



/**
 * Créer une notification pour les utilisateurs ayant certaines autorisations
 * @param string $titre Titre de la notification
 * @param string $contenu Contenu de la notification
 * @param array $authsArray Liste des IDs d'autorisations (vide = tous les users)
 * @param int $for indique si la notification est pour soi-même ou non
 * @return bool Succès ou échec
 */
function notification($titre, $contenu, $authsArray = [], $for = 0)
{
	global $bdd, $dt;

	// Créer la notification
	$sqlNotif = "INSERT INTO div_notifications (titre, contenu, id_emetteur, created_at) VALUES (:titre, :contenu, :id_emetteur, :created_at)";
	$stmtNotif = $bdd->prepare($sqlNotif);
	$stmtNotif->execute([
		':titre' => $titre,
		':contenu' => $contenu,
		':id_emetteur' => isset($_SESSION['id']) ? $_SESSION['id'] : 0,
		':created_at' => $dt
	]);

	$notificationId = $bdd->lastInsertId();

	if (!$notificationId) return false;

	// Récupérer les utilisateurs concernés
	$users = [];

	$withID = $for > 0 ? $for : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);

	if (empty($authsArray)) {
		$where = ($for == 0 ? ' AND id <> ' : ' AND id = ') . $withID;
		// Si aucune auth spécifiée, notifier tous les users actifs
		$sqlUsers = "SELECT DISTINCT id FROM div_users WHERE locked = 0 $where";
		$stmtUsers = $bdd->query($sqlUsers);
		$users = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);
	} else {
		$where = ($_SESSION['isClient'] ? '' : ($for == 0 ? ' AND id_users <> ' : ' AND id_users = ') . $withID);
		// Récupérer les users ayant AU MOINS une des autorisations
		$placeholders = implode(',', array_fill(0, count($authsArray), '?'));
		$sqlUsers = "SELECT DISTINCT id_users 
                   FROM div_permissions_users 
                   WHERE id_permissions IN ($placeholders) AND authorize = 1 $where";
		$stmtUsers = $bdd->prepare($sqlUsers);
		$stmtUsers->execute($authsArray);
		$users = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);
	}

	// Créer une entrée pour chaque utilisateur (DISTINCT évite les doublons)
	$sqlInsertUser = "INSERT INTO div_notifications_users (id_notification, id_user, lu, created_at) 
                      VALUES (:notif_id, :user_id, 0, :created_at)";
	$stmtInsertUser = $bdd->prepare($sqlInsertUser);

	foreach (array_unique($users) as $userId) {
		$stmtInsertUser->execute([
			':notif_id' => $notificationId,
			':user_id' => $userId,
			':created_at' => $dt
		]);
	}

	return true;
}


/**
 * Envoyer une notification push à un ou plusieurs utilisateurs
 * Nécessite la librairie minishlink/web-push
 * Installation: composer require minishlink/web-push
 * 
 * @param string $title Titre de la notification
 * @param string $body Corps de la notification
 * @param string $tag Spécifie le type de notification
 * @param bool $interAction Indique si la notification s'affiche jusqu'à l'interaction du client ou non true = oui.
 * @return array Résultats de l'envoi
 */
function sendPush($title, $body, $tag = 'command', $interAction = false)
{
	global $bdd, $url;
	$uri = $url . '/#/n';

	$body = strip_tags(trim($body));

	// Clés VAPID (à générer avec: vendor/bin/web-push generate-vapid-keys)
	// IMPORTANT: Remplacez ces valeurs par vos propres clés VAPID
	$vapidPublicKey = 'BCtxUH1YA4Zxcu05GWwj3yppIlhzMnsWGXCJs1bEv2Z6n13vIu_Q2ydUiFq7mdUNcgcjk95PGawXex6hLW2uI0s'; // Clé publique
	$vapidPrivateKey = '7RFslCi_XauIUFKRjLXcLOpNuYW7HrI9jKsQ3i-RABs'; // Clé secrète
	$vapidSubject = $url; // Site

	// Si pas de clés configurées
	if (strpos($vapidPublicKey, 'VOTRE_') !== false) {
		error_log("Clés VAPID non configurées. Générez-les avec: vendor/bin/web-push generate-vapid-keys");
		return ['success' => false, 'error' => 'Les clés VAPID ne sont pas configurées.'];
	}

	// Initialiser WebPush
	$webPush = new WebPush([
		'VAPID' => [
			'subject' => $vapidSubject,
			'publicKey' => $vapidPublicKey,
			'privateKey' => $vapidPrivateKey,
		]
	]);

	// Préparer le payload
	$payload = json_encode([
		'titre' => $title,
		'mess' => $body,
		'url' => $uri,
		'img' => $url . '/assets/img/notifIcon.png',
		'tag' => 'notification-' . $tag,
		'requireInteraction' => $interAction
	]);

	// Récupérer toutes les subscriptions des utilisateurs
	/* $placeholders = implode(',', array_fill(0, count($userIds), '?'));
	$sql = "SELECT * FROM div_push_subscriptions WHERE id_user IN ($placeholders)"; */
	$sql = "SELECT * FROM div_push_subscriptions WHERE id_user != ?";
	$stmt = $bdd->prepare($sql);
	$stmt->execute([isset($_SESSION['id']) ? $_SESSION['id'] : 0]);
	$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (empty($subscriptions)) {
		return ['success' => false, 'error' => 'Pas de souscriptions disponibles.'];
	}

	// Envoyer à chaque subscription
	foreach ($subscriptions as $sub) {
		$subscription = Subscription::create([
			'endpoint' => $sub['endpoint'],
			'keys' => [
				'p256dh' => $sub['p256dh_key'],
				'auth' => $sub['auth_key']
			]
		]);

		$webPush->queueNotification($subscription, $payload);
	}

	// Envoyer les notifications
	$sendResults = $webPush->flush();

	// Analyser les résultats
	$successCount = 0;
	$errorCount = 0;
	$errorsText = "";
	foreach ($sendResults as $result) {
		if ($result->isSuccess()) {
			$successCount++;
		} else {
			$errorCount++;

			$errorsText .= "Push error: " . ($result->getReason() ?? 'unknown') . ' • ';

			$response = $result->getResponse();
			if ($response) {
				$errorsText .= "Status code: " . $response->getStatusCode() . ' • ';
			} else {
				$errorsText .= "No HTTP response • ";
			}

			// Si endpoint expiré ou invalide, supprimer la subscription
			if ($result->isSubscriptionExpired()) {
				$endpoint = $result->getEndpoint();
				$sqlDelete = "DELETE FROM div_push_subscriptions WHERE endpoint = ?";
				$stmtDelete = $bdd->prepare($sqlDelete);
				$stmtDelete->execute([$endpoint]);
				error_log("Subscription expirée supprimée: " . $endpoint);
			}
		}
	}

	return [
		'success' => true,
		'sent' => $successCount,
		'failed' => $errorCount,
		'total' => count($subscriptions),
		'errors' => $errorsText
	];
}
