<?php

include('drivers/inc/main.php');

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Taxi CM • Reservez un moyen de transport rapidement et efficacement partout au Cameroun.</title>
  <meta name="description" content="Mon Taxi CM est une plateforme de réservation de taxi en ligne qui vous permet de réserver un moyen de transport rapidement et efficacement partout au Cameroun. Avec Mon Taxi CM, vous pouvez facilement trouver un taxi à proximité, réserver votre trajet en quelques clics et profiter d'un service de qualité pour vos déplacements quotidiens ou occasionnels. Que ce soit pour aller au travail, faire du shopping ou visiter des amis, Mon Taxi CM est là pour vous offrir une expérience de transport pratique et fiable. Réservez dès maintenant et découvrez la commodité de voyager avec Mon Taxi CM !">

  <link rel="stylesheet" href="<?= $css ?>all.css">
  <link rel="stylesheet" href="<?= $css ?>polices.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="<?= $css ?>style.css">
</head>

<body>
  <main>
    <div class="phones">
      <div class="phoneHead">
        <div class="heure"></div>

        <div class="tools">
          <span class="mio">network_cell</span>
          <span class="mio">network_wifi</span>
          <span class="mio specialBattery">battery_5_bar</span>
        </div>
      </div>
      <div class="contents">
        <header>
          <h1>Mon Taxi CM</h1>
          <span class="mio">search</span>
        </header>
        <div id="cartMap"></div>
      </div>

      <div class="phoneFoot">
        <span class="homebar"></span>
      </div>
    </div>
  </main>

  <script src="<?= $js ?>functions.js"></script>
  <script src="<?= $js ?>all.js"></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="<?= $js ?>script.js"></script>
</body>

</html>