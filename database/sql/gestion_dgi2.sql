-- ============================================================
-- Gestion DGI2 — Base de données complète (structure + données de démonstration)
--
-- Généré depuis les migrations et seeders Laravel (php artisan migrate --seed).
-- Import : phpMyAdmin (onglet Importer) ou
--   mysql -u root < database/sql/gestion_dgi2.sql
--
-- ATTENTION : supprime puis recrée toutes les tables de la base `gestion_dgi2`.
-- Comptes de démonstration : mot de passe « password » (voir README).
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gestion_dgi2` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gestion_dgi2`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `bureaux_ordre`;
CREATE TABLE `bureaux_ordre` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `unite_id` bigint(20) unsigned DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bureaux_ordre_code_unique` (`code`),
  KEY `bureaux_ordre_unite_id_foreign` (`unite_id`),
  CONSTRAINT `bureaux_ordre_unite_id_foreign` FOREIGN KEY (`unite_id`) REFERENCES `unites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `categories_courrier`;
CREATE TABLE `categories_courrier` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `necessite_reponse` tinyint(1) NOT NULL DEFAULT 1,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_courrier_nom_unique` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `categories_courrier` (`id`, `nom`, `description`, `necessite_reponse`, `actif`, `created_at`, `updated_at`) VALUES (1,'Attestation',NULL,1,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `categories_courrier` (`id`, `nom`, `description`, `necessite_reponse`, `actif`, `created_at`, `updated_at`) VALUES (2,'Réclamation',NULL,1,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `categories_courrier` (`id`, `nom`, `description`, `necessite_reponse`, `actif`, `created_at`, `updated_at`) VALUES (3,'Demande',NULL,1,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `categories_courrier` (`id`, `nom`, `description`, `necessite_reponse`, `actif`, `created_at`, `updated_at`) VALUES (4,'Recours',NULL,1,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
DROP TABLE IF EXISTS `contribuables`;
CREATE TABLE `contribuables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom_raison_sociale` varchar(255) NOT NULL,
  `type_contribuable` varchar(40) DEFAULT NULL,
  `ifu` varchar(20) DEFAULT NULL,
  `cin` varchar(20) DEFAULT NULL,
  `ice` varchar(20) DEFAULT NULL,
  `numero_tp` varchar(30) DEFAULT NULL,
  `numero_th` varchar(30) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contribuables_ifu_unique` (`ifu`),
  KEY `contribuables_nom_raison_sociale_index` (`nom_raison_sociale`),
  KEY `contribuables_cin_index` (`cin`),
  KEY `contribuables_ice_index` (`ice`),
  KEY `contribuables_numero_tp_index` (`numero_tp`),
  KEY `contribuables_numero_th_index` (`numero_th`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (1,'Alaoui Mohammed',NULL,'123456789012345','AB123456',NULL,NULL,'TH-458921','Quartier Gueliz, Avenue Mohammed V, N° 12','Marrakech','0661234567','m.alaoui@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (2,'Benjelloun Fatima',NULL,'234567890123456','CD789012',NULL,NULL,'TH-782341','Hay Riad, Rue des Oliviers, N° 45','Rabat','0672345678','fatima.benjelloun@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (3,'SARL Atlas Distribution',NULL,'345678901234567',NULL,'001234567890123','TP-10234',NULL,'Zone Industrielle Sidi Ghanem, Lot 28','Marrakech','0524432187','contact@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (4,'EURL Maroc Services Plus',NULL,'456789012345678',NULL,'001987654321098','TP-20567',NULL,'Boulevard Zerktouni, Immeuble Al Amal','Casablanca','0522987654','info@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (5,'Tazi Karim',NULL,'567890123456789','EF345678',NULL,NULL,'TH-334512','Hay Mohammadi, Rue Ibn Khaldoun, N° 8','Fès','0653456789','karim.tazi@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (6,'SA Immobilier du Sud',NULL,'678901234567890',NULL,'001456789012345','TP-30112',NULL,'Avenue Hassan II, Résidence Al Bahja','Agadir','0528334455','direction@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (7,'Idrissi Amina',NULL,'789012345678901','GH901234',NULL,NULL,'TH-556789','Quartier Médina, Derb Sidi Bel Abbès, N° 3','Meknès','0664567890','amina.idrissi@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (8,'SNC Transport Nord Express',NULL,'890123456789012',NULL,'001321654987654','TP-40889',NULL,'Route de Tanger, Km 5, Zone Logistique','Tanger','0539943210','logistique@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (9,'Chraibi Youssef',NULL,'901234567890123','IJ567890',NULL,NULL,'TH-667234','Hay Salam, Avenue Al Massira, N° 102','Oujda','0675678901','youssef.chraibi@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `contribuables` (`id`, `nom_raison_sociale`, `type_contribuable`, `ifu`, `cin`, `ice`, `numero_tp`, `numero_th`, `adresse`, `ville`, `telephone`, `email`, `created_at`, `updated_at`) VALUES (10,'Groupe Ouarzazate Trading',NULL,'012345678901234',NULL,'001789012345678','TP-50234',NULL,'Avenue Moulay Abdellah, Complexe Commercial N° 7','Ouarzazate','0524887766','commercial@example.com','2026-10-08 21:41:45','2026-10-08 21:41:45');
DROP TABLE IF EXISTS `courrier_annees`;
CREATE TABLE `courrier_annees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `courrier_id` bigint(20) unsigned NOT NULL,
  `annee` smallint(5) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courrier_annees_courrier_id_annee_unique` (`courrier_id`,`annee`),
  KEY `courrier_annees_courrier_id_index` (`courrier_id`),
  CONSTRAINT `courrier_annees_courrier_id_foreign` FOREIGN KEY (`courrier_id`) REFERENCES `courriers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (1,1,2024,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (2,1,2025,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (3,2,2024,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (4,3,2023,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (5,3,2024,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (6,4,2025,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (7,5,2024,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (8,6,2024,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (9,6,2025,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (10,7,2025,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (11,8,2023,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (12,9,2025,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (13,10,2024,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (14,11,2025,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (15,12,2025,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (16,13,2024,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (17,14,2025,'2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (18,15,2024,'2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `courrier_annees` (`id`, `courrier_id`, `annee`, `created_at`, `updated_at`) VALUES (19,16,2025,'2026-10-08 21:41:46','2026-10-08 21:41:46');
DROP TABLE IF EXISTS `courrier_historiques`;
CREATE TABLE `courrier_historiques` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `courrier_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `pour_compte_de_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(30) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courrier_historiques_user_id_foreign` (`user_id`),
  KEY `courrier_historiques_courrier_id_created_at_index` (`courrier_id`,`created_at`),
  KEY `courrier_historiques_pour_compte_de_id_foreign` (`pour_compte_de_id`),
  CONSTRAINT `courrier_historiques_courrier_id_foreign` FOREIGN KEY (`courrier_id`) REFERENCES `courriers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courrier_historiques_pour_compte_de_id_foreign` FOREIGN KEY (`pour_compte_de_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courrier_historiques_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (1,1,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (2,2,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (3,3,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (4,4,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (5,5,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (6,6,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (7,7,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (8,8,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (9,9,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (10,10,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (11,11,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (12,12,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (13,13,1,NULL,'depot',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (14,14,1,NULL,'depot',NULL,'2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (15,15,1,NULL,'depot',NULL,'2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `courrier_historiques` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `action`, `commentaire`, `created_at`, `updated_at`) VALUES (16,16,1,NULL,'depot',NULL,'2026-10-08 21:41:46','2026-10-08 21:41:46');
DROP TABLE IF EXISTS `courrier_impots`;
CREATE TABLE `courrier_impots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `courrier_id` bigint(20) unsigned NOT NULL,
  `impot` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courrier_impots_courrier_id_impot_unique` (`courrier_id`,`impot`),
  KEY `courrier_impots_courrier_id_index` (`courrier_id`),
  CONSTRAINT `courrier_impots_courrier_id_foreign` FOREIGN KEY (`courrier_id`) REFERENCES `courriers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (1,1,'IR','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (2,2,'IR','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (3,2,'TVA','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (4,3,'IR','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (5,4,'TP','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (6,5,'IR','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (7,6,'IS','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (8,7,'TVA','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (9,8,'IR','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (10,8,'IS','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (11,9,'IR','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (12,10,'TVA','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (13,11,'TP','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (14,12,'TP','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (15,13,'IR','2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (16,14,'IR','2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (17,15,'IS','2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `courrier_impots` (`id`, `courrier_id`, `impot`, `created_at`, `updated_at`) VALUES (18,16,'TVA','2026-10-08 21:41:46','2026-10-08 21:41:46');
DROP TABLE IF EXISTS `courrier_sequences`;
CREATE TABLE `courrier_sequences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `annee` smallint(5) unsigned NOT NULL,
  `unite_id` bigint(20) unsigned NOT NULL,
  `dernier_numero` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courrier_sequences_annee_unite_id_unique` (`annee`,`unite_id`),
  KEY `courrier_sequences_unite_id_foreign` (`unite_id`),
  CONSTRAINT `courrier_sequences_unite_id_foreign` FOREIGN KEY (`unite_id`) REFERENCES `unites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `courrier_sequences` (`id`, `annee`, `unite_id`, `dernier_numero`, `created_at`, `updated_at`) VALUES (1,2026,1,3,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_sequences` (`id`, `annee`, `unite_id`, `dernier_numero`, `created_at`, `updated_at`) VALUES (2,2026,3,3,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_sequences` (`id`, `annee`, `unite_id`, `dernier_numero`, `created_at`, `updated_at`) VALUES (3,2026,4,3,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_sequences` (`id`, `annee`, `unite_id`, `dernier_numero`, `created_at`, `updated_at`) VALUES (4,2026,2,3,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `courrier_sequences` (`id`, `annee`, `unite_id`, `dernier_numero`, `created_at`, `updated_at`) VALUES (5,2026,5,2,'2026-10-08 21:41:45','2026-10-08 21:41:46');
INSERT INTO `courrier_sequences` (`id`, `annee`, `unite_id`, `dernier_numero`, `created_at`, `updated_at`) VALUES (6,2026,6,2,'2026-10-08 21:41:46','2026-10-08 21:41:46');
DROP TABLE IF EXISTS `courriers`;
CREATE TABLE `courriers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) NOT NULL,
  `contribuable_id` bigint(20) unsigned NOT NULL,
  `categorie_courrier_id` bigint(20) unsigned NOT NULL,
  `type_courrier_id` bigint(20) unsigned NOT NULL,
  `objet` varchar(255) DEFAULT NULL,
  `necessite_reponse` tinyint(1) NOT NULL DEFAULT 1,
  `unite_destinataire_id` bigint(20) unsigned NOT NULL,
  `code_unite` varchar(50) NOT NULL,
  `annee_enregistrement` smallint(5) unsigned NOT NULL,
  `sequence` int(10) unsigned NOT NULL,
  `remarque` text DEFAULT NULL,
  `statut` varchar(30) NOT NULL DEFAULT 'depose',
  `date_depot` timestamp NOT NULL DEFAULT current_timestamp(),
  `priorite` varchar(255) NOT NULL DEFAULT 'Moyenne',
  `date_reception` date DEFAULT NULL,
  `heure_reception` time DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unite_actuelle_id` bigint(20) unsigned DEFAULT NULL,
  `responsable_actuel_id` bigint(20) unsigned DEFAULT NULL,
  `responsable_reception_id` bigint(20) unsigned DEFAULT NULL,
  `date_traitement` date DEFAULT NULL,
  `traite_par_id` bigint(20) unsigned DEFAULT NULL,
  `document_genere` tinyint(1) DEFAULT NULL,
  `motif_rejet` text DEFAULT NULL,
  `motif_suspension` text DEFAULT NULL,
  `unite_retour_id` bigint(20) unsigned DEFAULT NULL,
  `date_delivrance` date DEFAULT NULL,
  `delivre_par_id` bigint(20) unsigned DEFAULT NULL,
  `mode_livraison` varchar(30) DEFAULT NULL,
  `mandataire_piece` varchar(100) DEFAULT NULL,
  `mandataire_nom` varchar(255) DEFAULT NULL,
  `mandataire_prenom` varchar(255) DEFAULT NULL,
  `mandataire_cin` varchar(50) DEFAULT NULL,
  `date_procuration` date DEFAULT NULL,
  `postal_cin` varchar(50) DEFAULT NULL,
  `postal_nom` varchar(255) DEFAULT NULL,
  `reference_envoi` varchar(100) DEFAULT NULL,
  `numero_depart` varchar(40) DEFAULT NULL,
  `annee_depart` smallint(5) unsigned DEFAULT NULL,
  `sequence_depart` int(10) unsigned DEFAULT NULL,
  `code_unite_depart` varchar(50) DEFAULT NULL,
  `date_annulation` timestamp NULL DEFAULT NULL,
  `motif_annulation` text DEFAULT NULL,
  `annule_par_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courriers_numero_unique` (`numero`),
  UNIQUE KEY `courriers_annee_code_unite_sequence_unique` (`annee_enregistrement`,`code_unite`,`sequence`),
  UNIQUE KEY `courriers_numero_depart_unique` (`numero_depart`),
  KEY `courriers_contribuable_id_foreign` (`contribuable_id`),
  KEY `courriers_categorie_courrier_id_foreign` (`categorie_courrier_id`),
  KEY `courriers_type_courrier_id_foreign` (`type_courrier_id`),
  KEY `courriers_unite_destinataire_id_foreign` (`unite_destinataire_id`),
  KEY `courriers_user_id_foreign` (`user_id`),
  KEY `courriers_annee_enregistrement_code_unite_index` (`annee_enregistrement`,`code_unite`),
  KEY `courriers_statut_index` (`statut`),
  KEY `courriers_date_depot_index` (`date_depot`),
  KEY `courriers_unite_actuelle_id_foreign` (`unite_actuelle_id`),
  KEY `courriers_responsable_actuel_id_foreign` (`responsable_actuel_id`),
  KEY `courriers_responsable_reception_id_foreign` (`responsable_reception_id`),
  KEY `courriers_traite_par_id_foreign` (`traite_par_id`),
  KEY `courriers_unite_retour_id_foreign` (`unite_retour_id`),
  KEY `courriers_delivre_par_id_foreign` (`delivre_par_id`),
  KEY `courriers_annule_par_id_foreign` (`annule_par_id`),
  CONSTRAINT `courriers_annule_par_id_foreign` FOREIGN KEY (`annule_par_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courriers_categorie_courrier_id_foreign` FOREIGN KEY (`categorie_courrier_id`) REFERENCES `categories_courrier` (`id`),
  CONSTRAINT `courriers_contribuable_id_foreign` FOREIGN KEY (`contribuable_id`) REFERENCES `contribuables` (`id`),
  CONSTRAINT `courriers_delivre_par_id_foreign` FOREIGN KEY (`delivre_par_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courriers_responsable_actuel_id_foreign` FOREIGN KEY (`responsable_actuel_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courriers_responsable_reception_id_foreign` FOREIGN KEY (`responsable_reception_id`) REFERENCES `users` (`id`),
  CONSTRAINT `courriers_traite_par_id_foreign` FOREIGN KEY (`traite_par_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courriers_type_courrier_id_foreign` FOREIGN KEY (`type_courrier_id`) REFERENCES `types_courrier` (`id`),
  CONSTRAINT `courriers_unite_actuelle_id_foreign` FOREIGN KEY (`unite_actuelle_id`) REFERENCES `unites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courriers_unite_destinataire_id_foreign` FOREIGN KEY (`unite_destinataire_id`) REFERENCES `unites` (`id`),
  CONSTRAINT `courriers_unite_retour_id_foreign` FOREIGN KEY (`unite_retour_id`) REFERENCES `unites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `courriers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (1,'2026-000001-DR001',1,1,2,'Demande attestation de régularité fiscale',1,1,'DR001',2026,1,'Dossier complet','depose','2026-07-01 09:15:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:45','2026-10-08 21:41:45',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (2,'2026-000002-DR001',2,2,3,'Réclamation sur avis IR 2024',1,1,'DR001',2026,2,NULL,'receptionne','2026-07-03 10:30:00','Moyenne','2026-07-04','08:45:00',1,'2026-10-08 21:41:45','2026-10-08 21:41:45',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (3,'2026-000003-DR001',3,3,5,'Demande remise gracieuse pénalités',1,1,'DR001',2026,3,'Traitée favorablement','traite','2026-07-05 11:00:00','Moyenne','2026-07-06','09:00:00',1,'2026-10-08 21:41:45','2026-10-08 21:41:45',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (4,'2026-000001-SERV002',4,1,1,'Attestation inscription TP',1,3,'SERV002',2026,1,NULL,'depose','2026-07-07 08:20:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:45','2026-10-08 21:41:45',3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (5,'2026-000002-SERV002',5,4,7,'Recours gracieux IR',1,3,'SERV002',2026,2,'Réaffecté depuis bureau d\'ordre','reaffecte','2026-07-08 14:00:00','Moyenne','2026-07-09','10:30:00',1,'2026-10-08 21:41:45','2026-10-08 21:41:45',3,3,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (6,'2026-000003-SERV002',6,3,6,'Demande dégrèvement IS',1,3,'SERV002',2026,3,NULL,'receptionne','2026-07-10 09:45:00','Moyenne','2026-07-11','11:00:00',1,'2026-10-08 21:41:45','2026-10-08 21:41:45',3,3,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (7,'2026-000001-BRIG001',7,2,4,'Réclamation pénalités retard déclaration',1,4,'BRIG001',2026,1,NULL,'depose','2026-07-12 10:00:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:45','2026-10-08 21:41:45',4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (8,'2026-000002-BRIG001',8,4,8,'Recours hiérarchique contrôle fiscal',1,4,'BRIG001',2026,2,'Clôturé','traite','2026-07-13 11:30:00','Moyenne','2026-07-14','09:15:00',1,'2026-10-08 21:41:45','2026-10-08 21:41:45',4,4,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (9,'2026-000003-BRIG001',9,1,2,'Attestation régularité avant marché public',1,4,'BRIG001',2026,3,NULL,'depose','2026-07-15 08:00:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:45','2026-10-08 21:41:45',4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (10,'2026-000001-BO001',10,3,5,'Demande remise gracieuse TVA',1,2,'BO001',2026,1,NULL,'receptionne','2026-07-16 09:00:00','Moyenne','2026-07-16','14:00:00',1,'2026-10-08 21:41:45','2026-10-08 21:41:45',2,5,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (11,'2026-000002-BO001',1,2,3,'Réclamation avis taxe professionnelle',1,2,'BO001',2026,2,NULL,'depose','2026-07-17 10:15:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:45','2026-10-08 21:41:45',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (12,'2026-000003-BO001',2,1,1,'Attestation TP renouvellement',1,2,'BO001',2026,3,'Dossier traité','traite','2026-07-18 11:00:00','Moyenne','2026-07-19','08:30:00',1,'2026-10-08 21:41:45','2026-10-08 21:41:45',2,5,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (13,'2026-000001-SECT001',3,3,6,'Dégrèvement taxe d\'habitation Guéliz',1,5,'SECT001',2026,1,NULL,'depose','2026-07-20 09:30:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:45','2026-10-08 21:41:45',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (14,'2026-000002-SECT001',4,4,7,'Recours gracieux secteur Guéliz',1,5,'SECT001',2026,2,NULL,'depose','2026-07-21 10:00:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:46','2026-10-08 21:41:46',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (15,'2026-000001-BUR003',5,4,8,'Recours recouvrement amiable',1,6,'BUR003',2026,1,NULL,'depose','2026-07-22 08:45:00','Moyenne',NULL,NULL,1,'2026-10-08 21:41:46','2026-10-08 21:41:46',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `courriers` (`id`, `numero`, `contribuable_id`, `categorie_courrier_id`, `type_courrier_id`, `objet`, `necessite_reponse`, `unite_destinataire_id`, `code_unite`, `annee_enregistrement`, `sequence`, `remarque`, `statut`, `date_depot`, `priorite`, `date_reception`, `heure_reception`, `user_id`, `created_at`, `updated_at`, `unite_actuelle_id`, `responsable_actuel_id`, `responsable_reception_id`, `date_traitement`, `traite_par_id`, `document_genere`, `motif_rejet`, `motif_suspension`, `unite_retour_id`, `date_delivrance`, `delivre_par_id`, `mode_livraison`, `mandataire_piece`, `mandataire_nom`, `mandataire_prenom`, `mandataire_cin`, `date_procuration`, `postal_cin`, `postal_nom`, `reference_envoi`, `numero_depart`, `annee_depart`, `sequence_depart`, `code_unite_depart`, `date_annulation`, `motif_annulation`, `annule_par_id`) VALUES (16,'2026-000002-BUR003',6,2,4,'Réclamation pénalités recouvrement',1,6,'BUR003',2026,2,NULL,'receptionne','2026-07-23 09:00:00','Moyenne','2026-07-24','10:00:00',1,'2026-10-08 21:41:46','2026-10-08 21:41:46',6,7,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
DROP TABLE IF EXISTS `delegations`;
CREATE TABLE `delegations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agent_absent_id` bigint(20) unsigned NOT NULL,
  `agent_remplacant_id` bigint(20) unsigned NOT NULL,
  `unite_id` bigint(20) unsigned NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `motif` varchar(255) NOT NULL,
  `statut` varchar(255) NOT NULL DEFAULT 'active',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delegations_unite_id_foreign` (`unite_id`),
  KEY `delegations_created_by_foreign` (`created_by`),
  KEY `delegations_agent_remplacant_id_statut_index` (`agent_remplacant_id`,`statut`),
  KEY `delegations_agent_absent_id_statut_index` (`agent_absent_id`,`statut`),
  KEY `delegations_date_debut_date_fin_index` (`date_debut`,`date_fin`),
  CONSTRAINT `delegations_agent_absent_id_foreign` FOREIGN KEY (`agent_absent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delegations_agent_remplacant_id_foreign` FOREIGN KEY (`agent_remplacant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delegations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delegations_unite_id_foreign` FOREIGN KEY (`unite_id`) REFERENCES `unites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `depart_sequences`;
CREATE TABLE `depart_sequences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `annee` smallint(5) unsigned NOT NULL,
  `dernier_numero` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `depart_sequences_annee_unique` (`annee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `historique_affectations`;
CREATE TABLE `historique_affectations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `courrier_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `pour_compte_de_id` bigint(20) unsigned DEFAULT NULL,
  `unite_source_id` bigint(20) unsigned DEFAULT NULL,
  `unite_destination_id` bigint(20) unsigned DEFAULT NULL,
  `ancien_responsable_id` bigint(20) unsigned DEFAULT NULL,
  `nouveau_responsable_id` bigint(20) unsigned DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `action_type` varchar(255) DEFAULT 'reaffectation',
  `motif` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `historique_affectations_courrier_id_foreign` (`courrier_id`),
  KEY `historique_affectations_user_id_foreign` (`user_id`),
  KEY `historique_affectations_unite_source_id_foreign` (`unite_source_id`),
  KEY `historique_affectations_unite_destination_id_foreign` (`unite_destination_id`),
  KEY `historique_affectations_ancien_responsable_id_foreign` (`ancien_responsable_id`),
  KEY `historique_affectations_nouveau_responsable_id_foreign` (`nouveau_responsable_id`),
  KEY `historique_affectations_pour_compte_de_id_foreign` (`pour_compte_de_id`),
  CONSTRAINT `historique_affectations_ancien_responsable_id_foreign` FOREIGN KEY (`ancien_responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `historique_affectations_courrier_id_foreign` FOREIGN KEY (`courrier_id`) REFERENCES `courriers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historique_affectations_nouveau_responsable_id_foreign` FOREIGN KEY (`nouveau_responsable_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `historique_affectations_pour_compte_de_id_foreign` FOREIGN KEY (`pour_compte_de_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `historique_affectations_unite_destination_id_foreign` FOREIGN KEY (`unite_destination_id`) REFERENCES `unites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `historique_affectations_unite_source_id_foreign` FOREIGN KEY (`unite_source_id`) REFERENCES `unites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `historique_affectations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (1,1,1,NULL,NULL,1,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (2,2,1,NULL,NULL,1,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (3,3,1,NULL,NULL,1,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (4,4,1,NULL,NULL,3,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (5,5,1,NULL,NULL,3,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (6,6,1,NULL,NULL,3,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (7,7,1,NULL,NULL,4,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (8,8,1,NULL,NULL,4,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (9,9,1,NULL,NULL,4,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (10,10,1,NULL,NULL,2,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (11,11,1,NULL,NULL,2,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (12,12,1,NULL,NULL,2,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (13,13,1,NULL,NULL,5,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (14,14,1,NULL,NULL,5,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (15,15,1,NULL,NULL,6,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:46','2026-10-08 21:41:46');
INSERT INTO `historique_affectations` (`id`, `courrier_id`, `user_id`, `pour_compte_de_id`, `unite_source_id`, `unite_destination_id`, `ancien_responsable_id`, `nouveau_responsable_id`, `commentaire`, `action_type`, `motif`, `created_at`, `updated_at`) VALUES (16,16,1,NULL,NULL,6,NULL,NULL,'Dépôt initial du courrier',NULL,NULL,'2026-10-08 21:41:46','2026-10-08 21:41:46');
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_07_14_000001_create_unites_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_07_14_000002_create_contribuables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_07_14_000003_create_categories_courrier_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_07_14_000004_create_types_courrier_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_07_14_000005_create_courrier_sequences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_07_14_000006_create_courriers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_07_14_000007_create_courrier_impots_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_07_14_000008_create_courrier_annees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_07_14_000009_create_pieces_jointes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_07_14_000010_create_courrier_historiques_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_07_14_000011_add_tp_th_to_contribuables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_07_15_000001_add_unique_annee_unite_sequence_to_courriers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_07_15_000002_add_indexes_to_pilier1_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_07_15_000003_update_foreign_key_delete_rules',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_07_15_000004_add_indexes_to_contribuables_tp_th',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_07_15_100131_create_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_07_15_100158_add_role_and_unite_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_07_15_100252_create_historique_affectations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_07_15_100312_add_unite_actuelle_and_responsable_actuel_to_courriers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_07_16_084003_add_reception_columns_to_courriers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_07_16_084122_add_action_type_and_motif_to_historique_affectations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_08_03_100000_create_permissions_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_08_03_110000_create_permission_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_08_04_140000_create_delegations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_08_04_140100_add_pour_compte_de_id_to_historiques_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_08_05_110000_add_traitement_delivrance_to_courriers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_08_05_120000_ajustements_pilier3_tracabilite',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_08_06_100000_conformite_cdc_extensions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_08_06_110000_create_role_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_08_13_160000_aligner_delegations_cdc',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_08_13_170000_retirer_delegations_accueil_admin',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_08_13_171000_ajouter_delegations_aux_chefs_avec_droits_perso',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_10_07_100000_add_necessite_reponse_to_categories_courrier_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_10_07_110000_rendre_objet_facultatif_dans_courriers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_10_08_100000_retirer_mise_a_jour_auto_date_depot_courriers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_10_08_110000_unicite_sequence_par_code_unite_courriers',1);
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_role_role_id_permission_id_unique` (`role_id`,`permission_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (1,3,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (2,3,8,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (3,3,9,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (4,3,21,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (5,3,22,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (6,3,23,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (7,3,24,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (8,4,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (9,4,3,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (10,4,6,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (11,4,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (12,4,10,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (13,4,14,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (14,4,15,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (15,4,17,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (16,1,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (17,1,4,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (18,1,5,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (19,1,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (20,1,11,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (21,1,12,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (22,1,13,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (23,1,16,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (24,2,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (25,2,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (26,2,17,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (27,5,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (28,5,4,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (29,5,5,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (30,5,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (31,5,11,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (32,5,12,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (33,5,13,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (34,5,16,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (35,5,18,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (36,5,19,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (37,5,20,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (38,5,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (39,6,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (40,6,4,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (41,6,5,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (42,6,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (43,6,11,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (44,6,12,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (45,6,13,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (46,6,16,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (47,6,18,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (48,6,19,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (49,6,20,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (50,6,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (51,7,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (52,7,4,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (53,7,5,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (54,7,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (55,7,11,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (56,7,12,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (57,7,13,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (58,7,16,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (59,7,18,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (60,7,19,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (61,7,20,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (62,7,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (63,8,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (64,8,4,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (65,8,5,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (66,8,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (67,8,11,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (68,8,12,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (69,8,13,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (70,8,16,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (71,8,18,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (72,8,19,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (73,8,20,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (74,8,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (75,9,2,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (76,9,4,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (77,9,5,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (78,9,7,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (79,9,11,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (80,9,12,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (81,9,13,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (82,9,16,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (83,9,18,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (84,9,19,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (85,9,20,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permission_role` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES (86,9,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
DROP TABLE IF EXISTS `permission_user`;
CREATE TABLE `permission_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_user_user_id_permission_id_unique` (`user_id`,`permission_id`),
  KEY `permission_user_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `groupe` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (1,'delegations.annuler','Annuler une délégation','delegations','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (2,'menu.dashboard','Accueil','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (3,'menu.enregistrer_courrier','Enregistrer un courrier','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (4,'menu.reception','Réception','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (5,'menu.traitement','Traitement','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (6,'menu.delivrance','Délivrance','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (7,'menu.consultation','Statistiques','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (8,'menu.utilisateurs','Gestion des utilisateurs','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (9,'menu.referentiels','Référentiels','menu','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (10,'courriers.creer','Créer / importer un courrier','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (11,'courriers.receptionner','Confirmer une réception','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (12,'courriers.annuler_reception','Annuler une réception','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (13,'courriers.reaffecter','Réaffecter un courrier','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (14,'courriers.delivrer','Délivrer un document (Accueil)','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (15,'courriers.annuler_depot','Annuler un dépôt','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (16,'courriers.refuser_reception','Refuser une réception (retour B.O.)','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (17,'stats.exporter','Exporter les statistiques','courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (18,'delegations.voir','Consulter les délégations','delegations','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (19,'delegations.creer','Créer une délégation','delegations','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (20,'delegations.modifier','Modifier une délégation planifiée','delegations','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (21,'users.gerer','Gérer les utilisateurs','administration','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (22,'categories.gerer','Gérer les catégories','administration','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (23,'types_courrier.gerer','Gérer les types de courrier','administration','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `permissions` (`id`, `code`, `label`, `groupe`, `created_at`, `updated_at`) VALUES (24,'unites.gerer','Gérer les unités','administration','2026-10-08 21:41:42','2026-10-08 21:41:42');
DROP TABLE IF EXISTS `pieces_jointes`;
CREATE TABLE `pieces_jointes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `courrier_id` bigint(20) unsigned NOT NULL,
  `nom_original` varchar(255) NOT NULL,
  `nom_stockage` varchar(255) NOT NULL,
  `chemin` varchar(255) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `taille` bigint(20) unsigned NOT NULL,
  `ordre` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pieces_jointes_courrier_id_ordre_index` (`courrier_id`,`ordre`),
  CONSTRAINT `pieces_jointes_courrier_id_foreign` FOREIGN KEY (`courrier_id`) REFERENCES `courriers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_user_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (1,'Traitement','Profil CDC — traitement des courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (2,'Consultation','Profil CDC — consultation et statistiques','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (3,'Admin','Gère les utilisateurs, rôles, catégories, types de courrier, unités administratives et paramètres','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (4,'Accueil','Guichet d\'accueil — enregistrement et orientation des courriers','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (5,'Service','Gère un service','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (6,'Brigade','Gère une brigade','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (7,'Bureau','Gère un bureau','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (8,'Section','Gère une section','2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES (9,'Secteur','Gère un secteur','2026-10-08 21:41:42','2026-10-08 21:41:42');
DROP TABLE IF EXISTS `types_courrier`;
CREATE TABLE `types_courrier` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `categorie_courrier_id` bigint(20) unsigned NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `types_courrier_categorie_courrier_id_nom_unique` (`categorie_courrier_id`,`nom`),
  CONSTRAINT `types_courrier_categorie_courrier_id_foreign` FOREIGN KEY (`categorie_courrier_id`) REFERENCES `categories_courrier` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (1,1,'Attestation d\'inscription à la TP',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (2,1,'Attestation de régularité fiscale',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (3,2,'Réclamation sur avis d\'imposition',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (4,2,'Réclamation sur pénalités',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (5,3,'Demande de remise gracieuse',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (6,3,'Demande de dégrèvement',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (7,4,'Recours gracieux',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `types_courrier` (`id`, `categorie_courrier_id`, `nom`, `description`, `actif`, `created_at`, `updated_at`) VALUES (8,4,'Recours hiérarchique',NULL,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
DROP TABLE IF EXISTS `unites`;
CREATE TABLE `unites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type_unite` varchar(50) NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ville` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `est_bureau_ordre` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unites_code_unique` (`code`),
  KEY `unites_type_unite_index` (`type_unite`),
  KEY `unites_parent_id_index` (`parent_id`),
  CONSTRAINT `unites_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `unites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `unites` (`id`, `nom`, `code`, `type_unite`, `parent_id`, `description`, `ville`, `actif`, `est_bureau_ordre`, `created_at`, `updated_at`) VALUES (1,'Direction Régionale des Impôts de Marrakech','DR001','DR',NULL,'Direction Régionale racine',NULL,1,0,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `unites` (`id`, `nom`, `code`, `type_unite`, `parent_id`, `description`, `ville`, `actif`, `est_bureau_ordre`, `created_at`, `updated_at`) VALUES (2,'Bureau d\'ordre','BO001','Bureau',1,'Bureau d\'ordre central','Marrakech',1,1,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `unites` (`id`, `nom`, `code`, `type_unite`, `parent_id`, `description`, `ville`, `actif`, `est_bureau_ordre`, `created_at`, `updated_at`) VALUES (3,'Service IT','SERV002','Service',1,'Service informatique',NULL,1,0,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `unites` (`id`, `nom`, `code`, `type_unite`, `parent_id`, `description`, `ville`, `actif`, `est_bureau_ordre`, `created_at`, `updated_at`) VALUES (4,'Brigade de vérification','BRIG001','Brigade',1,NULL,NULL,1,0,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `unites` (`id`, `nom`, `code`, `type_unite`, `parent_id`, `description`, `ville`, `actif`, `est_bureau_ordre`, `created_at`, `updated_at`) VALUES (5,'Secteur fiscal Guéliz','SECT001','Secteur',3,NULL,NULL,1,0,'2026-10-08 21:41:42','2026-10-08 21:41:42');
INSERT INTO `unites` (`id`, `nom`, `code`, `type_unite`, `parent_id`, `description`, `ville`, `actif`, `est_bureau_ordre`, `created_at`, `updated_at`) VALUES (6,'Bureau de recouvrement','BUR003','Bureau',2,NULL,NULL,1,0,'2026-10-08 21:41:42','2026-10-08 21:41:42');
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ppr` varchar(30) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `prenom` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `unite_id` bigint(20) unsigned DEFAULT NULL,
  `bureau_ordre_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_ppr_unique` (`ppr`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_unite_id_foreign` (`unite_id`),
  KEY `users_bureau_ordre_id_foreign` (`bureau_ordre_id`),
  CONSTRAINT `users_bureau_ordre_id_foreign` FOREIGN KEY (`bureau_ordre_id`) REFERENCES `bureaux_ordre` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_unite_id_foreign` FOREIGN KEY (`unite_id`) REFERENCES `unites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (1,NULL,'Admin System',NULL,'admin@dgi.ma','2026-10-08 21:41:43','$2y$12$P18z47N1BrMRbIl01RgLW.1H7ZKZyMF11n1C7qn8garupS.d4im2C',NULL,'2026-10-08 21:41:43','2026-10-08 21:41:43',3,1,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (2,NULL,'Agent Accueil',NULL,'accueil@dgi.ma','2026-10-08 21:41:43','$2y$12$zsL1Hhx0hc.2NMp3jlEIwOc0Y61myGIi6NaIFZyI38tfMfO0oWTHi',NULL,'2026-10-08 21:41:43','2026-10-08 21:41:43',4,2,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (3,NULL,'Chef de Service IT',NULL,'service.it@dgi.ma','2026-10-08 21:41:43','$2y$12$h.dKxmBpJQ/Vxs1K.jNkROPdhQa2XhVKSEuGpUFg0POguvsx2eD9m',NULL,'2026-10-08 21:41:43','2026-10-08 21:41:43',5,3,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (4,NULL,'Chef de Brigade',NULL,'brigade@dgi.ma','2026-10-08 21:41:43','$2y$12$YAXnwvGpACcijoCSqj9Jy.Hlpu2GQV0ov6/XB9nQpUyJZOdHkpVLO',NULL,'2026-10-08 21:41:43','2026-10-08 21:41:43',6,4,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (5,NULL,'Chef de Bureau',NULL,'bureau@dgi.ma','2026-10-08 21:41:44','$2y$12$9OBc81WGrqp8JIzm0sSbwuUnYEw7aEHkuCLRSIIvilmaZG9ocJWYq',NULL,'2026-10-08 21:41:44','2026-10-08 21:41:44',7,2,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (6,NULL,'Chef de Secteur Guéliz',NULL,'secteur.gueliz@dgi.ma','2026-10-08 21:41:44','$2y$12$yCJONob9w42fzk6XWCRg0eGFVkJVTD0SwTuFd2BbbUB3IOPneTgce',NULL,'2026-10-08 21:41:44','2026-10-08 21:41:44',9,5,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (7,NULL,'Chef Bureau Recouvrement',NULL,'recouvrement@dgi.ma','2026-10-08 21:41:44','$2y$12$1r8VhSPORJl06bzmUbB9bee0UaloBrPrqEm44rTSPwO6LqnWLejGW',NULL,'2026-10-08 21:41:44','2026-10-08 21:41:44',7,6,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (8,NULL,'Agent Bureau 1',NULL,'agent.bureau1@dgi.ma','2026-10-08 21:41:44','$2y$12$R.hgqpVwWAan.BV66ZFwaOmc5IXKdYrW1ekGSMA1CoELSST15Z/JS',NULL,'2026-10-08 21:41:44','2026-10-08 21:41:44',1,2,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (9,NULL,'Agent Bureau 2',NULL,'agent.bureau2@dgi.ma','2026-10-08 21:41:45','$2y$12$1qHRWg.dMuq9dXVdvf6rr.gK5fZKWY9gF/3BMFZUf70JqhgY1Q/k2',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45',1,2,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (10,NULL,'Agent Service 1',NULL,'agent.service1@dgi.ma','2026-10-08 21:41:45','$2y$12$MiNc4xmPWWYfYtmrfGvYMOf3IP3AA1rIcCQS6QkF.K8FbSNIbb9Wu',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45',1,3,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (11,NULL,'Agent Brigade 1',NULL,'agent.brigade1@dgi.ma','2026-10-08 21:41:45','$2y$12$GlWW.jj4m/IA7P5.c/0hI.Q4o8YJS2aDNG7RUTBGF6zl8DIh80R9i',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45',1,4,NULL);
INSERT INTO `users` (`id`, `ppr`, `name`, `prenom`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `unite_id`, `bureau_ordre_id`) VALUES (12,NULL,'Agent Section 1',NULL,'agent.section1@dgi.ma','2026-10-08 21:41:45','$2y$12$lHZWJxTlZ.IiD.NkDjPLtez3x7D3s2vn12kvR23mYj6CNsvjq3IrO',NULL,'2026-10-08 21:41:45','2026-10-08 21:41:45',1,3,NULL);

SET FOREIGN_KEY_CHECKS = 1;
