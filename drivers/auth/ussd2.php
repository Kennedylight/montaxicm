<?php
header('Content-Type: application/json');
$text = $_POST['text'] ?? '';
$step = $_POST['step'] ?? 'start';
$res = ["message" => "", "nextStep" => "", "close" => false];

switch ($step) {
  case 'start':
    $res["message"] = "ESPACE CHAUFFEUR<br>1. Voir les courses (3)<br>2. Mon chiffre du jour<br>3. Passer Hors-ligne";
    $res["nextStep"] = "driver_menu";
    break;
  case 'driver_menu':
    if ($text == "1") {
      $res["message"] = "Courses dispo :<br>1. Bastos -> Akwa (2500F)<br>2. Bonanjo -> Aéroport (5000F)";
      $res["nextStep"] = "accept_course";
    } elseif ($text == "2") {
      $res["message"] = "Total aujourd'hui : 18 500 FCFA";
      $res["close"] = true;
    }
    break;
  case 'accept_course':
    $res["message"] = "Course acceptée !<br>L'itinéraire a été envoyé sur votre application.";
    $res["close"] = true;
    break;
}
echo json_encode($res);
