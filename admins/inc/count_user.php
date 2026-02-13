<?php

$iv4 = false;

// Détection IP
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    $ipv4 = $_SERVER['HTTP_X_FORWARDED_FOR'] == "::1" ? "127.0.0.1" : $_SERVER['HTTP_X_FORWARDED_FOR'];
else if (isset($_SERVER['HTTP_CLIENT_IP']))
    $ipv4 = $_SERVER['HTTP_CLIENT_IP'] == "::1" ? "127.0.0.1" : $_SERVER['HTTP_CLIENT_IP'];
else if (isset($_SERVER['HTTP_X_REAL_IP']))
    $ipv4 = $_SERVER['HTTP_X_REAL_IP'] == "::1" ? "127.0.0.1" : $_SERVER['HTTP_X_REAL_IP'];
else
    $ipv4 = $_SERVER['REMOTE_ADDR'] == "::1" ? "127.0.0.1" : $_SERVER['REMOTE_ADDR'];

if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false)
    $iv4 = inet_ntop($ipv4);

$userName = isset($_SESSION['noms']) ? $_SESSION['noms'] : 'Utilisateur inconnu';
$sip = $iv4 == false ? $ipv4 : $iv4;


// Gestion période hebdomadaire
$day = date('N');
$mois = date('m');
if ($day == 0) $day = 7;
$start_day = $day - 1;
$end_day = 7 - $day;
$start_date = date('Y-m-d', strtotime(' - ' . $start_day . ' days'));
$end_date = date('Y-m-d', strtotime(' + ' . $end_day . ' days'));

$search_periode = $bdd->prepare("SELECT COUNT(*) AS ca FROM div_periode WHERE start_week = ? AND end_week = ?");
$search_periode->execute(array($start_date, $end_date));
$periode_total = $search_periode->fetch();
$search_periode->closeCursor();

if ($periode_total['ca'] == 0) {
    $ins = $bdd->prepare('INSERT INTO div_periode(mois, start_week, end_week) VALUES(?, ?, ?)');
    $ins->execute(array($mois, $start_date, $end_date));
    $ins->closeCursor();
}

// Vérifier si l'adresse ip existe déjà pour la date du jour
$ip = $sip;
$ip = $ip == "127.0.0.1" ? "102.244.197.63" : $ip;

$search_ip = $bdd->prepare("SELECT * FROM div_visiteurs WHERE ip = ? AND `date` = ?");
$search_ip->execute(array($ip, date("Y-m-d")));
$tempUser = $search_ip->fetch();
$search_ip->closeCursor();

$country = '';
$ville = '';

$currency = '';
$countryCode = '';
$currencyRate = null;

if ($tempUser == false) { // Si non, on fait une requete a l'API seulement quand l'adresse ip est différente de localhost
    if ($ip == "127.0.0.1") {
        $country = 'Pays inconnu';
        $currency = 'CAD';
        $countryCode = 'CA';
        $ville = 'Region et ville inconnues';
        $currencyRate = 1;
    } else {
        $data = getIP($ip);

        $country = $data['countryName'] ?? 'Pays inconnu';
        $ville = $data['regionName'] . ', ' . $data['cityName'] ?? 'Region et ville inconnues';
        $currency = $data['currencies'][0] ?? 'CAD';
        $countryCode = $data['countryCode'] ?? 'CA';
    }

    $ins = $bdd->prepare("INSERT INTO div_visiteurs(ip, `date`, heure, jour, mois, `year`, `lang`, `pays`, `ville`, `nom`, `countryCode`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->execute(array($ip, date("Y-m-d"), date("H:i:s"), $jrs[date('N') - 1], $mois, date('Y'), $lang, $country, $ville, $userName, $countryCode));
} else {
    $country = $tempUser['pays'];
    $ville = $tempUser['ville'];
    $countryCode = $tempUser['countryCode'];

    $upd = $bdd->prepare("UPDATE div_visiteurs SET `date` = ?, heure = ?, lang = ?, `nom` = ? WHERE ip = ? AND `date` = ?");
    $upd->execute(array(date('Y-m-d'), date('H:i:s'), $lang, $userName, $ip, date('Y-m-d')));
}
