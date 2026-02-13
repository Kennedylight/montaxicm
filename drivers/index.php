<?php

session_start();

include('inc/main.php');
include('inc/verif_connected.php');

include('inc/login.php');
$branch = 1;
include('inc/functions.php');

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="description" content="<?= $_SESSION['website_name'] ?> est la plateforme de gestion de ses activités incluant la vente, la livraison ou les commandes. Avec <?= $_SESSION['website_name'] ?>, gérer votre stocks, vos commandes ainsi que vos entrées et sorties efficacement.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $_SESSION['website_name'] ?> • Tableau de bord d'administration.</title>

  <!-- Important : informations sur le site web -->
  <meta property="og:titre" content="<?= $_SESSION['website_name'] ?> • Tableau de bord d'administration.">
  <meta property="og:description" content="<?= $_SESSION['website_name'] ?> est la plateforme de gestion de ses activités incluant la vente, la livraison ou les commandes. Avec <?= $_SESSION['website_name'] ?>, gérer votre stocks, vos commandes ainsi que vos entrées et sorties efficacement.">
  <meta property="og:url" content="<?= $url ?>">
  <meta property="og:image" content="<?= $img ?>logo.png">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= $_SESSION['website_name'] ?>">

  <!-- Inclusion et importation CSS et Javascript -->
  <link rel="stylesheet" href="<?= $css ?>polices.css?v=<?= $version_app ?>">
  <link rel="stylesheet" href="<?= $css ?>all.css?v=<?= $version_app ?>">
  <link rel="stylesheet" href="<?= $css ?>errorOrSuccessBox.css?v=<?= $version_app ?>">
  <link rel="stylesheet" href="<?= $css ?>style.css?v=<?= $version_app ?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?= $img ?>fav.png">

  <!-- Le router -->
  <script src="<?= $js . 'router.js' ?>?v=<?= $version_app ?>"></script>
  <script src="<?= $js . 'ajax.js' ?>?v=<?= $version_app ?>"></script>
  <link rel="stylesheet" href="<?= $css ?>lightbox.min.css?v=<?= $version_app ?>">

  <!-- Les autres scripts -->
  <script src="<?= $js ?>tinymce/tinymce.min.js?v=<?= $version_app ?>"></script>
  <script src="<?= $js ?>chart.js?v=<?= $version_app ?>"></script>
  <script>
    localStorage.removeItem('continue');
  </script>

  <!-- Relative à l'application web -->
  <link rel="manifest" href="<?= $js ?>manifest.json?v=<?= $version_app ?>">
  <script>
    var auths = <?= json_encode($_SESSION['auths']); ?>;
    const roleUser = <?= $_SESSION['role'] ?>;
    window.addEventListener('load', () => {
      if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register('/sw.js?v=<?= $version_app ?>');
      }
    })
  </script>
</head>

