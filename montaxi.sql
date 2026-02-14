-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 14 fév. 2026 à 04:25
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `montaxi`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

DROP TABLE IF EXISTS `administrateurs`;
CREATE TABLE IF NOT EXISTS `administrateurs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_general_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('super_admin','moderateur') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'moderateur',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `administrateurs`
--

INSERT INTO `administrateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `actif`, `cree_le`) VALUES
(1, 'Admin', 'MonTaxi', 'kennedylight05@gmail.com', '$2y$10$qWWfeyKIZdALu5R656qzcepWiG9mIvABOqGBr7emwB5dNNz0xipxq', 'super_admin', 1, '2026-02-13 21:05:34');

-- --------------------------------------------------------

--
-- Structure de la table `chauffeurs`
--

DROP TABLE IF EXISTS `chauffeurs`;
CREATE TABLE IF NOT EXISTS `chauffeurs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_general_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `statut` enum('inactif','kyc_en_attente','kyc_rejete','actif','suspendu') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'inactif',
  `en_ligne` tinyint(1) NOT NULL DEFAULT '0',
  `covoiturage_actif` tinyint(1) NOT NULL DEFAULT '1',
  `note_moyenne` decimal(3,2) NOT NULL DEFAULT '0.00',
  `nombre_courses` int UNSIGNED NOT NULL DEFAULT '0',
  `token_session` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mis_a_jour_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `telephone` (`telephone`),
  KEY `idx_chauffeurs_statut` (`statut`),
  KEY `idx_chauffeurs_position` (`latitude`,`longitude`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chauffeurs`
--

INSERT INTO `chauffeurs` (`id`, `nom`, `prenom`, `email`, `telephone`, `mot_de_passe`, `statut`, `en_ligne`, `covoiturage_actif`, `note_moyenne`, `nombre_courses`, `token_session`, `token_expiry`, `derniere_connexion`, `latitude`, `longitude`, `cree_le`, `mis_a_jour_le`) VALUES
(1, 'Kouonga', 'Kennedy', 'kennedylight05@gmail.com', '+237653228070', '$2y$12$EK2OoudCeOLhafGaKNMryeTGLJvwVfVoyiXaZ/0vfyp4mTaLi2ERG', 'actif', 0, 1, 0.00, 0, NULL, NULL, '2026-02-14 01:42:48', 4.03388374, 9.69567725, '2026-02-13 21:10:49', '2026-02-14 03:49:05'),
(2, 'Diamond', 'Plus', 'eliseenoelbiboum.officiel@gmail.com', '+237671966915', '$2y$12$EK2OoudCeOLhafGaKNMryeTGLJvwVfVoyiXaZ/0vfyp4mTaLi2ERG', 'inactif', 0, 1, 0.00, 0, NULL, NULL, NULL, NULL, NULL, '2026-02-13 22:50:30', '2026-02-13 22:50:30');

-- --------------------------------------------------------

