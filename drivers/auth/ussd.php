<?php
header('Content-Type: application/json');
$text = $_POST['text'] ?? '';
$step = $_POST['step'] ?? 'start';
$res = ["message" => "", "nextStep" => "", "close" => false];

if ($step == "emergency") {
  $res["message"] = "🚨 ALERTE SOS ENVOYÉE 🚨<br>Votre position a été transmise aux autorités et aux chauffeurs proches.";
  $res["close"] = true;
} else {
  switch ($step) {
    case 'start':
      $res["message"] = "Mon Taxi CM (CLIENT)<br>1. Commander un taxi<br>2. Mon Solde<br>3. Promo";
      $res["nextStep"] = "main_menu";
      break;
    case 'main_menu':
      if ($text == "1") {
        $res["message"] = "Où êtes-vous ? (Entrez le quartier)";
        $res["nextStep"] = "get_pickup";
      } else {
        $res["message"] = "Solde : 5 400 FCFA";
        $res["close"] = true;
      }
      break;
    case 'get_pickup':
      $res["message"] = "Destination ? (Entrez le quartier)";
      $res["nextStep"] = "get_dest";
      break;
    case 'get_dest':
      $res["message"] = "Paiement :<br>1. Cash<br>2. Mobile Money";
      $res["nextStep"] = "payment";
      break;
    case 'payment':
      $msg = ($text == "2") ? "Validation MoMo en cours..." : "Course confirmée (Cash).";
      $res["message"] = "$msg<br>Un chauffeur est en route !";
      $res["close"] = true;
      break;
  }
}
echo json_encode($res);
