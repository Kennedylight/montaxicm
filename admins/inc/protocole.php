<?php
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $url = "https";
else $url = "http";
$url .= "://";

// Ajoutez l'hôte (nom de domaine, ip) à l'URL.

// Vérifier et traiter le domaine
$host = $_SERVER['HTTP_HOST'];
$hostname = explode(':', $host)[0]; // Retire le port s'il existe
$port = explode(':', $host)[1] ?? '';
$port = $port != '' ? ':' . $port : '';
$parts = explode('.', $hostname);
$isDashboard = true;

$subdomain = $parts[0];

$url_site = $url;
$url .= $host;
$url_site = implode('.', array_slice($parts, 1)) . $port;

$img = $url . "/assets/images/";
$css = $url . "/assets/css/";
$js = $url . "/assets/js/";
$aud = $url . "/assets/audio/";
$auth = $url . "/auth/";
// $fr = $url_site . "/";
// $en = $url_site . "/en/";
$inc = $_SERVER['DOCUMENT_ROOT'] . "/inc/";
$mails = $_SERVER['DOCUMENT_ROOT'] . "/assets/phpmailer/";

$version_app = "010001"; // Version de l'application : anti cache pour les ressources statiques (img, css, js)
