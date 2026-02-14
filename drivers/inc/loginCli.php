<?php


if (!isset($_SESSION['id'])) {
	if (isset($_SERVER['HTTP_DPLUS_FETCH_API']) && strtoupper($_SERVER['HTTP_DPLUS_FETCH_API']) == 'REQUEST_FETCH_DPLUS') echo json_encode(["message" => "Session expirée !<br>Vous n'êtes pas autorisé(e) à effectuer cette action.", "code" => 2]);
	else header('location: /logout');
	die;
}

$id = $_SESSION['id'];
$where = " AND statut = 0";

$reqx = $bdd->prepare("SELECT * FROM div_clients WHERE id = ? $where");
$reqx->execute(array($_SESSION['id']));
$resx = $reqx->fetch();

if ($resx == false) {
	if (isset($_SERVER['HTTP_DPLUS_FETCH_API']) && strtoupper($_SERVER['HTTP_DPLUS_FETCH_API']) == 'REQUEST_FETCH_DPLUS') echo json_encode(["message" => "Erreur inconnue !<br>Vous n'êtes pas autorisé(e) à effectuer cette action.", "code" => 2]);
	else {
		header('location: /logout');
	}
	die;
} else {

	$updActivity = $bdd->prepare("UPDATE div_clients SET last_update = ? WHERE id = ?");
	$updActivity->execute([$dt, $_SESSION['id']]);

	$_SESSION['pw'] = $resx['pass'];
	$_SESSION['noms'] = $resx['noms'];
	$_SESSION['email'] = $resx['email'];
	$_SESSION['pic'] = $resx['pic'] == null ? $img . 'clients/user.png' : $url . '/' . $resx['pic'];
	$_SESSION['pic_link'] = $resx['pic'];
  
	$_SESSION['img'] = $resx['pic'] == null ? true : false;
	$_SESSION['isClient'] = true;

}
