<?php
date_default_timezone_set('Africa/Douala');
// Lancement de la session pour tout le monde
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

# inclure les liens utiles
$lang = 'fr';
include('protocole.php');
include('functions.php');

# Données nécessaires dans la récupération des informations
$dt = date("Y-m-d H:i:s");
$dd = date("Y-m-d");
$time = date('H:i:s');

# Calcul des timers : pour les notifications de 10 minutes avant et après, et 5 minutes avant et après
$timer = date('Y-m-d H:i:s', strtotime($dt . ' + 10 mins'));
$timer2 = date('Y-m-d H:i:s', strtotime($dt . ' - 10 mins'));

# Calcul des timers : pour les notifications de 5 minutes avant et après
$timer5min = date('Y-m-d H:i:s', strtotime($dt . ' + 5 mins'));
$timer5min2 = date('Y-m-d H:i:s', strtotime($dt . ' - 5 mins'));

# Listes des jours et mois
$jrs = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
$jrs_en = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
$months = ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"];
$months_en = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];
$mths = ["janv.", "fév.", "mars", "avril", "mai", "juin", 'juil.', "août", "sept.", "oct.", "nov.", "déc."];

# Connexion à la base de données
$user = "root";
$host = "localhost";
$bd = "montaxi";
$p = "RooT";
try {
    $bdd = new PDO("mysql:host=$host;dbname=$bd;charset=utf8mb4", $user, $p);
} catch (Exception $e) {
    die("Erreur de connexion :" . $e->getMessage());
}

# Inclusion count_user.php uniquement pour le site client
if (!$isDashboard) include_once('count_user.php');

$currency = "FCFA";