--
-- Structure de la table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` int UNSIGNED DEFAULT NULL,
  `chauffeur_id` int UNSIGNED DEFAULT NULL,
  `depart_latitude` decimal(10,8) NOT NULL,
  `depart_longitude` decimal(11,8) NOT NULL,
  `depart_adresse` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `arrivee_latitude` decimal(10,8) NOT NULL,
  `arrivee_longitude` decimal(11,8) NOT NULL,
  `arrivee_adresse` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statut` enum('en_attente','acceptee','en_route','en_cours','terminee','annulee') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_attente',
  `est_covoiturage` tinyint(1) NOT NULL DEFAULT '0',
  `plan_id` int UNSIGNED DEFAULT NULL,
  `type_vehicule` enum('taxi','moto') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'taxi',
  `nb_places` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `places_disponibles` tinyint NOT NULL DEFAULT '1',
  `course_parent_id` int UNSIGNED DEFAULT NULL,
  `distance_km` decimal(8,2) DEFAULT NULL,
  `duree_minutes` int DEFAULT NULL,
  `prix_estime` int UNSIGNED DEFAULT NULL,
  `prix_final` int UNSIGNED DEFAULT NULL,
  `demande_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `acceptee_le` datetime DEFAULT NULL,
  `prise_en_charge_le` datetime DEFAULT NULL,
  `terminee_le` datetime DEFAULT NULL,
  `cree_le` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `chauffeur_id` (`chauffeur_id`),
  KEY `course_parent_id` (`course_parent_id`),
  KEY `idx_courses_statut` (`statut`),
  KEY `idx_courses_position` (`depart_latitude`,`depart_longitude`),
  KEY `plan_id` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `courses`
--

INSERT INTO `courses` (`id`, `client_id`, `chauffeur_id`, `depart_latitude`, `depart_longitude`, `depart_adresse`, `arrivee_latitude`, `arrivee_longitude`, `arrivee_adresse`, `statut`, `est_covoiturage`, `plan_id`, `type_vehicule`, `nb_places`, `places_disponibles`, `course_parent_id`, `distance_km`, `duree_minutes`, `prix_estime`, `prix_final`, `demande_le`, `acceptee_le`, `prise_en_charge_le`, `terminee_le`, `cree_le`) VALUES
(2, 2, NULL, 4.03387757, 9.69568461, 'Ma position actuelle', 4.05196730, 9.73598070, 'Cité Sic Stade Marion, Douala, Cameroun', 'annulee', 0, 3, 'taxi', 1, 1, NULL, NULL, 15, 1050, NULL, '2026-02-14 01:40:32', NULL, NULL, NULL, '2026-02-14 01:40:32');

-- --------------------------------------------------------

--
-- Structure de la table `div_clients`
--

DROP TABLE IF EXISTS `div_clients`;
CREATE TABLE IF NOT EXISTS `div_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `noms` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `pass` text COLLATE utf8mb4_general_ci NOT NULL,
  `statut` int NOT NULL DEFAULT '0' COMMENT '0 pas bloqué, 1 bloqué',
  `pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  `telephone` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `div_clients`
--

INSERT INTO `div_clients` (`id`, `noms`, `email`, `pass`, `statut`, `pic`, `created_at`, `last_update`, `telephone`) VALUES
(1, 'InnovTeam', 'team@team.com', '$2y$10$oOcM6QIBa5HYeNZW1UWy0.elewgNH6K0RCn7I7yjT2N2tsBO0tYem', 0, NULL, '2026-02-13 17:08:57', '2026-02-14 01:21:44', ''),
(2, 'InnovTeam', 'petitnounousdollar@gmail.com', '$2y$10$BiQe0rWe8FdX.JoeRFyfU.I7P7d0GcADpcm67OYjNrU0y1TlmyEYi', 0, NULL, '2026-02-14 00:20:16', '2026-02-14 04:33:10', '');

-- --------------------------------------------------------

--
-- Structure de la table `div_periode`
--

DROP TABLE IF EXISTS `div_periode`;
CREATE TABLE IF NOT EXISTS `div_periode` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mois` int NOT NULL COMMENT 'Contient le numéro du mois',
  `start_week` date NOT NULL COMMENT 'contient une date qui indique toujours un lundi',
  `end_week` date NOT NULL COMMENT 'Contient une date qui indique toujours un dimanche',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `div_periode`
--

INSERT INTO `div_periode` (`id`, `mois`, `start_week`, `end_week`) VALUES
(1, 2, '2026-02-09', '2026-02-15');

-- --------------------------------------------------------

--
-- Structure de la table `div_visiteurs`
--

DROP TABLE IF EXISTS `div_visiteurs`;
CREATE TABLE IF NOT EXISTS `div_visiteurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Adresse IP de l''utilisateur ipv4/ipv6',
  `date` date NOT NULL COMMENT 'la date de connexion',
  `heure` time NOT NULL COMMENT 'heure de connexion',
  `jour` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Contient le jour en francais',
  `mois` int NOT NULL COMMENT 'Le numéro du mois',
  `year` year NOT NULL COMMENT 'L''année',
  `lang` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'la langue de l''utilisateur fr pour francais, en pour anglais uniquement',
  `pays` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Le pays lié à l''IP (obtenu via une api )',
  `ville` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'La ville aussi par rapport à son IP',
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Utilisateur inconnu' COMMENT 'Le nom complet de l''utilisateur connecté (par utilisateur, o, parle de client et non d''administrateur) si est connecté, sinon, un texte par défaut est prévu !',
  `countryCode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `div_visiteurs`
--

