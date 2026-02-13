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

// Vérifier s'il y a un sous-domaine
if (count($parts) >= 3) { // Il y a un sous domaine !
  $subdomain = $parts[0]; // On le capture

  $url_site = $url;
  $url .= $host;
  $url_site = implode('.', array_slice($parts, 1)) . $port;

  $img = $url . "/assets/img/";
  $css = $url . "/assets/css/";
  $js = $url . "/assets/js/";
  $aud = $url . "/assets/audio/";
  $auth = $url . "/auth/";
  $fr = $url_site . "/";
  $en = $url_site . "/en/";
  $inc = $_SERVER['DOCUMENT_ROOT'] . "/inc/";
  $mails = $_SERVER['DOCUMENT_ROOT'] . "/assets/phpmailer/";
} else { // Dans le cas contraire, c'est le site normal pour les clients
  $url .= $host;
  $url_site = $url;
  $url_2 = $url . '/drivers/';
  $isDashboard = false;

  $img = $url . "/drivers/assets/img/";
  $css = $url . "/drivers/assets/css/";
  $js = $url . "/drivers/assets/js/";
  $aud = $url . "/drivers/assets/audio/";
  $auth = $_SERVER['DOCUMENT_ROOT'] . "/drivers/auth/";
  $fr = $url . "/";
  $en = $url . "/en/";
  $panel = $url . "/panel/";
  $inc = $_SERVER['DOCUMENT_ROOT'] . "/drivers/inc/";
  $mails = $_SERVER['DOCUMENT_ROOT'] . "/drivers/assets/phpmailer/";
}

$version_app = "010001"; // Version de l'application : anti cache pour les ressources statiques (img, css, js)