<body>
  <!-- Le loader -->
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/inc/loadder.php'); ?>
  <!-- Player de notification -->
  <audio src="" class="player"></audio>
  <main>
    <div>
      <div class="mainContainers">
        <div class="containers">
          <div class="menu active">
            <div>
              <h2>
                <div>
                  <img src="<?= $img ?>fav.png" class="logoreduit" alt="Logo <?= $_SESSION['website_name'] ?>">
                  <img src="<?= $img ?>logo.png" class="logoAgrandi" alt="Logo <?= $_SESSION['website_name'] ?>">
                </div>
                <span class="mio toggleMenu" title="Dérouler/Reduire le menu">keyboard_arrow_right</span>
              </h2> <br>
              <ul>
                <li class="active" title="Vue d'ensemble">
                  <div><span class="mio">home_max</span><span class="textHidden">Accueil</span></div>
                </li>
                <li title="Les statistiques" class="auth-spec">
                  <div><span class="mio">trending_up</span><span class="textHidden">Statistiques</span></div>
                </li>
                <li title="Voir toutes les commandes" class="auth-9">
                  <div><span class="mio">local_grocery_store</span><span class="textHidden">Commandes</span></div>
                </li>
                <li title="Gérer votre stock" class="auth-15">
                  <div><span class="mio">local_mall</span><span class="textHidden">Gérer le stock</span></div>
                </li>
                <li title="Consulter la liste de vos clients" class="auth-spec2">
                  <div><span class="mio">people</span><span class="textHidden">Gérer les clients</span></div>
                </li>
                <li title="Consulter la liste des administrateurs">
                  <div><span class="mio">admin_panel_settings</span><span class="textHidden">Gérer les utilisateurs</span></div>
                </li>
                <li title="Voir les avis des clients" class="auth-18">
                  <div><span class="mio">star_outline</span><span class="textHidden">Avis des clients</span></div>
                </li>
                <li title="Voir les demandes de remboursements et litiges" class="auth-11">
                  <div><span class="mio">assignment_late</span><span class="textHidden">Remboursements et litiges</span></div>
                </li>
              </ul>
            </div>
            <ul>
              <li title="Accéder aux paramètres">
                <div><span class="mio">settings</span><span class="textHidden">Paramètres</span></div>
              </li>
              <li class="textHidden" title="Se déconnecter de votre compte">
                <div> <span class="mio">logout</span> Déconnexion</div>
              </li>
            </ul>
          </div>
          <div class="parts">
            <div class="header">
              <form class="rechercheForm visible">
                <h2 class="lighterGreeting">
                  <?php
                  if (date('H') >= 5 && date('H') <= 11) echo '<span class="mio">light_mode</span> Bonjour 👋.';
                  else if (date('H') >= 12 && date('H') <= 17) echo '<span class="mio">beach_access</span> Bon après midi.';
                  else echo '<span class="mio">nights_stay</span> Bonsoir 🫡';
                  ?>
                </h2>
              </form>
              <div class="logoTextuel"><span><?= $_SESSION['website_name'] ?></span></div>
              <div>
                <!-- <span class="mio searchIco">search</span> -->
                <div class="iconsNotifications">
                  <span class="mio notificationsIco">notifications</span>
                  <span class="pastille"></span>
                </div>
                <div class="forProfile">
                  <img class="profile_pic noClick" src="<?= $_SESSION['pic'] ?>" alt="Profile pic user">
                  <div>
                    <span class="surnameAlt">
                      <?= $_SESSION['prenom'] ?>
                    </span><br>
                    <span><?= $_SESSION['role'] == 0 ? "Propriétaire" : "Administrateur" ?></span>
                  </div>
                </div>
              </div>
              <div class="profileBox">
                <div>
                  <p class="info email emailAlt" title="<?= $_SESSION['email'] ?>"><?= $_SESSION['email'] ?></p>
                  <figure>
                    <input type="file" accept=".png, .jpg, .jpeg, .webn" hidden>
                    <a href="<?= $_SESSION['pic'] ?>" data-lightbox="profile_pic" data-title="Votre photo de profil" class="profile_pic">
                      <img class="profile_pic noClick" src="<?= $_SESSION['pic'] ?>" alt="Profile pic user">
                    </a>
                    <p class="info names namesAlt" title="<?= $_SESSION['names'] ?>"><?= $_SESSION['names'] ?></p>
                    <figcaption><span class="mio">edit</span>Modifier la photo</figcaption>
                  </figure>
                  <ul>
                    <li><span class="mio">visibility</span>Mon profil</li>
                    <li><span class="mio">settings</span>Paramètres</li>
                    <li><span class="mio">web</span>Aller au site de vente</li>
                    <li><span class="mio">logout</span>Se déconnecter de mon compte</li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="allContents">
              <div class="gestDashboard">
                <!-- Section Vue d'ensemble -->
                <section id="dashboardOverview" class="content-section">
                  <div class="dashboard-container">

                    <!-- Header -->
                    <div class="dashboard-header">
                      <div>
                        <h2>Vue d'ensemble</h2>
                        <p class="dashboard-subtitle">Vue d'ensemble de votre activité</p>
                      </div>
                      <div class="dashboard-date">
                        <span class="mio">calendar_today</span>
                        <span id="dashboardDate"></span>
                      </div>
                    </div>

                    <!-- Stats rapides -->
                    <div class="quick-stats" id="quickStats">
                      <!-- Rempli dynamiquement -->
                    </div>

                    <!-- Grille de widgets -->
                    <div class="dashboard-widgets" id="dashboardWidgets">
                      <!-- Rempli dynamiquement selon autorisations -->
                    </div>

                  </div>
                </section>
              </div>
              <div class="active statsManager auth-spec">
                <!-- Section Statistiques -->
                <section id="statsManager" class="content-section">
                  <div class="stats-container">

                    <!-- Header -->
                    <div class="section-header">
                      <div class="header-left">
                        <h2>Statistiques</h2>
                      </div>
                      <div class="header-actions"></div>
                    </div>

                    <!-- Onglets -->
                    <div class="stats-tabs">
                      <div class="tab-btn active" data-tab="ventes" id="tabVentes" style="display: none;">
                        <span class="mio">shopping_cart</span> Les ventes
                      </div>
                      <div class="tab-btn" data-tab="visites" id="tabVisites" style="display: none;">
                        <span class="mio">visibility</span> Les visites
                      </div>
                    </div>

                    <div class="stats-content" id="statsVentes" style="display: none;">

                      <!-- Chiffres clés -->
                      <p class="info">Vos statistiques de vente inclus les <span class="ssec">ventes totales, les revenus générés</span> ainsi que plusieurs autres statistiques. Cliquez sur une statistique pour avoir plus de détails.</p><br>
                      <div class="quick-stats">
                        <!-- Ventes totales -->
                        <div class="dashQuickStats main" onclick="showVentesDetails('total')">
                          <div class='head'>
                            <span class="title bold">Ventes totales</span>
                            <a class="mio" onclick="showVentesDetails('total')" title="Gérer vos commandes">arrow_outward</a>
                          </div>
                          <div class="number" id="ventesTotalMontant">
                            0
                          </div>
                          <div class='inf'>Avec <span id="ventesTotalNb">0</span> vente(s) réalisée(s)</div>
                          <span class="mio">shopping_bag</span>
                        </div>

                        <!-- Ventes 7 derniers jours -->
                        <div class="dashQuickStats" onclick="showVentesDetails('7days')">
                          <div class='head'>
                            <span class="title bold">Les 7 derniers jours</span>
                            <a class="mio" onclick="showVentesDetails('7days')" title="Gérer vos commandes">arrow_outward</a>
                          </div>
                          <div class="number" id="ventes7daysMontant">
                            0
                          </div>
                          <div class='inf'>Avec <span id="ventes7daysNb">0</span> vente(s) réalisée(s)</div>
                          <span class="mio">date_range</span>
                        </div>

                        <!-- Revenus aujourd'hui -->
                        <div class="dashQuickStats" onclick="showVentesDetails('today')">
                          <div class='head'>
                            <span class="title bold">Revenus d'aujourd'hui</span>
                            <a class="mio" onclick="showVentesDetails('today')" title="Gérer vos commandes">arrow_outward</a>
                          </div>
                          <div class="number" id="revenusToday">
                            0
                          </div>
                          <div class='inf'></div>
                          <span class="mio">attach_money</span>
                        </div>

                        <!-- Panier moyen -->
                        <div class="dashQuickStats">
                          <div class='head'>
                            <span class="title bold">Panier moyen</span>
                          </div>
                          <div class="number" id="panierMoyen">
                            0
                          </div>
                          <div class='inf'></div>
                          <span class="mio">shopping_basket</span>
                        </div>

                        <!-- Commandes en attente -->
                        <div class="dashQuickStats" onclick="showVentesDetails('pending')">
                          <div class='head'>
                            <span class="title bold">Ventes en attente</span>
                            <a class="mio" onclick="showVentesDetails('pending')" title="Voir données sur les ventes">arrow_outward</a>
                          </div>
                          <div class="number" id="commandesAttente">
                            0
                          </div>
                          <div class='inf'></div>
                          <span class="mio">pending</span>
                        </div>
                      </div>

                      <!-- Graphiques -->
                      <div class="charts-grid">
                        <!-- Commandes réussies vs échouées -->
                        <div class="chart-card">
                          <h3><span class="mio">pie_chart</span> Commandes réussies vs échouées</h3>
                          <canvas id="chartSuccessFailure"></canvas>
                        </div>

                        <!-- Top 5 produits -->
                        <div class="chart-card">
                          <h3><span class="mio">bar_chart</span> Top 5 produits vendus</h3>
                          <canvas id="chartTopProducts"></canvas>
                        </div>
                      </div>

                      <!-- Recherche historique -->
                      <div class="search-history">
                        <h3>Recherche historique</h3>
                        <form novalidate class="history-form" onsubmit="searchVentesHistory(event)">
                          <div>
                            <label for="ventesDateDebut">Date de début</label>
                            <div class="entry">
                              <input type="date" id="ventesDateDebut" min="2025-12-01" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                          </div>

                          <div>
                            <label for="ventesDateFin">Date de fin</label>
                            <div class="entry">
                              <input type="date" id="ventesDateFin" min="2025-12-01" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                          </div>

                          <button class="btn-search next">
                            <span class="mio">search</span> Rechercher
                          </button>
                        </form>

                        <div id="ventesHistoryResults" style="display: none;">
                          <h4>Résultats de la recherche</h4>
                          <div class="history-stats">
                            <div class="history-stat-item">
                              <span class="label">Nombre de commandes :</span>
                              <span class="value" id="historyVentesNb">0</span>
                            </div>
                            <div class="history-stat-item">
                              <span class="label">Montant total :</span>
                              <span class="value" id="historyVentesMontant">0 CAD</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>


                    <div class="stats-content" id="statsVisites" style="display: none;">

                      <!-- Chiffres clés -->
                      <p class="info">Vos statitisques de visites inclus les <span class="ssec">personnes en lignes actuellement, divers traffics périodiques</span> ainsi que plusieurs autres statistiques. Cliquez sur une statistique pour avoir plus de détails.</p><br>
                      <div class="quick-stats">
                        <!-- En ligne -->
                        <div class="dashQuickStats main" onclick="showVisitesDetails('online')">
                          <div class='head'>
                            <span class="title bold">En ligne</span>
                            <a class="mio" onclick="showVisitesDetails('online')" title="Voir les personnes connectées">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesOnline">
                            0
                          </div>
                          <div class='inf'>Personnes actuellement sur le site.</div>
                          <span class="mio">wifi</span>
                        </div>

                        <!-- Aujourd'hui -->
                        <div class="dashQuickStats" onclick="showVisitesDetails('today')">
                          <div class='head'>
                            <span class="title bold">Aujourd'hui</span>
                            <a class="mio" onclick="showVisitesDetails('today')" title="Voir les données de ceux qui ont accéder au site aujourd'hui">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesToday">
                            0
                          </div>
                          <div class='inf'>Personnes qui ont visité le site aujourd'hui.</div>
                          <span class="mio">today</span>
                        </div>

                        <!-- Cette semaine -->
                        <div class="dashQuickStats" onclick="showVisitesDetails('week')">
                          <div class='head'>
                            <span class="title bold">Cette semaine</span>
                            <a class="mio" onclick="showVisitesDetails('week')" title="Voir les données de ceux qui ont accéder au site cette semaine">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesWeek">
                            0
                          </div>
                          <div class='inf'>Visite(s).</div>
                          <span class="mio">date_range</span>
                        </div>

                        <!-- Semaine dernière -->
                        <div class="dashQuickStats" onclick="showVisitesDetails('lastweek')">
                          <div class='head'>
                            <span class="title bold">La semaine dernière</span>
                            <a class="mio" onclick="showVisitesDetails('lastweek')" title="Voir les données de ceux qui ont accéder au site la semaine passée">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesLastWeek">
                            0
                          </div>
                          <div class='inf'>Visite(s).</div>
                          <span class="mio">history</span>
                        </div>

                        <!-- Ce mois -->
                        <div class="dashQuickStats" onclick="showVisitesDetails('month')">
                          <div class='head'>
                            <span class="title bold">Ce mois</span>
                            <a class="mio" onclick="showVisitesDetails('month')" title="Voir les données de ceux qui ont accéder au site ce mois">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesMonth">
                            0
                          </div>
                          <div class='inf'>Visite(s).</div>
                          <span class="mio">calendar_today</span>
                        </div>

                        <!-- Mois dernier -->
                        <div class="dashQuickStats" onclick="showVisitesDetails('lastmonth')">
                          <div class='head'>
                            <span class="title bold">Le mois dernier</span>
                            <a class="mio" onclick="showVisitesDetails('lastmonth')" title="Voir les données de ceux qui ont accéder au site le mois passé">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesLastMonth">
                            0
                          </div>
                          <div class='inf'>Visite(s).</div>
                          <span class="mio">event_note</span>
                        </div>

                        <!-- Cette année -->
                        <div class="dashQuickStats" onclick="showVisitesDetails('year')">
                          <div class='head'>
                            <span class="title bold">Cette année</span>
                            <a class="mio" onclick="showVisitesDetails('year')" title="Voir les données de ceux qui ont accéder au site cette année">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesYear">
                            0
                          </div>
                          <div class='inf'>Visite(s).</div>
                          <span class="mio">calendar_month</span>
                        </div>

                        <!-- Année dernière (conditionnel) -->
                        <div class="dashQuickStats" id="visitesLastYearCard" style="display: none;" onclick="showVisitesDetails('lastyear')">
                          <div class='head'>
                            <span class="title bold">L'année dernière</span>
                            <a class="mio" onclick="showVisitesDetails('lastyear')" title="Voir les données de ceux qui ont accéder au site l'année précédente">arrow_outward</a>
                          </div>
                          <div class="number" id="visitesLastYear">
                            0
                          </div>
                          <div class='inf'>Visite(s).</div>
                          <span class="mio">event</span>
                        </div>
                      </div>

                      <!-- Graphique comparaison semaines -->
                      <div class="charts-grid single">
                        <div class="chart-card">
                          <h3><span class="mio">show_chart</span> Visites : Semaine actuelle vs Semaine précédente</h3>
                          <canvas id="chartWeeksComparison"></canvas>
                        </div>
                      </div>

                      <!-- Recherche historique -->
                      <div class="search-history">
                        <h3>Recherche historique</h3>
                        <form class="history-form" onsubmit="searchVisitesHistory(event)">
                          <div>
                            <label for="visitesDateDebut">Date de début</label>
                            <div class="entry">
                              <input type="date" id="visitesDateDebut" min="2025-12-01" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                          </div>

                          <div>
                            <label for="visitesDateFin">Date de fin</label>
                            <div class="entry">
                              <input type="date" id="visitesDateFin" min="2025-12-01" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                          </div>

                          <button class="btn-search next">
                            <span class="mio">search</span> Rechercher
                          </button>
                        </form>

                        <div id="visitesHistoryResults" style="display: none;">
                          <h4>Résultats de la recherche</h4>
                          <div class="history-stats">
                            <div class="history-stat-item">
                              <span class="label">Nombre de visites :</span>
                              <span class="value" id="historyVisitesNb">0</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </section>

                <!-- Modal Détails Ventes -->
                <div class="modal-overlay" id="modalDetailsVentes" style="display: none;">
                  <div class="modal-content modal-details">
                    <div class="modal-header">
                      <h2><span class="mio">receipt_long</span> <span id="modalVentesTitre"></span></h2>
                      <span class="mio close-modal" onclick="closeDetailsVentesModal()">close</span>
                    </div>
                    <div class="modal-body" id="modalVentesContent">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </div>

                <!-- Modal Détails Visites -->
                <div class="modal-overlay" id="modalDetailsVisites" style="display: none;">
                  <div class="modal-content modal-details">
                    <div class="modal-header">
                      <h2><span class="mio">people</span> <span id="modalVisitesTitre"></span></h2>
                      <span class="mio close-modal" onclick="closeDetailsVisitesModal()">close</span>
                    </div>
                    <div class="modal-body" id="modalVisitesContent">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </div> <!-- Fin ici -->
              </div>
              <div class="active auth-9 gestCommands">
                <!-- Section Gestion des commandes -->
                <section id="ordersManager" class="content-section">
                  <div class="orders-container">

                    <!-- Header -->
                    <div class="section-header">
                      <div class="header-left">
                        <h2>Gestion des commandes</h2>
                      </div>
                      <div class="header-actions">
                        <span class="mio searchCommands btn-secondary">search</span>
                      </div>
                    </div>

                    <!-- Onglets principaux -->
                    <div class="orders-tabs">
                      <div class="tab-btn active" data-tab="encours">
                        <span class="mio">pending_actions</span> En attente / En cours
                      </div>
                      <div class="tab-btn" data-tab="reussies">
                        <span class="mio">check_circle</span> Réussies
                      </div>
                      <div class="tab-btn" data-tab="annulees">
                        <span class="mio">cancel</span> Annulées / Échouées
                      </div>
                    </div>

                    <!-- Sous-onglets (En attente uniquement) -->
                    <div class="orders-subtabs" id="subTabsEncours">
                      <div class="subtab-btn active" data-subtab="livraison">
                        <span class="mio">local_shipping</span> À livrer
                      </div>
                      <div class="subtab-btn" data-subtab="recuperation">
                        <span class="mio">store</span> À récupérer
                      </div>
                    </div>

                    <!-- Filtres -->
                    <div class="filters-bar popups active">
                      <form onsubmit="filterOrders(event)">
                        <h3>
                          Filtrer les résultats <span class="mio">close</span>
                        </h3>
                        <div class="filter-item search-filter search-box">
                          <label>Rechercher</label>
                          <div class="entry">
                            <input type="text" id="orderSearch" placeholder="N° commande, nom, prénom ou email...">
                            <span class="mio icons">search</span>
                          </div>
                        </div>
                        <div class="filter-group">
                          <div class="filter-item">
                            <label>Date début</label>
                            <div class="entry">
                              <input type="date" id="orderDateDebut">
                            </div>
                          </div>

                          <div class="filter-item">
                            <label>Date fin</label>
                            <div class="entry">
                              <input type="date" id="orderDateFin">
                            </div>
                          </div>

                          <div class="filter-item">
                            <label>Montant</label>
                            <div class="entry">
                              <select id="orderMontantFilter">
                                <option value="">Tous</option>
                                <option value="asc">Croissant (↑)</option>
                                <option value="desc">Décroissant (↓)</option>
                                <option value="custom">Personnalisé</option>
                              </select>
                            </div>
                          </div>
                          <div class="filter-item custom-montant" id="customMontantInputs" style="display: none;">
                            <label>Min - Max</label>
                            <div class="montant-range entry">
                              <input type="number" id="montantMin" placeholder="Min" step="0.01">
                              <span>—</span>
                              <input type="number" id="montantMax" placeholder="Max" step="0.01">
                            </div>
                          </div>

                          <div class="filter-actions btns">
                            <button class="btn-reset-filters prev" onclick="resetOrdersFilters()">
                              <span class="mio">refresh</span> Réinitialiser
                            </button>
                            <button class="btn-search-filter next">
                              <span class="mio">search</span> Rechercher
                            </button>
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Liste des commandes -->
                    <div class="orders-list" id="ordersList">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </section>

                <!-- Modal Détails Commande -->
                <div class="modal-overlay" id="modalOrderDetails" style="display: none;">
                  <div class="modal-content modal-order-details">
                    <div class="modal-header">
                      <h2><span class="mio">receipt_long</span> Détails de la commande</h2>
                      <span class="mio close-modal" onclick="closeOrderDetailsModal()">close</span>
                    </div>
                    <div class="modal-body" id="orderDetailsContent">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </div>

                <!-- Modal Annulation Admin -->
                <div class="modal-overlay" id="modalCancelOrder" style="display: none;">
                  <div class="modal-content modal-cancel-order">
                    <div class="modal-header">
                      <h2><span class="mio">cancel</span> Annuler la commande</h2>
                      <span class="mio close-modal" onclick="closeCancelOrderModal()">close</span>
                    </div>
                    <div class="modal-body">
                      <p class="warning-text">
                        <span class="mio">warning</span>
                        Cette action va annuler définitivement la commande et notifier le client par email. Un remboursement devra être effectué.
                      </p>

                      <form novalidate id="formCancelOrder">
                        <input type="hidden" id="cancel_order_id">

                        <div>
                          <label for="cancel_motif">Motif d'annulation <span class="ppri">*</span></label>
                          <div class="entry">
                            <textarea id="cancel_motif" rows="4" required placeholder="Expliquez la raison de l'annulation..."></textarea>
                          </div>
                        </div><br>

                        <div>
                          <label for="cancel_password">Votre mot de passe <span class="ppri">*</span></label>
                          <div class="entry">
                            <span class="mio icons">lock</span>
                            <input type="password" id="cancel_password" required>
                            <span class="mio icons eyes">visibility</span>
                          </div>
                        </div>

                        <div class="btns">
                          <span class="prev" onclick="closeCancelOrderModal()"><span class="mio">close</span> Annuler</span>
                          <button type="submit" class="next"><span class="mio">cancel</span> Confirmer l'annulation</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Modal Marquer comme Livrée/Récupérée -->
                <div class="modal-overlay" id="modalMarkDelivered" style="display: none;">
                  <div class="modal-content modal-mark-delivered">
                    <div class="modal-header">
                      <h2><span class="mio">check_circle</span> <span id="markDeliveredTitle">Marquer comme livrée</span></h2>
                      <span class="mio close-modal" onclick="closeMarkDeliveredModal()">close</span>
                    </div>
                    <div class="modal-body">
                      <p class="info-text" id="markDeliveredInfo">
                        Veuillez fournir une preuve de livraison (photo et/ou document signé). Ces documents seront envoyés au client par email.
                      </p><br>

                      <form novalidate id="formMarkDelivered">
                        <div>
                          <input type="hidden" id="mark_order_id">
                          <label for="delivery_photo">Photo de livraison</label>
                          <div class="dropzone livrCom1">
                            <span class="mio">upload</span>
                            <img src="" style="display: none;">
                            <p>Cliquez pour importer ou glisser/déposer l'image preuve de livraison.</p>
                            <input type="file" accept=".png, .gif, .jpg, .jpeg, .webp" hidden id="delivery_photo">
                          </div>
                          <small class="info">Formats acceptés : JPG, JPEG, GIF, PNG, WEBP (max 2 MB)</small> <br>
                        </div>
                        <div>
                          <input type="hidden" id="mark_action_type">
                          <div class="dropzone livrCom2">
                            <span class="mio">upload</span>
                            <img src="" style="display: none;">
                            <p>Cliquez pour importer ou glisser/déposer l'image du document de livraison signé</p>
                            <input type="file" accept=".png, .gif, .jpg, .jpeg, .webp" hidden id="delivery_document">
                          </div>
                          <small class="info">Formats acceptés : JPG, JPEG, GIF, PNG, WEBP (max 2 MB)</small>
                        </div>
                        <div class="btns">
                          <span class="prev" onclick="closeMarkDeliveredModal()"><span class="mio">close</span> Annuler</span>
                          <button type="submit" class="next"><span class="mio">check_circle</span> Confirmer</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="active content-section gestStocksManager auth-15" id="stockManager">
                <div class="section-header">
                  <div class="header-left">
                    <h2>Gestion de Stock</h2>
                  </div>
                  <div class="header-actions">
                    <span title="exporter les données" class="btn-secondary mio" onclick="exportStock()">download</span>
                    <span title="Rechercher dans le stock" class="btn-secondary mio" onclick="showFilters()">search</span>
                  </div>
                </div>

                <!-- Filtres et Recherche -->
                <div class="filters-bar popups">
                  <form novalidate>
                    <h3>Filtrer les résultats <span class="mio">close</span></h3>
                    <div class="search-box">
                      <div class="entry">
                        <input type="search" id="searchStock" placeholder="Rechercher un produit...">
                        <span class="mio icons">search</span>
                      </div>
                    </div>

                    <div class="filter-group">
                      <div class="filter-item">
                        <label>Catégorie</label>
                        <div class="entry">
                          <select id="filterCategory">
                            <option value="">Toutes les catégories</option>
                          </select>
                        </div>
                      </div>

                      <div class="filter-item">
                        <label>Statut</label>
                        <div class="entry">
                          <select id="filterStatus">
                            <option value="">Tous les statuts</option>
                            <option value="0">Publié</option>
                            <option value="1">Non publié</option>
                          </select>
                        </div>
                      </div>

                      <div class="filter-item">
                        <label>Stock</label>
                        <div class="entry">
                          <select id="filterStock">
                            <option value="">Tous</option>
                            <option value="low">Stock faible</option>
                            <option value="out">Rupture</option>
                          </select>
                        </div>
                      </div>

                      <div class="filter-item">
                        <label>Trier par</label>
                        <div class="entry">
                          <select id="sortBy">
                            <option value="nom_asc">Nom (A-Z)</option>
                            <option value="nom_desc">Nom (Z-A)</option>
                            <option value="date_desc">Plus récents</option>
                            <option value="date_asc">Plus anciens</option>
                            <option value="qte_asc">Stock croissant</option>
                            <option value="qte_desc">Stock décroissant</option>
                          </select>
                        </div>
                      </div>

                      <div class="btns">
                        <button class="btn-icon prev" onclick="resetFilters()" title="Réinitialiser les filtres">
                          <span class="mio">filter_alt_off</span> Réinitialiser le filtre
                        </button>
                        <button class="next">Valider</button>
                      </div>
                    </div>
                  </form>
                </div>

                <!-- Actions en masse -->
                <div class="bulk-actions" id="bulkActions" style="display: none;">
                  <div class="bulk-info">
                    <span id="selectedCount">0</span> produit(s) sélectionné(s)
                  </div>
                  <div class="bulk-buttons">
                    <div class="btn-icon" onclick="bulkPublish()" title="Publier">
                      <span class="mio">cloud</span>
                    </div>
                    <div class="btn-icon" onclick="bulkUnpublish()" title="Dépublier">
                      <span class="mio">cloud_off</span>
                    </div>
                    <div class="btn-icon btn-danger" onclick="bulkDelete()" title="Supprimer">
                      <span class="mio">delete</span>
                    </div>
                    <div class="btn-icon" onclick="deselectAll()" title="Désélectionner tout">
                      <span class="mio">close</span>
                    </div>
                  </div>
                </div>

                <!-- Liste des produits -->
                <div class="products-grid" id="productsGrid">
                  <!-- Chargement initial -->
                  <div class="loading-placeholder" style="display: none;">
                  </div>
                </div>

                <!-- Pagination -->
                <div class="pagination" id="paginationStock" style="display: none;">
                  <div class="btn-icon" id="prevPage" onclick="location.href = '#/stocks/page/' + (currentPage - 1)">
                    <span class="mio">navigate_before</span>
                  </div>
                  <div class="page-selector">
                    <form novalidate>
                      <label class="page-label">Page <span id="currentPageLabel">1</span> sur <span id="totalPagesLabel">1</span></label>
                      <div class="entry">
                        <select id="pageSelect" onchange="location.href = '#/stocks/page/' + this.value"></select>
                      </div>
                    </form>
                  </div>
                  <div class="btn-icon" id="nextPage" onclick="location.href = '#/stocks/page/' + (currentPage + 1)">
                    <span class="mio">navigate_next</span>
                  </div>
                </div>
                <div class="modal-overlay" id="modalProductDetails" style="display: none;">
                  <div class="modal modal-large">
                    <div class="modal-header">
                      <h3><span class="mio">info</span> Détails du Produit</h3>
                      <span class="mio close" onclick="closeProductDetails()">close</span>
                    </div>

                    <div class="modal-body">
                      <div class="product-details-grid">
                        <!-- Image principale -->
                        <div class="product-image-section">
                          <a href="" data-lightbox="" data-title="">
                            <img id="detailImg" src="" alt="Produit" onerror="this.src='<?= $img ?>no-image.png'">
                          </a>
                          <div class="product-badges" id="detailBadges"></div>
                        </div>

                        <!-- Informations -->
                        <div class="product-info-section">
                          <h2 id="detailNom"></h2>
                          <p class="product-category" id="detailCategorie"></p>

                          <div class="product-prices">
                            <div class="price-item">
                              <label>Prix normal</label>
                              <span class="price" id="detailPrix"></span>
                            </div>
                            <div class="price-item" id="promoSection" style="display: none;">
                              <label>Prix promo</label>
                              <span class="price promo" id="detailPrixPromo"></span>
                            </div>
                          </div>

                          <div class="product-stock-info">
                            <div class="stock-item">
                              <span class="mio">inventory</span>
                              <div>
                                <label>Stock disponible</label>
                                <strong id="detailQte"></strong>
                              </div>
                            </div>
                          </div>

                          <div class="product-description">
                            <h4>Description</h4>
                            <div id="detailDescription"></div>
                          </div>

                          <div id="detailVariantes" style="display: none;">
                            <h4 style="margin-bottom: 10px;">Variantes disponibles</h4>
                            <div id="variantesList"></div>
                          </div><br>

                          <div class="product-meta">
                            <div class="meta-item">
                              <span class="mio">person</span>
                              <div>
                                <label>Créé par</label>
                                <span id="detailCreatedBy"></span>
                              </div>
                            </div>
                            <div class="meta-item">
                              <span class="mio">schedule</span>
                              <div>
                                <label>Créé le</label>
                                <span id="detailCreatedAt"></span>
                              </div>
                            </div>
                            <div class="meta-item" id="updateSection" style="display: none;">
                              <span class="mio">update</span>
                              <div>
                                <label>Mis à jour par</label>
                                <span id="detailUpdatedBy"></span>
                              </div>
                            </div>
                            <div class="meta-item" id="updateDateSection" style="display: none;">
                              <span class="mio">event</span>
                              <div>
                                <label>Mis à jour le</label>
                                <span id="detailUpdatedAt"></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="modal-footer">
                      <div class="btns v2">
                        <div class="prev" onclick="closeProductDetails()">
                          <span class="mio">close</span>
                          Fermer
                        </div>
                        <div class="next" onclick="editProductFromDetails()">
                          <span class="mio">edit</span>
                          Modifier
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="active gestClients auth-spec2">
                <!-- Section Gestion des clients -->
                <section id="clientsManager" class="content-section">
                  <div class="clients-container">

                    <!-- Header -->
                    <div class="section-header">
                      <div class="header-left">
                        <h2>Gestion des clients</h2>
                      </div>
                      <div class="header-actions">
                        <span class="mio searchClients btn-secondary">search</span>
                      </div>
                    </div>

                    <!-- Filtres -->
                    <div class="filters-bar popups">
                      <form onsubmit="filterClients(event)">
                        <h3>
                          Filtrer les résultats <span class="mio">close</span>
                        </h3>
                        <div class="filter-item search-filter search-box">
                          <label>Rechercher</label>
                          <div class="entry">
                            <input type="text" id="clientSearch" placeholder="Nom, prénom, email, téléphone ou adresse...">
                            <span class="mio icons">search</span>
                          </div>
                        </div>
                        <div class="filter-group">
                          <div class="filter-item">
                            <label>Trier par nom</label>
                            <div class="entry">
                              <select id="clientSortName">
                                <option value="">Aucun tri</option>
                                <option value="asc">A → Z</option>
                                <option value="desc">Z → A</option>
                              </select>
                            </div>
                          </div>

                          <div class="filter-item">
                            <label>Trier par commandes</label>
                            <div class="entry">
                              <select id="clientSortOrders">
                                <option value="">Aucun tri</option>
                                <option value="asc">Croissant (↑)</option>
                                <option value="desc">Décroissant (↓)</option>
                              </select>
                            </div>
                          </div>

                          <div class="filter-item">
                            <label>Trier par date d'arrivée</label>
                            <div class="entry">
                              <select id="clientSortDate">
                                <option value="desc">Du plus récent</option>
                                <option value="asc">Du plus ancien</option>
                              </select>
                            </div>
                          </div>

                          <div class="filter-actions btns">
                            <button type="button" class="btn-reset-filters prev" onclick="resetClientsFilters()">
                              <span class="mio">refresh</span> Réinitialiser
                            </button>
                            <button type="submit" class="next">
                              <span class="mio">search</span> Rechercher
                            </button>
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Liste des clients -->
                    <div class="clients-list" id="clientsList">
                      <!-- Rempli dynamiquement -->
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-controls" id="paginationControls" style="display: none;">
                      <button class="pagination-btn prev-page" id="prevPageBtnCli">
                        <span class="mio">navigate_before</span>
                      </button>
                      <div class="pagination-select">
                        <select id="pageSelectCli">
                          <!-- Rempli dynamiquement -->
                        </select>
                      </div>
                      <button class="pagination-btn next-page" id="nextPageBtnCli">
                        <span class="mio">navigate_next</span>
                      </button>
                    </div>
                  </div>
                </section>

                <!-- Modal Détails Client -->
                <div class="modal-overlay" id="modalClientDetails" style="display: none;">
                  <div class="modal-content modal-client-details">
                    <div class="modal-header">
                      <h2><span class="mio">person</span> Détails du client</h2>
                      <span class="mio close-modal" onclick="closeClientDetailsModal()">close</span>
                    </div>
                    <div class="modal-body" id="clientDetailsContent">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </div>

                <!-- Modal Commandes Client -->
                <div class="modal-overlay" id="modalClientOrders" style="display: none;">
                  <div class="modal-content modal-client-orders">
                    <div class="modal-header">
                      <h2><span class="mio">shopping_cart</span> <span id="clientOrdersTitle">Commandes du client</span></h2>
                      <span class="mio close-modal" onclick="closeClientOrdersModal()">close</span>
                    </div>
                    <div class="modal-body" id="clientOrdersContent">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </div>

                <!-- Modal Bloquer/Suspendre Client -->
                <div class="modal-overlay" id="modalBlockClient" style="display: none;">
                  <div class="modal-content modal-block-client">
                    <div class="modal-header">
                      <h2><span class="mio">block</span> Bloquer ou suspendre le client</h2>
                      <span class="mio close-modal" onclick="closeBlockClientModal()">close</span>
                    </div>
                    <div class="modal-body">
                      <p class="warning-text">
                        <span class="mio">warning</span>
                        Cette action empêchera le client de se connecter et de passer des commandes.
                      </p>

                      <form novalidate id="formBlockClient">
                        <input type="hidden" id="block_client_id">

                        <div>
                          <label>Type de blocage <span class="ppri">*</span></label>
                          <div class="block-type-options">
                            <label class="radio-option">
                              <input type="radio" name="block_type" value="permanent" checked>
                              <span class="radio-custom"></span>
                              <span class="radio-label">Blocage définitif</span>
                            </label>
                            <label class="radio-option">
                              <input type="radio" name="block_type" value="temporary">
                              <span class="radio-custom"></span>
                              <span class="radio-label">Suspension temporaire</span>
                            </label>
                          </div>
                        </div>

                        <div id="temporaryDateField" style="display: none;"><br>
                          <label for="block_until_date">Date et heure de fin de suspension <span class="ppri">*</span></label>
                          <div class="entry">
                            <input type="datetime-local" id="block_until_date">
                          </div>
                          <small class="info">Le client sera automatiquement débloqué à cette date</small>
                        </div>

                        <div><br>
                          <label for="block_password">Votre mot de passe <span class="ppri">*</span></label>
                          <div class="entry">
                            <input type="password" id="block_password" required>
                            <span class="mio eyes icons">visibility</span>
                          </div>
                        </div>

                        <div class="btns">
                          <span class="prev" onclick="closeBlockClientModal()"><span class="mio">close</span> Annuler</span>
                          <button type="submit" class="next"><span class="mio">block</span> Confirmer le blocage</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="active gestionUsers">
                <!-- Section Gestion des utilisateurs -->
                <section id="usersManager" class="content-section">
                  <div class="users-manager-container">

                    <!-- Header -->
                    <div class="section-header">
                      <div class="header-left">
                        <h2>Gestion des utilisateurs</h2>
                      </div>
                      <div class="header-actions">
                        <span class="btn-secondary mio" title="Rechercher parmis les utilisateurs" onclick="showFiltersUsers()">search</span>
                      </div>

                    </div>
                    <!-- Bouton ajouter utilisateur -->
                    <!-- <div class="add-user-section btns sympa auth-2" id="addUserSection" style="display: none;">
                      <button class="next" onclick="openAddUserModal()">
                        <span class="mio">person_add</span> Ajouter un utilisateur
                      </button>
                    </div> -->

                    <!-- Barre de recherche et filtres -->
                    <div class="filters-bar popups active" id="searchFiltersBar">
                      <form novalidate>
                        <h3>Filtrer les résultats <span class="mio">close</span></h3>
                        <div class="entry search-box">
                          <input type="search" id="userSearch" placeholder="Rechercher par nom, prénom, pseudo ou email...">
                          <span class="mio icons">search</span>
                        </div>

                        <div class="filter-group">
                          <div class="filter-item">
                            <label for="filterStatus2">Statut</label>
                            <select id="filterStatus2">
                              <option value="">Tous</option>
                              <option value="0">Actifs</option>
                              <option value="1">Bloqués</option>
                            </select>
                          </div>

                          <div class="filter-item" style="display: none;">
                            <label for="filterRole">Rôle</label>
                            <select id="filterRole">
                              <option value="">Tous</option>
                              <option value="0">Propriétaire</option>
                              <option value="1">Administrateur</option>
                            </select>
                          </div>

                          <div class="filter-item">
                            <label for="filterAuth">Autorisation</label>
                            <select id="filterAuth">
                              <option value="">Toutes</option>
                              <!-- Rempli dynamiquement -->
                            </select>
                          </div>

                          <div class="btns">
                            <div class="btn-icon prev" onclick="resetFilters()" id="btnResetFilters" title="Réinitialiser les filtres">
                              <span class="mio">refresh</span> Réinitialiser le filtre
                            </div>
                            <button type="submit" class="next">Valider</button>
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Box utilisateur connecté -->
                    <div class="current-user-box">
                      <div class="user-card">
                        <div class="user-avatar" onclick="location.hash='/settings/user-infos'">
                          <img src="<?= $_SESSION['pic'] ?>" alt="Photo de profil" class="profile_pic">
                          <div class="edit-overlay">
                            <span class="mio">edit</span>
                          </div>
                        </div>

                        <div class="user-info">
                          <h3 class="namesAlt"><?= htmlspecialchars($_SESSION['names']) ?></h3>
                          <p class="user-pseudo">@<?= htmlspecialchars($_SESSION['username']) ?></p>
                          <p class="user-email emailAlt"><?= htmlspecialchars($_SESSION['email']) ?></p>

                          <div class="user-meta">
                            <span class="badge-role"><?= $_SESSION['role'] == 0 ? 'Propriétaire' : 'Administrateur' ?></span>
                            <span class="badge-date">Membre depuis <?= formatDateHuman($_SESSION['created_at']) ?></span>
                          </div>

                          <div class="user-auths">
                            <p>
                              <strong>
                                <span class="mio">verified_user</span>
                                <span>
                                  <span id="currentUserAuthCount"><?= count($_SESSION['auths']) ?></span> autorisation<?= count($_SESSION['auths']) > 1 ? 's' : '' ?>
                                </span>
                              </strong>
                            </p>
                            <button class="btn-view-details next" onclick="viewUserDetails(<?= $_SESSION['id'] ?>)">
                              <span class="mio">visibility</span> Voir ma fiche
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Liste des utilisateurs -->
                    <div class="users-list-container" id="usersListContainer" style="display: none;">
                      <h2 class="list-title">
                        <span class="mio">people</span> Liste des utilisateurs
                        <span class="count" id="usersCount">(0)</span>
                      </h2>

                      <div class="dashboard-widgets users-list" id="usersList">
                        <!-- Rempli dynamiquement -->
                      </div>

                      <div class="load-more-container" id="loadMoreContainer" style="display: none;">
                        <button class="btn-load-more next" id="btnLoadMore">
                          <span class="mio">expand_more</span> Charger plus d'utilisateurs
                        </button>
                      </div>

                      <div class="end-list-message" id="endListMessage" style="display: none;">
                        <span class="mio">check_circle</span> Fin de la liste
                      </div>
                    </div>

                  </div>
                </section>

                <!-- Modal Voir détails utilisateur -->
                <div class="modal-overlay" id="modalUserDetails" style="display: none;">
                  <div class="modal-content modal-user-details">
                    <div class="modal-header">
                      <h2><span class="mio">person</span> Fiche utilisateur</h2>
                      <span class="mio close-modal" onclick="closeUserDetailsModal()">close</span>
                    </div>

                    <div class="modal-body" id="userDetailsContent">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </div>

                <!-- Modal Modifier autorisations -->
                <div class="modal-overlay" id="modalEditPermissions" style="display: none;">
                  <div class="modal-content modal-edit-permissions">
                    <div class="modal-header">
                      <h2><span class="mio">admin_panel_settings</span> Modifier les autorisations</h2>
                      <span class="mio close-modal" onclick="closeEditPermissionsModal()">close</span>
                    </div>

                    <div class="modal-body">
                      <div class="user-info-mini">
                        <img src="" alt="" id="editPermUserImg">
                        <div>
                          <h3 id="editPermUserName"></h3>
                          <p id="editPermUserPseudo"></p>
                        </div>
                      </div>

                      <form novalidate id="formEditPermissions">
                        <input type="hidden" id="edit_perm_user_id">

                        <div class="permissions-section">
                          <div class="permissions-actions">
                            <button type="button" class="btn-select-all" onclick="selectAllPermissionsEdit(true)">
                              <span class="mio">check_box</span> Tout autoriser
                            </button>
                            <button type="button" class="btn-deselect-all" onclick="selectAllPermissionsEdit(false)">
                              <span class="mio">check_box_outline_blank</span> Tout refuser
                            </button>
                          </div>

                          <div class="permissions-grid" id="permissionsGridEdit">
                            <!-- Rempli dynamiquement -->
                          </div>
                        </div>

                        <div class="btns">
                          <span class="prev" onclick="closeEditPermissionsModal()"><span class="mio">close</span> Annuler</span>
                          <button type="submit" class="next"><span class="mio">save</span> Enregistrer les modifications</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="active auth-18 gestAvis active">
                <!-- Section Gestion des avis clients -->
                <section id="reviewsManager" class="content-section">
                  <div class="reviews-container">

                    <!-- Header -->
                    <div class="section-header">
                      <div class="header-left">
                        <h2>Avis clientèle</h2>
                      </div>
                      <div class="header-actions">
                        <span class="mio searchReviews btn-secondary">search</span>
                      </div>
                    </div>

                    <!-- Filtres -->
                    <div class="filters-bar popups">
                      <form onsubmit="filterReviews(event)">
                        <h3>
                          Filtrer les résultats <span class="mio">close</span>
                        </h3>
                        <div class="filter-item search-filter search-box">
                          <label>Rechercher les notes d'un client (Nom, prénom, email, téléphone ou partie d'un avis)</label>
                          <div class="entry">
                            <input type="text" id="reviewSearch" placeholder="Nom, prénom, email, téléphone, partie d'un avis...">
                            <span class="mio icons">search</span>
                          </div>
                        </div>
                        <div class="filter-group">
                          <div class="filter-item">
                            <label>Trier par note</label>
                            <div class="entry">
                              <select id="reviewSortRating">
                                <option value="asc">1 → 5 étoiles</option>
                                <option value="desc">5 → 1 étoiles</option>
                              </select>
                            </div>
                          </div>

                          <div class="filter-item">
                            <label>Trier par date</label>
                            <div class="entry">
                              <select id="reviewSortDate">
                                <option value="">Aucun tri</option>
                                <option value="desc">Du plus récent</option>
                                <option value="asc">Du plus ancien</option>
                              </select>
                            </div>
                          </div>

                          <div class="filter-item">
                            <label>Trier par produit</label>
                            <div class="entry">
                              <select id="reviewSortProduct">
                                <option value="">Aucun tri</option>
                                <option value="asc">A → Z</option>
                                <option value="desc">Z → A</option>
                              </select>
                            </div>
                          </div>

                          <div class="filter-actions btns">
                            <button type="button" class="btn-reset-filters prev" onclick="resetReviewsFilters()">
                              <span class="mio">refresh</span> Réinitialiser
                            </button>
                            <button type="submit" class="next">
                              <span class="mio">search</span> Rechercher
                            </button>
                          </div>
                        </div>
                      </form>
                    </div>

                    <!-- Liste des avis -->
                    <div class="reviews-list" id="reviewsList">
                      <!-- Rempli dynamiquement -->
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-controls" id="reviewsPaginationControls" style="display: none;">
                      <button class="pagination-btn prev-page" id="reviewsPrevPageBtn">
                        <span class="mio">navigate_before</span>
                      </button>
                      <div class="pagination-select">
                        <select id="reviewsPageSelect">
                          <!-- Rempli dynamiquement -->
                        </select>
                      </div>
                      <button class="pagination-btn next-page" id="reviewsNextPageBtn">
                        <span class="mio">navigate_next</span>
                      </button>
                    </div>
                  </div>
                </section>
              </div>
              <div class="active auth-11 gestRemboursements">
                <!-- Section Gestion des remboursements -->
                <section id="refundsManager" class="content-section">
                  <div class="refunds-container">

                    <!-- Header -->
                    <div class="section-header">
                      <div class="header-left">
                        <h2>Gestion des Remboursements & litiges</h2>
                      </div>
                    </div>

                    <!-- Onglets principaux -->
                    <div class="refunds-tabs">
                      <div class="tab-btn active" data-tab="encours">
                        <span class="mio">pending_actions</span> En attente / En cours
                      </div>
                      <div class="tab-btn" data-tab="rembourses">
                        <span class="mio">check_circle</span> Remboursés
                      </div>
                      <div class="tab-btn" data-tab="rejetes">
                        <span class="mio">cancel</span> Rejetés
                      </div>
                    </div>

                    <!-- Liste des demandes de remboursement -->
                    <div class="refunds-list" id="refundsList">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </section>

                <!-- Modal Détails Commande (pour remboursements) -->
                <div class="modal-overlay" id="modalRefundOrderDetails" style="display: none;">
                  <div class="modal-content modal-refund-order-details">
                    <div class="modal-header">
                      <h2><span class="mio">receipt_long</span> Détails de la commande</h2>
                      <span class="mio close-modal" onclick="closeRefundOrderDetailsModal()">close</span>
                    </div>
                    <div class="modal-body" id="refundOrderDetailsContent">
                      <!-- Rempli dynamiquement -->
                    </div>
                  </div>
                </div>

                <!-- Modal Rejeter une demande -->
                <div class="modal-overlay" id="modalRejectRefund" style="display: none;">
                  <div class="modal-content modal-reject-refund">
                    <div class="modal-header">
                      <h2><span class="mio">cancel</span> Rejeter la demande de remboursement</h2>
                      <span class="mio close-modal" onclick="closeRejectRefundModal()">close</span>
                    </div>
                    <div class="modal-body">
                      <p class="warning-text">
                        <span class="mio">warning</span>
                        Cette action va rejeter définitivement la demande de remboursement et notifier le client par email avec le motif du rejet.
                      </p>

                      <form novalidate id="formRejectRefund">
                        <input type="hidden" id="reject_refund_id">
                        <input type="hidden" id="reject_order_id">

                        <div>
                          <label for="reject_motif">Motif du rejet <span class="ppri">*</span></label>
                          <div class="entry">
                            <textarea id="reject_motif" class="tiny" rows="6" required placeholder="Expliquez la raison du rejet de cette demande de remboursement..."></textarea>
                          </div>
                          <small class="info">Maximum 300 caractères</small>
                        </div><br>
                        <div>
                          <label for="reject_password">Votre mot de passe <span class="ppri">*</span></label>
                          <div class="entry">
                            <input type="password" id="reject_password" required style="border: solid 1px #aaa;">
                            <span class="mio icons eye">visibility</span>
                          </div>
                        </div>

                        <div class="btns">
                          <span class="prev" onclick="closeRejectRefundModal()"><span class="mio">close</span> Annuler</span>
                          <button type="submit" class="next"><span class="mio">cancel</span> Confirmer le rejet</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Modal Effectuer le remboursement -->
                <div class="modal-overlay" id="modalProcessRefund" style="display: none;">
                  <div class="modal-content modal-process-refund">
                    <div class="modal-header">
                      <h2><span class="mio">payments</span> Effectuer le remboursement</h2>
                      <span class="mio close-modal" onclick="closeProcessRefundModal()">close</span>
                    </div>
                    <div class="modal-body">
                      <!-- Infos commande -->
                      <div class="refund-order-summary" id="refundOrderSummary">
                        <!-- Rempli dynamiquement -->
                      </div>

                      <form novalidate id="formProcessRefund">
                        <input type="hidden" id="process_refund_id">
                        <input type="hidden" id="process_order_id">
                        <input type="hidden" id="process_max_amount">

                        <div>
                          <label>Type de remboursement <span class="ppri">*</span></label>
                          <div class="refund-type-options">
                            <label class="radio-option">
                              <input type="radio" name="refund_type" value="complet" checked>
                              <span class="radio-custom"></span>
                              <span class="radio-label">Remboursement complet (90% de la somme sera remboursée).</span>
                            </label>
                            <label class="radio-option">
                              <input type="radio" name="refund_type" value="partiel">
                              <span class="radio-custom"></span>
                              <span class="radio-label">Remboursement partiel.</span>
                            </label>
                          </div>
                        </div>

                        <div id="partialAmountField" style="display: none;"> <br>
                          <label for="process_amount">Montant à rembourser <span class="ppri">*</span></label>
                          <div class="entry">
                            <input type="number" id="process_amount" step="0.01" min="0.01" placeholder="Ex: 50.00">
                            <span class="mio icons">payments</span>
                          </div>
                          <small class="info">Le montant ne peut pas dépasser <strong id="maxAmountDisplay">0.00</strong> CAD</small>
                        </div>

                        <div> <br>
                          <label for="process_password">Votre mot de passe <span class="ppri">*</span></label>
                          <div class="entry">
                            <input type="password" id="process_password" required>
                            <span class="mio eyes icons">visibility</span>
                          </div>
                        </div>

                        <div class="btns">
                          <span class="prev" onclick="closeProcessRefundModal()"><span class="mio">close</span> Annuler</span>
                          <button type="submit" class="next"><span class="mio">payments</span> Effectuer le remboursement</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <div class="active settingsBox">
                <!-- Section Paramètres -->
                <section id="settingsManager" class="content-section settings-layout">
                  <div class="settings-container">

                    <!-- Sidebar gauche -->
                    <aside class="settings-sidebar">
                      <div class="settings-header">
                        <h2><span class="mio">settings</span> Paramètres</h2>
                        <div class="settings-tabs">
                          <div class="tab-btn active" data-tab="user">Mes paramètres</div>
                          <div class="tab-btn" data-tab="website" id="websiteTabBtn" style="display: none;">Paramètres du site</div>
                        </div>
                      </div>

                      <!-- Menu Mes paramètres -->
                      <nav class="settings-menu user" id="userSettingsMenu">
                        <div class="menu-category">
                          <h3>Compte & données personnelles</h3>
                          <ul>
                            <li data-setting="user-infos"><span class="mio">person</span> Infos personnelles</li>
                            <li data-setting="user-access"><span class="mio">vpn_key</span> Accès et contrôle</li>
                          </ul>
                        </div>

                        <div class="menu-category">
                          <h3>Sécurité</h3>
                          <ul>
                            <li data-setting="user-password"><span class="mio">lock</span> Mot de passe</li>
                            <li data-setting="user-2fa"><span class="mio">security</span> Double authentification</li>
                            <li data-setting="user-passkeys"><span class="mio">fingerprint</span> Clés d'accès</li>
                          </ul>
                        </div>

                        <div class="menu-category">
                          <h3>Déconnexion</h3>
                          <ul>
                            <li data-setting="logout-all" class="danger"><span class="mio">devices</span> Se déconnecter de tous les appareils</li>
                            <li data-setting="logout-now" class="danger"><span class="mio">logout</span> Se déconnecter</li>
                          </ul>
                        </div>
                      </nav>

                      <!-- Menu Paramètres du site -->
                      <nav class="settings-menu website" id="websiteSettingsMenu" style="display: none;">
                        <div class="menu-category">
                          <h3>Configuration générale</h3>
                          <ul>
                            <li data-setting="site-general"><span class="mio">web</span> Informations générales</li>
                          </ul>
                        </div>

                        <div class="menu-category">
                          <h3>Coordonnées</h3>
                          <ul>
                            <li data-setting="site-contact"><span class="mio">contact_mail</span> Contact et adresses</li>
                          </ul>
                        </div>

                        <div class="menu-category">
                          <h3>Configuration email</h3>
                          <ul>
                            <li data-setting="site-email-clients"><span class="mio">mail</span> Email clients</li>
                            <li data-setting="site-email-users"><span class="mio">group</span> Email utilisateurs</li>
                          </ul>
                        </div>

                        <div class="menu-category">
                          <h3>Paiements</h3>
                          <ul>
                            <li data-setting="site-payment"><span class="mio">payment</span> Configuration paiements</li>
                          </ul>
                        </div>
                      </nav>
                    </aside>

                    <!-- Contenu droite -->
                    <main class="settings-content">
                      <div class="settings-default">
                        <div>
                          <span class="mio">settings</span>
                          <p>Cliquez sur un paramètre pour pouvoir le gérer.</p>
                        </div>
                      </div>

                      <!-- Mes paramètres - Contenu -->
                      <div id="settings-user-content" style="display: none;">

                        <!-- Infos personnelles -->
                        <div class="setting-panel" id="panel-user-infos" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Informations personnelles</h2>
                            <p class="info">Modifiez votre nom, prénom et photo de profil</p>
                          </div>

                          <div class="panel-body">
                            <!-- Photo de profil -->
                            <div class="profile-section">
                              <figure>
                                <input type="file" accept=".png, .jpg, .jpeg, .webp" hidden id="profile_pic_input">
                                <a href="<?= $_SESSION['pic'] ?>" data-lightbox="profile" data-title="Votre photo de profil">
                                  <img src="<?= $_SESSION['pic'] ?>" class="profile_pic" alt="Votre photo de profil">
                                </a>
                                <figcaption>
                                  <span class="mio">edit</span>
                                  <p>Cliquez pour modifier votre photo</p>
                                </figcaption>
                              </figure>
                            </div>

                            <!-- Formulaire nom/prénom -->
                            <form novalidate id="formInfosPerso">
                              <div>
                                <label for="user_nom">Nom <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="text" id="user_nom" value="<?= htmlspecialchars($_SESSION['nom']) ?>" required>
                                </div>
                              </div>

                              <div>
                                <label for="user_prenom">Prénom <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="text" id="user_prenom" value="<?= htmlspecialchars($_SESSION['prenom']) ?>" required>
                                </div>
                              </div>

                              <div class="btns v2">
                                <button type="submit" class="next"><span class="mio">save</span> Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>

                        <!-- Accès et contrôle -->
                        <div class="setting-panel" id="panel-user-access" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Accès et contrôle</h2>
                            <p class="info">Vérifiez vos informations d'authentification</p>
                          </div>

                          <div class="panel-body">
                            <!-- Lecture seule -->
                            <div class="info-display">
                              <div class="info-item">
                                <label>Pseudo</label>
                                <div class="value"><?= htmlspecialchars($_SESSION['username']) ?></div>
                              </div>

                              <div class="info-item">
                                <label>Adresse email actuelle</label>
                                <div class="value2"><?= htmlspecialchars($_SESSION['email']) ?></div>
                              </div>
                            </div>

                            <button class="next v2" id="btnChangeEmail">
                              <span class="mio">edit</span> Modifier l'email
                            </button>

                            <!-- Formulaire changement email (caché par défaut) -->
                            <div id="changeEmailForm" style="display: none;">
                              <form novalidate id="formChangeEmail">
                                <div>
                                  <label for="new_email">Nouvelle adresse email <span class="ppri">*</span></label>
                                  <div class="entry">
                                    <input type="email" id="new_email" required>
                                    <span class="mio icons">email</span>
                                  </div>
                                </div>

                                <div>
                                  <label for="confirm_pass_email">Mot de passe actuel <span class="ppri">*</span></label>
                                  <div class="entry">
                                    <input type="password" id="confirm_pass_email" required>
                                    <span class="mio icons eyes">visibility</span>
                                  </div>
                                </div>

                                <div class="btns">
                                  <span class="prev" id="cancelChangeEmail"><span class="mio">close</span> Annuler</span>
                                  <button type="submit" class="next"><span class="mio">send</span> Vérifier l'email</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>

                        <!-- Mot de passe -->
                        <div class="setting-panel" id="panel-user-password" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Mot de passe</h2>
                            <p class="info">Modifiez votre mot de passe en toute sécurité</p>
                          </div>

                          <div class="panel-body">
                            <form novalidate id="formChangePassword">
                              <div>
                                <label for="current_pass">Mot de passe actuel <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="password" id="current_pass" required>
                                  <span class="mio eyes icons">visibility</span>
                                </div>
                              </div>

                              <div>
                                <label for="new_pass">Nouveau mot de passe <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="password" id="new_pass" required minlength="8">
                                  <span class="mio eyes icons">visibility</span>
                                </div>
                                <small class="info">Minimum 8 caractères</small>
                              </div>

                              <div class="btns sympa">
                                <button type="submit" class="next"><span class="mio">save</span> Modifier le mot de passe</button>
                              </div>
                            </form>
                          </div>
                        </div>

                        <!-- Double authentification -->
                        <div class="setting-panel" id="panel-user-2fa" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Double authentification</h2>
                            <p class="info">Sécurisez votre compte avec une couche de protection supplémentaire</p>
                          </div>

                          <div class="panel-body">
                            <!-- 2FA Email -->
                            <div class="setting-item">
                              <div class="setting-info">
                                <h3><span class="mio">email</span> Authentification par email</h3>
                                <p class="info">Recevez un code de vérification par email lors de chaque connexion</p>
                              </div>
                              <div class="setting-action">
                                <div class="toggle-switch" id="toggle2FAEmail">
                                  <input type="checkbox" id="2fa_email" <?= $_SESSION['dbl'] == 1 ? 'checked' : '' ?>>
                                  <label for="2fa_email"></label>
                                </div>
                              </div>
                            </div>

                            <div class="divider"></div>

                            <!-- 2FA Application -->
                            <div class="setting-item">
                              <div class="setting-info">
                                <h3><span class="mio">smartphone</span> Authentification par application</h3>
                                <p class="info">Utilisez Google Authenticator ou une autre application TOTP</p>
                              </div>
                              <div class="setting-action">
                                <div class="toggle-switch" id="toggle2FAApp">
                                  <input type="checkbox" id="2fa_app" <?= !empty($_SESSION['totp']) ? 'checked' : '' ?>>
                                  <label for="2fa_app"></label>
                                </div>
                              </div>
                            </div>

                            <!-- Modal configuration 2FA App -->
                            <div id="modal2FASetup" class="modal-2fa" style="display: none;">
                              <div class="modal-2fa-content">
                                <h3>Configuration de l'authentification par application</h3>
                                <div id="qrcode-container">
                                  <p class="info">Scannez ce QR code avec votre application d'authentification</p>
                                  <div id="qrcode"></div>
                                  <p class="secret-key">Clé secrète : <code id="secret-key"></code></p>
                                  <br>
                                  <div class="info">Cliquez pour copier la clé ou scanner le code directement sur votre application d'authentification telle que Authenticator, Authy, etc...</div>

                                </div><br>
                                <div class="btns">
                                  <span class="prev" id="cancel2FA"><span class="mio">close</span> Annuler</span>
                                  <button class="next" id="verify2FA"><span class="mio">check</span> Vérifier</button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Clés d'accès -->
                        <div class="setting-panel" id="panel-user-passkeys" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Clés d'accès</h2>
                            <p class="info">Gérez vos clés d'accès biométriques</p>
                          </div>

                          <div class="panel-body keys">
                            <div class="btns">
                              <button class="next" onclick="createPasskey('')">
                                <span class="mio">add</span> Ajouter une clé d'accès
                              </button>
                            </div>

                            <div id="passkeys-list" class="passkeys-list">
                              <!-- Rempli dynamiquement -->
                            </div>
                          </div>
                        </div>

                      </div>

                      <!-- Paramètres du site - Contenu -->
                      <div id="settings-website-content" style="display: none;">

                        <!-- Configuration générale -->
                        <div class="setting-panel" id="panel-site-general" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Informations générales</h2>
                            <p class="info">Configuration de base du site</p>
                          </div>

                          <div class="panel-body">
                            <form novalidate id="formSiteGeneral">
                              <div>
                                <label for="site_name">Nom du site <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="text" id="site_name" required value="<?= $_SESSION['website_name'] ?>">
                                  <span class="mio icons">web</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_status">Statut du site <span class="ppri">*</span></label>
                                <div class="entry">
                                  <span class="mio icons">power_settings_new</span>
                                  <select id="site_status" required>
                                    <option value="0" <?= $_SESSION['website_statut'] == 0 ? 'selected' : '' ?>>Site actif</option>
                                    <option value="1" <?= $_SESSION['website_statut'] == 1 ? 'selected' : '' ?>>Site en maintenance</option>
                                  </select>
                                </div>
                              </div>

                              <div id="maintenance_date_container" style="display: none;">
                                <label for="site_maintenance_date">Date limite de maintenance</label>
                                <div class="entry">
                                  <input type="date" id="site_maintenance_date">
                                </div>
                              </div>

                              <div>
                                <label for="site_devise">Devise principale <span class="ppri">*</span></label>
                                <div class="entry">
                                  <select id="site_devise" required>
                                    <option value="CAD">CAD - Dollar Canadien</option>
                                    <option value="USD">USD - Dollar Américain</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="XAF">XAF - Franc CFA (Afrique Centrale)</option>
                                    <option value="XOF">XOF - Franc CFA (Afrique de l'Ouest)</option>
                                  </select>
                                </div>
                              </div>

                              <div class="btns">
                                <button type="submit" class="next"><span class="mio">save</span> Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>

                        <!-- Contact et adresses -->
                        <div class="setting-panel" id="panel-site-contact" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Contact et adresses</h2>
                            <p class="info">Informations de contact visibles sur le site</p>
                          </div>

                          <div class="panel-body">
                            <form novalidate id="formSiteContact">
                              <div>
                                <label for="site_email_support">Email support client <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="email" id="site_email_support" required>
                                  <span class="mio icons">support_agent</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_tel_1">Téléphone principal <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="tel" id="site_tel_1" required>
                                  <span class="mio icons">phone</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_tel_2">Téléphone secondaire</label>
                                <div class="entry">
                                  <input type="tel" id="site_tel_2">
                                  <span class="mio icons">phone</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_adresse_1">Adresse physique principale <span class="ppri">*</span></label>
                                <div class="entry">
                                  <textarea id="site_adresse_1" rows="2" required></textarea>
                                  <span class="mio icons">location_on</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_map_1">Lien Google Maps (adresse 1) <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="url" id="site_map_1" required>
                                  <span class="mio icons">map</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_adresse_2">Adresse physique secondaire</label>
                                <div class="entry">
                                  <textarea id="site_adresse_2" rows="2"></textarea>
                                  <span class="mio icons">location_on</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_map_2">Lien Google Maps (adresse 2)</label>
                                <div class="entry">
                                  <input type="url" id="site_map_2">
                                  <span class="mio icons">map</span>
                                </div>
                              </div>

                              <div class="btns sympa">
                                <button type="submit" class="next"><span class="mio">save</span> Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>

                        <!-- Email clients -->
                        <div class="setting-panel" id="panel-site-email-clients" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Email clients</h2>
                            <p class="info">Configuration de l'email pour les newsletters et communications clients</p>
                          </div>

                          <div class="panel-body">
                            <form novalidate id="formSiteEmailClients">
                              <div>
                                <label for="site_email_clients">Adresse email <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="email" id="site_email_clients" required>
                                  <span class="mio icons">email</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_pass_clients">Mot de passe email <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="password" id="site_pass_clients" required placeholder="Mot de passe d'application (Si adresse Gmail)">
                                  <span class="mio eyes icons">visibility</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_smtp_1">Serveur SMTP</label>
                                <div class="entry">
                                  <input type="text" id="site_smtp_1" placeholder="Ex: smtp.gmail.com (si adresse Gmail)" required>
                                  <span class="mio icons">dns</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_port_ssl_clients">Port SSL/TLS <span class="ppri">*</span></label>
                                <div class="entry">
                                  <select id="site_port_ssl_clients">
                                    <option value="465">Port 465 (SSL)</option>
                                    <option value="Port 587">Port 587 (TLS)</option>
                                  </select>
                                </div>
                              </div>

                              <div class="btns sympa">
                                <button type="submit" class="next"><span class="mio">save</span> Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>

                        <!-- Email utilisateurs -->
                        <div class="setting-panel" id="panel-site-email-users" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Email utilisateurs</h2>
                            <p class="info">Configuration de l'email pour les communications internes</p>
                          </div>

                          <div class="panel-body">
                            <form novalidate id="formSiteEmailUsers">
                              <div>
                                <label for="site_email_users">Adresse email</label>
                                <div class="entry">
                                  <input type="email" id="site_email_users">
                                  <span class="mio icons">email</span>
                                </div>
                                <small class="info">Laissez vide pour ne pas utiliser d'email séparé</small>
                              </div>

                              <div>
                                <label for="site_pass_users">Mot de passe email</label>
                                <div class="entry">
                                  <input type="password" id="site_pass_users">
                                  <span class="mio eyes icons">visibility</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_smtp_2">Serveur SMTP</label>
                                <div class="entry">
                                  <input type="text" id="site_smtp_2" placeholder="Ex: smtp.gmail.com (si adresse Gmail)" required>
                                  <span class="mio icons">dns</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_port_ssl_users">Port SSL/TLS</label>
                                <div class="entry">
                                  <select id="site_port_ssl_users">
                                    <option value="465">Port 465 (SSL)</option>
                                    <option value="Port 587">Port 587 (TLS)</option>
                                  </select>
                                </div>
                              </div>

                              <div class="btns sympa">
                                <button type="submit" class="next"><span class="mio">save</span> Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>

                        <!-- Configuration paiements -->
                        <div class="setting-panel" id="panel-site-payment" style="display: none;">
                          <div class="panel-header">
                            <h2><span class="mio back-mobile">arrow_back</span> Configuration paiements</h2>
                            <p class="info">Gérez les modes de paiement et frais de livraison</p>
                          </div>

                          <div class="panel-body">
                            <form novalidate id="formSitePayment">
                              <div>
                                <label for="site_api_key_public">Clé Publique Stripe <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="password" id="site_api_key_public" required>
                                  <span class="mio icons eyes">visibility</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_api_key_private">Clé privée Stripe <span class="ppri">*</span></label>
                                <div class="entry">
                                  <input type="password" id="site_api_key_private" required>
                                  <span class="mio icons eyes">visibility</span>
                                </div>
                              </div>

                              <div>
                                <label for="site_frais_livraison">Frais de livraison (CAD)</label>
                                <div class="entry">
                                  <select id="site_frais_livraison">
                                    <option value="0">Livraison Gratuite</option>
                                    <option value="1">Livraison au frais du client</option>
                                  </select>
                                  <!-- <input type="number" step="0.01" id=""> -->
                                  <!-- <span class="mio icons">local_shipping</span> -->
                                </div>
                              </div><br>

                              <div class="setting-item">
                                <div class="setting-info">
                                  <h3>Paiement par virement/carte bancaire</h3>
                                </div>
                                <div class="setting-action">
                                  <div class="toggle-switch">
                                    <input type="checkbox" id="paiement_carte">
                                    <label for="paiement_carte"></label>
                                  </div>
                                </div>
                              </div>

                              <div class="setting-item">
                                <div class="setting-info">
                                  <h3>Paiement via Interac</h3>
                                </div>
                                <div class="setting-action">
                                  <div class="toggle-switch">
                                    <input type="checkbox" id="paiement_mobile">
                                    <label for="paiement_mobile"></label>
                                  </div>
                                </div>
                              </div>

                              <div class="setting-item" style="display: none;">
                                <div class="setting-info">
                                  <h3>Paiement par virement</h3>
                                </div>
                                <div class="setting-action">
                                  <div class="toggle-switch">
                                    <input type="checkbox" id="paiement_virement">
                                    <label for="paiement_virement"></label>
                                  </div>
                                </div>
                              </div>

                              <div class="btns sympa">
                                <button type="submit" class="next"><span class="mio">save</span> Enregistrer</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>

                    </main>
                  </div>
                </section>
              </div>
              <div class="active gestNotifications">
                <!-- Section Notifications -->
                <section id="notificationsManager">
                  <div class="notifications-container">

                    <!-- Panel gauche - Liste -->
                    <div class="notifications-sidebar">
                      <div class="notifications-header">
                        <h2>
                          <span class="mio">notifications</span>
                          Notifications
                          <span class="notif-count" id="notifCount">0</span>
                        </h2>
                        <button class="btn-mark-all-read" id="btnMarkAllRead" style="display: none;">
                          <span class="mio">done_all</span>
                          Tout marquer comme lu
                        </button>
                      </div>

                      <div class="notifications-list" id="notificationsList">
                        <!-- Rempli dynamiquement -->
                      </div>
                    </div>

                    <!-- Panel droit - Détail -->
                    <div class="notifications-detail">
                      <div class="notifications-detail-empty" id="notifDetailEmpty">
                        <span class="mio">notifications_none</span>
                        <p>Sélectionnez une notification pour voir les détails</p>
                      </div>

                      <div class="notifications-detail-content" id="notifDetailContent" style="display: none;">
                        <!-- Rempli dynamiquement -->
                      </div>
                    </div>

                  </div>
                </section>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Bouton + (accueil) -->
      <figure class="f1 addGestBtnPlus">
        <span class="mio btnAdd">add</span>
        <figcaption>
          <div class="auth-12"><span class="mio">add_business</span><span>Nouveau produit</span></div>
          <div class="auth-16"><span class="mio">category</span><span>Gestion des catégories</span></div>
          <div class="auth-17"><span class="mio">badge</span><span>Gestion des bannières</span></div>
          <div class="auth-2"><span class="mio">admin_panel_settings</span><span>Nouvel utilisateur</span></div>
          <div class="auth-20"><span class="mio">notification_add</span><span>Nouvelle newsletter</span></div>
        </figcaption>
      </figure>

      <figure class="f2">
        <figcaption>
          <div class="auth-15"><span class="mio">local_mall</span><span>Gérer les stocks</span></div>
          <div><span class="mio">people</span><span>Gérer les clients</span></div>
          <div><span class="mio">admin_panel_settings</span><span>Gérer les utilisateurs</span></div>
          <div class="auth-18"><span class="mio">star_outline</span><span>Avis des clients</span></div>
          <div class="auth-11"><span class="mio">assignment_late</span><span>Remboursements et litiges</span></div>
          <div><span class="mio">settings</span><span>Paramètres</span></div>
          <div><span class="mio">logout</span> Déconnexion</div>
        </figcaption>
      </figure>
      <div class="menuMobile">
        <ul>
          <li title="Vue d'ensemble" class="active">
            <div><span class="mio">home_max</span><span class="textHid">Accueil</span></div>
          </li>
          <li title="Les statistiques">
            <div><span class="mio">trending_up</span><span class="textHid">Stats</span></div>
          </li>
          <li title="Voir toutes les commandes" class="auth-9">
            <div><span class="mio">local_grocery_store</span><span class="textHid">Commandes</span></div>
          </li>
          <li title="Gérer votre stock" class="auth-15">
            <div><span class="mio">local_mall</span><span class="textHid">Stocks</span></div>
          </li>
          <li title="Plus d'options">
            <div><span class="mio">more_vert</span><span class="textHid">Plus</span></div>
          </li>
        </ul>
      </div>
    </div>
  </main>

  <div class="boxCookie" style="display: none" data-utils="<?= $version_app ?>"></div>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/inc/popupsBox.php');
  ?>
  <script src="<?= $js ?>lightbox.min.js?v=<?= $version_app ?>"></script>
  <script src="<?= $js ?>aaguid.js?v=<?= $version_app ?>"></script>
  <script src="<?= $js ?>all.js?v=<?= $version_app ?>"></script>
  <script src="<?= $js ?>biblio.js?v=<?= $version_app ?>"></script>
  <script src="<?= $js ?>pagination.js?v=<?= $version_app ?>"></script>
  <script src="<?= $js ?>script.js?v=<?= $version_app ?>"></script>
  <script>
    var popupStart = "<?= isset($_SESSION['error']) || isset($_SESSION['success']) ? '0' : '1' ?>";

    <?= isset($_SESSION['error']) ? 'if (popupStart == 0) openError("' . $_SESSION['error'] . '");' : (isset($_SESSION['success']) ? 'if (popupStart == 0) openSuccess("' . $_SESSION['success'] . '");' : '// No message available at this time.') ?>

    <?php
    if (isset($_SESSION['error'])) unset($_SESSION['error']);
    if (isset($_SESSION['success'])) unset($_SESSION['success']); ?>
  </script>

</body>

</html>