INSERT INTO `div_visiteurs` (`id`, `ip`, `date`, `heure`, `jour`, `mois`, `year`, `lang`, `pays`, `ville`, `nom`, `countryCode`) VALUES
(1, '102.244.197.63', '2026-02-11', '21:33:01', 'Mercredi', 2, '2026', 'fr', 'Cameroon', 'Littoral, Douala', 'Utilisateur inconnu', 'CM'),
(2, '102.244.197.63', '2026-02-13', '23:32:40', 'Vendredi', 2, '2026', 'fr', 'Pays inconnu', ', ', 'Utilisateur inconnu', 'CA'),
(3, '102.244.197.63', '2026-02-14', '04:33:21', 'Samedi', 2, '2026', 'fr', 'Cameroon', 'Littoral', 'InnovTeam', 'CM');

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` int UNSIGNED NOT NULL,
  `evaluateur_type` enum('client','chauffeur') COLLATE utf8mb4_general_ci NOT NULL,
  `note` tinyint NOT NULL,
  `commentaire` text COLLATE utf8mb4_general_ci,
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ;

-- --------------------------------------------------------

--
-- Structure de la table `grille_tarifs`
--

DROP TABLE IF EXISTS `grille_tarifs`;
CREATE TABLE IF NOT EXISTS `grille_tarifs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `dist_min` int UNSIGNED NOT NULL COMMENT 'Distance min en mètres',
  `dist_max` int UNSIGNED NOT NULL COMMENT 'Distance max en mètres',
  `prix_base` int UNSIGNED NOT NULL COMMENT 'Prix de base en FCFA',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_dist` (`dist_min`,`dist_max`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Grille tarifaire par tranche de distance';

--
-- Déchargement des données de la table `grille_tarifs`
--

INSERT INTO `grille_tarifs` (`id`, `dist_min`, `dist_max`, `prix_base`, `actif`) VALUES
(1, 0, 199, 100, 1),
(2, 200, 1200, 200, 1),
(3, 1201, 2000, 250, 1),
(4, 2001, 3000, 350, 1),
(5, 3001, 4000, 450, 1),
(6, 4001, 6000, 600, 1),
(7, 6001, 9000, 1000, 1);

-- --------------------------------------------------------

--
-- Structure de la table `kyc`
--

DROP TABLE IF EXISTS `kyc`;
CREATE TABLE IF NOT EXISTS `kyc` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `chauffeur_id` int UNSIGNED NOT NULL,
  `photo_chauffeur` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cni_recto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cni_verso` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cni_numero` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cni_date_expiry` date DEFAULT NULL,
  `pays` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Cameroun',
  `ville` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quartier` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `adresse_complete` text COLLATE utf8mb4_general_ci,
  `domicile_latitude` decimal(10,8) DEFAULT NULL,
  `domicile_longitude` decimal(11,8) DEFAULT NULL,
  `statut` enum('soumis','en_cours','approuve','rejete') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'soumis',
  `commentaire_admin` text COLLATE utf8mb4_general_ci,
  `soumis_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verifie_le` datetime DEFAULT NULL,
  `verifie_par` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chauffeur_id` (`chauffeur_id`),
  KEY `idx_kyc_statut` (`statut`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `kyc`
--

INSERT INTO `kyc` (`id`, `chauffeur_id`, `photo_chauffeur`, `cni_recto`, `cni_verso`, `cni_numero`, `cni_date_expiry`, `pays`, `ville`, `quartier`, `adresse_complete`, `domicile_latitude`, `domicile_longitude`, `statut`, `commentaire_admin`, `soumis_le`, `verifie_le`, `verifie_par`) VALUES
(1, 1, 'uploads/kyc/1/photo_chauffeur_698f866c556ee.png', 'uploads/kyc/1/cni_recto_698f866c56e59.png', 'uploads/kyc/1/cni_verso_698f866c57e4c.png', '192221981', '2029-10-19', 'Cameroun', 'Douala', 'Pk10 - Génie Militaire', NULL, 3.85879754, 11.52166973, 'approuve', NULL, '2026-02-13 21:15:40', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `chauffeur_id` int UNSIGNED NOT NULL,
  `type` enum('kyc_approuve','kyc_rejete','nouvelle_course','course_annulee','message') COLLATE utf8mb4_general_ci NOT NULL,
  `titre` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `lue` tinyint(1) NOT NULL DEFAULT '0',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chauffeur_id` (`chauffeur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plans_facteurs`
--

DROP TABLE IF EXISTS `plans_facteurs`;
CREATE TABLE IF NOT EXISTS `plans_facteurs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_plan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `facteur` decimal(4,2) NOT NULL DEFAULT '1.00',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `position` tinyint NOT NULL DEFAULT '0' COMMENT 'Ordre d affichage',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `plans_facteurs`
--

INSERT INTO `plans_facteurs` (`id`, `nom_plan`, `slug`, `facteur`, `actif`, `position`) VALUES
(1, 'Classique', 'classique', 1.00, 1, 1),
(2, 'Prestige', 'prestige', 1.50, 1, 2),
(3, 'Prestige Plus', 'prestige_plus', 1.75, 1, 3);

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

DROP TABLE IF EXISTS `vehicules`;
CREATE TABLE IF NOT EXISTS `vehicules` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `chauffeur_id` int UNSIGNED NOT NULL,
  `marque` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `modele` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `annee` year NOT NULL,
  `couleur` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `immatriculation` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_places` tinyint NOT NULL DEFAULT '4',
  `type_vehicule` enum('berline','suv','minibus','moto','voiture taxi','voiture personnelle') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'berline',
  `carte_grise` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `assurance` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `assurance_expiry` date DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `immatriculation` (`immatriculation`),
  KEY `chauffeur_id` (`chauffeur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `kyc`
--
ALTER TABLE `kyc`
  ADD CONSTRAINT `kyc_ibfk_1` FOREIGN KEY (`chauffeur_id`) REFERENCES `chauffeurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`chauffeur_id`) REFERENCES `chauffeurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (`chauffeur_id`) REFERENCES `chauffeurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
