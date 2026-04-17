-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 17 avr. 2026 à 12:54
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `immogo`
--

-- --------------------------------------------------------

--
-- Structure de la table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `model_type`, `model_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:13:50', '2026-04-10 10:13:50'),
(2, 1, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:14:08', '2026-04-10 10:14:08'),
(3, 1, 'agence_created', 'Agence créée : az', 'App\\Models\\Agence', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:15:27', '2026-04-10 10:15:27'),
(4, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:15:35', '2026-04-10 10:15:35'),
(5, 4, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:15:58', '2026-04-10 10:15:58'),
(6, 4, 'bien_created', 'Bien créé : sdfghjk', 'App\\Models\\Bien', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:19:09', '2026-04-10 10:19:09'),
(7, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:27:03', '2026-04-10 10:27:03'),
(8, 3, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:27:53', '2026-04-10 10:27:53'),
(9, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 10:39:26', '2026-04-10 10:39:26'),
(10, 4, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:27:14', '2026-04-10 11:27:14'),
(11, 4, 'bien_created', 'Bien créé : sdfghjk', 'App\\Models\\Bien', 7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 11:28:27', '2026-04-10 11:28:27'),
(12, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:04:43', '2026-04-10 12:04:43'),
(13, 4, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:04:53', '2026-04-10 12:04:53'),
(14, 4, 'bien_created', 'Bien créé : Maison luxe', 'App\\Models\\Bien', 8, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:07:03', '2026-04-10 12:07:03'),
(15, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:08:42', '2026-04-10 12:08:42'),
(16, 1, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:09:31', '2026-04-10 12:09:31'),
(17, 1, 'agence_created', 'Agence créée : Paris Immo', 'App\\Models\\Agence', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:14:36', '2026-04-10 12:14:36'),
(18, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:14:49', '2026-04-10 12:14:49'),
(19, 7, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:15:10', '2026-04-10 12:15:10'),
(20, 7, 'bien_created', 'Bien créé : Appartement pour riche', 'App\\Models\\Bien', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-10 12:19:57', '2026-04-10 12:19:57'),
(21, 6, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 11:02:15', '2026-04-11 11:02:15'),
(22, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 12:50:27', '2026-04-11 12:50:27'),
(23, 1, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-11 12:54:40', '2026-04-11 12:54:40'),
(24, 1, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 11:33:10', '2026-04-12 11:33:10'),
(25, 1, 'agence_created', 'Agence créée : Apm', 'App\\Models\\Agence', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 11:35:18', '2026-04-12 11:35:18'),
(26, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 11:35:32', '2026-04-12 11:35:32'),
(27, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 11:35:47', '2026-04-12 11:35:47'),
(28, 8, 'agence_updated', 'Paramètres agence mis à jour : Apm', 'App\\Models\\Agence', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 11:36:42', '2026-04-12 11:36:42'),
(29, 8, 'bien_created', 'Bien créé : Test kkiapay', 'App\\Models\\Bien', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 11:38:04', '2026-04-12 11:38:04'),
(30, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 11:38:12', '2026-04-12 11:38:12'),
(31, 6, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 12:05:03', '2026-04-12 12:05:03'),
(32, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 12:05:51', '2026-04-12 12:05:51'),
(33, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 12:06:16', '2026-04-12 12:06:16'),
(34, 8, 'bien_statut', 'Statut bien \"Test kkiapay\" → disponible', 'App\\Models\\Bien', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 12:06:34', '2026-04-12 12:06:34'),
(35, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 18:32:58', '2026-04-12 18:32:58'),
(36, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 18:33:10', '2026-04-12 18:33:10'),
(37, 8, 'bien_statut', 'Statut bien \"Test kkiapay\" → disponible', 'App\\Models\\Bien', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 18:33:25', '2026-04-12 18:33:25'),
(38, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 18:33:29', '2026-04-12 18:33:29'),
(39, 9, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-04-12 18:33:39', '2026-04-12 18:33:39'),
(40, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 12:27:31', '2026-04-14 12:27:31'),
(41, 8, 'bien_statut', 'Statut bien \"Test kkiapay\" → disponible', 'App\\Models\\Bien', 10, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 12:27:42', '2026-04-14 12:27:42'),
(42, 4, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 15:08:24', '2026-04-14 15:08:24'),
(43, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 15:08:29', '2026-04-14 15:08:29'),
(44, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 15:09:00', '2026-04-14 15:09:00'),
(45, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 15:09:25', '2026-04-14 15:09:25'),
(46, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 15:09:41', '2026-04-14 15:09:41'),
(47, 8, 'bien_created', 'Bien créé : Sergio AHOSSAN', 'App\\Models\\Bien', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-14 15:10:47', '2026-04-14 15:10:47'),
(48, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-15 10:02:02', '2026-04-15 10:02:02'),
(49, 8, 'bien_statut', 'Statut bien \"Sergio AHOSSAN\" → disponible', 'App\\Models\\Bien', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-15 10:02:12', '2026-04-15 10:02:12'),
(50, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:18:34', '2026-04-17 09:18:34'),
(51, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:24:45', '2026-04-17 09:24:45'),
(52, 8, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:28:20', '2026-04-17 09:28:20'),
(53, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:30:51', '2026-04-17 09:30:51'),
(54, 1, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:31:24', '2026-04-17 09:31:24'),
(55, 1, 'agence_created', 'Agence créée : ImmoPlus', 'App\\Models\\Agence', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:34:41', '2026-04-17 09:34:41'),
(56, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:34:45', '2026-04-17 09:34:45'),
(57, 13, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:35:59', '2026-04-17 09:35:59'),
(58, 13, 'bien_created', 'Bien créé : Maison', 'App\\Models\\Bien', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:42:22', '2026-04-17 09:42:22'),
(59, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:42:40', '2026-04-17 09:42:40'),
(60, 12, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:42:48', '2026-04-17 09:42:48'),
(61, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:42:58', '2026-04-17 09:42:58'),
(62, 13, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:43:27', '2026-04-17 09:43:27'),
(63, 13, 'agence_updated', 'Paramètres agence mis à jour : ImmoPlus', 'App\\Models\\Agence', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:00:33', '2026-04-17 10:00:33'),
(64, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:00:58', '2026-04-17 10:00:58'),
(65, 12, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:01:08', '2026-04-17 10:01:08'),
(66, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:03:34', '2026-04-17 10:03:34'),
(67, 13, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:03:45', '2026-04-17 10:03:45'),
(68, 13, 'bien_statut', 'Statut bien \"Maison\" → disponible', 'App\\Models\\Bien', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:03:54', '2026-04-17 10:03:54'),
(69, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:04:02', '2026-04-17 10:04:02'),
(70, 12, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:04:08', '2026-04-17 10:04:08'),
(71, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:04:57', '2026-04-17 10:04:57'),
(72, 13, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:05:06', '2026-04-17 10:05:06'),
(73, 13, 'bien_statut', 'Statut bien \"Maison\" → disponible', 'App\\Models\\Bien', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:05:24', '2026-04-17 10:05:24'),
(74, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:05:26', '2026-04-17 10:05:26'),
(75, 12, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:05:32', '2026-04-17 10:05:32'),
(76, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:07:25', '2026-04-17 10:07:25'),
(77, 13, 'login', 'Connexion réussie', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:07:35', '2026-04-17 10:07:35'),
(78, 13, 'bien_statut', 'Statut bien \"Maison\" → disponible', 'App\\Models\\Bien', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:07:42', '2026-04-17 10:07:42'),
(79, NULL, 'logout', 'Déconnexion', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:08:45', '2026-04-17 10:08:45');

-- --------------------------------------------------------

--
-- Structure de la table `admin_agences`
--

DROP TABLE IF EXISTS `admin_agences`;
CREATE TABLE IF NOT EXISTS `admin_agences` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `agence_id` bigint UNSIGNED NOT NULL,
  `est_principal` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_agences_user_id_unique` (`user_id`),
  KEY `admin_agences_agence_id_foreign` (`agence_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin_agences`
--

INSERT INTO `admin_agences` (`id`, `user_id`, `agence_id`, `est_principal`, `whatsapp`, `created_at`, `updated_at`) VALUES
(1, 13, 5, 1, '0197552265', '2026-04-17 09:34:41', '2026-04-17 09:34:41'),
(2, 4, 5, 0, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(3, 5, 5, 0, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(4, 7, 5, 0, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(5, 8, 5, 0, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56');

-- --------------------------------------------------------

--
-- Structure de la table `agences`
--

DROP TABLE IF EXISTS `agences`;
CREATE TABLE IF NOT EXISTS `agences` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_commercial` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secteur` enum('Résidentiel','Commercial','Industriel','Mixte') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Résidentiel',
  `ville` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse_complete` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kkiapay_public_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kkiapay_private_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kkiapay_secret` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kkiapay_sandbox` tinyint(1) NOT NULL DEFAULT '1',
  `statut` enum('actif','en_attente','suspendu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agences_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `agences`
--

INSERT INTO `agences` (`id`, `nom_commercial`, `secteur`, `ville`, `adresse_complete`, `email`, `telephone`, `logo`, `kkiapay_public_key`, `kkiapay_private_key`, `kkiapay_secret`, `kkiapay_sandbox`, `statut`, `created_at`, `updated_at`) VALUES
(5, 'ImmoPlus', 'Commercial', 'Cotonou', 'Gbegamey', 'pk@gmail.com', '0197000000', 'agences/xKmKVqlsAO2JDY1nEgEoH36rYJszFeKIpA9A0Hqu.webp', '5dedd47034f711f1a2c61d4f994a8525', 'tpk_5dedd47234f711f1a2c61d4f994a8525', 'tsk_5dedd47334f711f1a2c61d4f994a8525', 1, 'actif', '2026-04-17 09:34:40', '2026-04-17 10:00:33');

-- --------------------------------------------------------

--
-- Structure de la table `biens`
--

DROP TABLE IF EXISTS `biens`;
CREATE TABLE IF NOT EXISTS `biens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `agence_id` bigint UNSIGNED NOT NULL,
  `type_bien_id` bigint UNSIGNED NOT NULL,
  `titre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `prix` decimal(15,2) NOT NULL,
  `superficie` double DEFAULT NULL,
  `localisation` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ville` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chambres` int DEFAULT NULL,
  `salles_bain` int DEFAULT NULL,
  `transaction` enum('location','vente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'location',
  `statut` enum('disponible','reserve','vendu','loue','indisponible') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  `is_premium` tinyint(1) NOT NULL DEFAULT '0',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `biens_agence_id_foreign` (`agence_id`),
  KEY `biens_type_bien_id_foreign` (`type_bien_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `biens`
--

INSERT INTO `biens` (`id`, `agence_id`, `type_bien_id`, `titre`, `description`, `prix`, `superficie`, `localisation`, `ville`, `chambres`, `salles_bain`, `transaction`, `statut`, `is_premium`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'sdfghjk', 'ertyhuikolpm', 21556.00, 5, 'zertyuio', 'sdfghj', 45, 23, 'location', 'disponible', 0, 1, '2026-04-10 10:19:09', '2026-04-10 10:26:59'),
(10, 4, 1, 'Test kkiapay', 'fghjklm', 25500.00, 200, 'dfghjk', 'sdfghj', 20, 20, 'location', 'vendu', 0, 1, '2026-04-12 11:38:03', '2026-04-14 15:04:40'),
(11, 4, 2, 'Sergio AHOSSAN', 'Bienvenue dans votre nouvelle maison', 25000.00, 99.2, 'Zogbadje, Calavi', 'Calavi', 15, 12, 'location', 'reserve', 0, 1, '2026-04-14 15:10:47', '2026-04-15 15:45:53'),
(12, 5, 1, 'Maison', 'rtyhujklmù', 25666.00, 70, 'Bohicon, Sodohomey', 'Bohicon', 20, 10, 'location', 'loue', 0, 1, '2026-04-17 09:42:22', '2026-04-17 10:38:04'),
(7, 1, 1, 'sdfghjk', 'Bienvenue', 25564.00, 65, 'cotonou akpakpa', 'dfghj', 2, 2, 'vente', 'disponible', 0, 1, '2026-04-10 11:28:27', '2026-04-10 11:29:31'),
(8, 1, 2, 'Maison luxe', 'Bienvenue dans votre maison', 15000000.00, 125, 'Banikanni', 'Parakou', 1, 12, 'vente', 'disponible', 0, 1, '2026-04-10 12:07:03', '2026-04-10 12:07:06'),
(9, 3, 1, 'Appartement pour riche', 'Libre', 25000.00, 50, 'Cotonou, Banikanni', 'Parakou, Banikanni', 3, 5, 'vente', 'disponible', 0, 1, '2026-04-10 12:19:57', '2026-04-10 12:20:04');

-- --------------------------------------------------------

--
-- Structure de la table `bien_photos`
--

DROP TABLE IF EXISTS `bien_photos`;
CREATE TABLE IF NOT EXISTS `bien_photos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `bien_id` bigint UNSIGNED NOT NULL,
  `chemin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_principale` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bien_photos_bien_id_foreign` (`bien_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `bien_photos`
--

INSERT INTO `bien_photos` (`id`, `bien_id`, `chemin`, `is_principale`, `created_at`, `updated_at`) VALUES
(1, 1, 'biens/UY8qCvSxHekNTECDllOtRAY7dWjpPSskBTzBSa4r.png', 1, '2026-04-10 10:19:09', '2026-04-10 10:19:09'),
(2, 2, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800', 1, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(3, 2, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(4, 2, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(5, 3, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800', 1, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(6, 3, 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(7, 3, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(8, 4, 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800', 1, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(9, 4, 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(10, 5, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800', 1, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(11, 5, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(12, 5, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(13, 6, 'https://images.unsplash.com/photo-1502672023488-70e25813eb80?w=800', 1, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(14, 6, 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800', 0, '2026-04-10 11:07:13', '2026-04-10 11:07:13'),
(15, 7, 'biens/bZjbGiQ4rw8KVDBmN8Caf1Y1Ld3zhhIbKKEU3Vxo.jpg', 1, '2026-04-10 11:28:27', '2026-04-10 11:28:27'),
(16, 7, 'biens/8T02ZRoSYOh8CzrYGmZHP3AIMYQIlkKiwGJ7LIXt.png', 0, '2026-04-10 11:28:27', '2026-04-10 11:28:27'),
(17, 7, 'biens/C4UotCWQVBFCo5kMpZg7fvxtNhvvYAmVcCHnrwYE.png', 0, '2026-04-10 11:28:27', '2026-04-10 11:28:27'),
(18, 7, 'biens/bqRnFb5CcXCxSS6dLb5csHKX4haa294VYEpMINFN.png', 0, '2026-04-10 11:28:27', '2026-04-10 11:28:27'),
(19, 7, 'biens/fIM4UxVLsnsRXzXHSfACCUj5J9fBjtEEdrTpXaWj.png', 0, '2026-04-10 11:28:27', '2026-04-10 11:28:27'),
(20, 7, 'biens/OD45vVmMAFEbRAfm7RJ1o7toeBVnE4ndz0SqfOCX.png', 0, '2026-04-10 11:28:27', '2026-04-10 11:28:27'),
(21, 8, 'biens/qmQyQgy6Kn7uS3pnVwJ0ZjXkRCiSKjFkUw2ZACaa.jpg', 1, '2026-04-10 12:07:03', '2026-04-10 12:07:03'),
(22, 8, 'biens/jq4I59349mCze74W6wdG055DYjENKyqcJbrSby6V.jpg', 0, '2026-04-10 12:07:03', '2026-04-10 12:07:03'),
(23, 9, 'biens/uWNwEsyHy9Exb4l3whRNA1zI5gPKDgX95cZ2RUeh.webp', 1, '2026-04-10 12:19:57', '2026-04-10 12:19:57'),
(24, 9, 'biens/tvBvfaQ27ypYkWKKUwmKnub2e50rFW1lhiDyoybq.webp', 0, '2026-04-10 12:19:57', '2026-04-10 12:19:57'),
(25, 10, 'biens/TAVevTIUb7KR3PTvsuV3kiakgdNe6s2gLHODsfqB.webp', 1, '2026-04-12 11:38:04', '2026-04-12 11:38:04'),
(26, 11, 'biens/Els4NGpAASqdFdTK1CLVsZnqNLereMMyzowbKZ3g.png', 1, '2026-04-14 15:10:47', '2026-04-14 15:10:47'),
(27, 12, 'biens/mSUQ00woL44ndfM7NSdroqB0ygSUGtzTtSoXntD9.jpg', 1, '2026-04-17 09:42:22', '2026-04-17 09:42:22'),
(28, 12, 'biens/0v4YeEa9W3NPz2zGX0Ptl8Ajn7Wg1K6IxKs0hDXt.webp', 0, '2026-04-17 09:42:22', '2026-04-17 09:42:22'),
(29, 12, 'biens/ysquYhwZfMw9TsPY8zPwZVikoFO839DMhbQScv9s.webp', 0, '2026-04-17 09:42:22', '2026-04-17 09:42:22');

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-reservation_5fgLRKWj66iDzKb0DinGkwM2', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1775985798),
('laravel-cache-pay_jIDVYZ9ZXW14tSK2dwNWerRI', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_5fgLRKWj66iDzKb0DinGkwM2\";s:9:\"reference\";s:14:\"PAY-CJYUVDJEAH\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1775985798),
('laravel-cache-reservation_dr09ryRIjgMkjd7jvZ45cMJ3', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1775985806),
('laravel-cache-pay_DskP07kpotwCuSFyIyIFcSQy', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_dr09ryRIjgMkjd7jvZ45cMJ3\";s:9:\"reference\";s:14:\"PAY-UCOUWNNPKP\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1775985806),
('laravel-cache-reservation_vrCSVYRNR5nxh916RFNGy9NJ', 'a:6:{s:7:\"bien_id\";i:8;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:1500000;s:13:\"type_paiement\";s:7:\"acompte\";}', 1775985909),
('laravel-cache-pay_zPBaQv7Mnskfv3WjAhnVXnS9', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:8;s:9:\"agence_id\";i:1;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_vrCSVYRNR5nxh916RFNGy9NJ\";s:9:\"reference\";s:14:\"PAY-FEP4KYHL8N\";s:7:\"montant\";d:1500000;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1775985909),
('laravel-cache-reservation_6VNicLRLy7uad6bon5YBTGes', 'a:6:{s:7:\"bien_id\";i:8;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:1500000;s:13:\"type_paiement\";s:7:\"acompte\";}', 1775985916),
('laravel-cache-pay_WVvCLxKFqgZqG5hXlDs1ESm1', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:8;s:9:\"agence_id\";i:1;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_6VNicLRLy7uad6bon5YBTGes\";s:9:\"reference\";s:14:\"PAY-HPSGTOJFMD\";s:7:\"montant\";d:1500000;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1775985916),
('laravel-cache-reservation_F33w3CgYu2aXLwrsBvXSMo3N', 'a:6:{s:7:\"bien_id\";i:8;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:1500000;s:13:\"type_paiement\";s:7:\"acompte\";}', 1775986086),
('laravel-cache-pay_s7mMymeXSvYv7Q9BZhqdhOEp', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:8;s:9:\"agence_id\";i:1;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_F33w3CgYu2aXLwrsBvXSMo3N\";s:9:\"reference\";s:14:\"PAY-BYGHGNOUBV\";s:7:\"montant\";d:1500000;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1775986086),
('laravel-cache-reservation_MreS6fmCjuhT6cNA1eUZRJdb', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-10\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776002317),
('laravel-cache-pay_QiracSM5OsOUlgoZMfi0RMPM', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-10\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_MreS6fmCjuhT6cNA1eUZRJdb\";s:9:\"reference\";s:14:\"PAY-V6ZOYAHPJE\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776002317),
('laravel-cache-reservation_aVbLIyuYrwx235vvP5xmK76l', 'a:6:{s:7:\"bien_id\";i:10;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776003113),
('laravel-cache-pay_J6ol4IVoyLsanZJBMjUq3mwp', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_aVbLIyuYrwx235vvP5xmK76l\";s:9:\"reference\";s:14:\"PAY-ST8J2TIZEQ\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776003114),
('laravel-cache-reservation_NXuLUxFPFVZaL4YP1ZQ0eFhk', 'a:6:{s:7:\"bien_id\";i:10;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776003687),
('laravel-cache-pay_1CVuziu4mLVwRIK65oV7x4Ca', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_NXuLUxFPFVZaL4YP1ZQ0eFhk\";s:9:\"reference\";s:14:\"PAY-ZCMUICC6H5\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776003688),
('laravel-cache-reservation_aIm1pWlpFA0iRLz1sJ6DpgsA', 'a:6:{s:7:\"bien_id\";i:10;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776161156),
('laravel-cache-pay_xfaBKz4SKEmnboBTQcci6r2s', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_aIm1pWlpFA0iRLz1sJ6DpgsA\";s:9:\"reference\";s:14:\"PAY-WMHSVXSMEY\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776161156),
('laravel-cache-reservation_vzw7y0oCgqRwb2cv3UKoYAbw', 'a:6:{s:7:\"bien_id\";i:10;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776172539),
('laravel-cache-pay_SdbjFBmgKl79tX9eZsyH7uBj', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-11\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_vzw7y0oCgqRwb2cv3UKoYAbw\";s:9:\"reference\";s:14:\"PAY-PQLOL148B2\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776172539),
('laravel-cache-pay_8OyuFXu75Os5JC6k9bzsKQbP', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-YGGHAMGQAF\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776172608),
('laravel-cache-pay_W0KXHwG4O2lnZAy7SkQk5rBz', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-9HWX9BYS2A\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776172613),
('laravel-cache-pay_nJxXFmjIMmYNIsOfOeRDvbkW', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-LTY47CFWI7\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776172660),
('laravel-cache-pay_edMJSXKD8qNKIyhc0BdbLgGN', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-XPWZMNE8MR\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776172663),
('laravel-cache-pay_MzoXA9aGlctsDAfedaUJzooL', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-WRBRTQDATL\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776172664),
('laravel-cache-pay_jxmzZ7UsWivLNtscMF4gsL7O', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-LV2PYETGKU\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776172665),
('laravel-cache-reservation_EZijX51hY4PZ1aFX0tQoKOu7', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:5:\"vente\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776178410),
('laravel-cache-pay_dWbblgvBd9q25ZHxgR5eZ5tA', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:5:\"vente\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_EZijX51hY4PZ1aFX0tQoKOu7\";s:9:\"reference\";s:14:\"PAY-W9KEOKDIZP\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776178410),
('laravel-cache-reservation_DVLkWtrXKpCL8TMlMJuCARcl', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776178422),
('laravel-cache-pay_ezWESA6FtJRsh4gswadcVw9z', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_DVLkWtrXKpCL8TMlMJuCARcl\";s:9:\"reference\";s:14:\"PAY-CFFWCV44GZ\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776178422),
('laravel-cache-reservation_2cuMkeGoy64KOHCdPwbolh5Z', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776178429),
('laravel-cache-pay_gVIY0XmEKUEj0lUdE7YqM0NN', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_2cuMkeGoy64KOHCdPwbolh5Z\";s:9:\"reference\";s:14:\"PAY-2F79A0FNKD\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776178430),
('laravel-cache-reservation_ap685QJFikIeeGVjTULIyJq9', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776178441),
('laravel-cache-pay_ecuJOOuazrWK2ffeylSPa6EM', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_ap685QJFikIeeGVjTULIyJq9\";s:9:\"reference\";s:14:\"PAY-ZK0WADJXLK\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776178442),
('laravel-cache-reservation_7mzP2wzcqO5qb4E6NIR3DiZx', 'a:6:{s:7:\"bien_id\";i:10;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776178699),
('laravel-cache-pay_ZqciBF1e836z30cMj5sRa2tv', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_7mzP2wzcqO5qb4E6NIR3DiZx\";s:9:\"reference\";s:14:\"PAY-OU6005QZWW\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776178700),
('laravel-cache-reservation_sTcBnZtl8SQBgw3ooRKtsyQf', 'a:6:{s:7:\"bien_id\";i:10;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776186889),
('laravel-cache-pay_r4rmNhssmEva8Zqu9sFSlqrZ', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-13\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_sTcBnZtl8SQBgw3ooRKtsyQf\";s:9:\"reference\";s:14:\"PAY-UFHMEWERB6\";s:7:\"montant\";d:2550;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776186889),
('laravel-cache-pay_zLSxDSfUSkr63I0vSZc3uTzQ', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-E1WVLX0CUW\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776186903),
('laravel-cache-pay_ziGTujkytUugkD4Lc1M77S9q', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-RY1BK9XSII\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776186906),
('laravel-cache-pay_AX6tHhTrYrVIcfjJ5nGdPgwX', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-KSOJADDHB2\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776186907),
('laravel-cache-pay_6FIZUQbaAwMhzjh6bQslFOHw', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-ABAXL63IQK\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776186908),
('laravel-cache-pay_sHKyslr1pkMdHGTnJaa2IAxa', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-PYB7K7NSXT\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776187039),
('laravel-cache-pay_q0LQw1rSqi1SM988G4l7Akv3', 'a:8:{s:6:\"action\";s:7:\"complet\";s:7:\"bien_id\";i:10;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:5:\"vente\";s:9:\"reference\";s:14:\"PAY-P4PQKZ1OYY\";s:7:\"montant\";d:25500;s:13:\"type_paiement\";s:7:\"complet\";s:9:\"client_id\";i:6;}', 1776187041),
('laravel-cache-reservation_FEt1mANLgTvhfzCWAA6JxGR6', 'a:6:{s:7:\"bien_id\";i:11;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776188813),
('laravel-cache-pay_b972IWg8ZbBYPv45IYkULCvH', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:11;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_FEt1mANLgTvhfzCWAA6JxGR6\";s:9:\"reference\";s:14:\"PAY-FZTX9JSHJ4\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776188813),
('laravel-cache-reservation_jyYaWkZS5B9GKMnr6zLG708m', 'a:6:{s:7:\"bien_id\";i:9;s:12:\"type_contrat\";s:5:\"vente\";s:11:\"date_limite\";s:10:\"2026-05-15\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776256162),
('laravel-cache-pay_uN0VQEUQoAQO7HI84lzN9sFT', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:9;s:9:\"agence_id\";i:3;s:12:\"type_contrat\";s:5:\"vente\";s:11:\"date_limite\";s:10:\"2026-05-15\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_jyYaWkZS5B9GKMnr6zLG708m\";s:9:\"reference\";s:14:\"PAY-Q4C5FB21IZ\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776256163),
('laravel-cache-reservation_5F6NAeNQfnX16CvR5E9SSfPq', 'a:6:{s:7:\"bien_id\";i:11;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776256374),
('laravel-cache-pay_QNY9jc1oto5JLSg1LZ3kulWb', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:11;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-12\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_5F6NAeNQfnX16CvR5E9SSfPq\";s:9:\"reference\";s:14:\"PAY-3FRQUVITPM\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776256374),
('laravel-cache-reservation_DMz6YSUDAQZyO6BCpXjRnL6L', 'a:6:{s:7:\"bien_id\";i:11;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-20\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";}', 1776257323),
('laravel-cache-pay_eSQGY1yTwAYJS65oG5IN4dqv', 'a:11:{s:6:\"action\";s:11:\"reservation\";s:7:\"bien_id\";i:11;s:9:\"agence_id\";i:4;s:12:\"type_contrat\";s:8:\"location\";s:11:\"date_limite\";s:10:\"2026-05-20\";s:13:\"mode_paiement\";s:12:\"mobile_money\";s:15:\"reservation_key\";s:36:\"reservation_DMz6YSUDAQZyO6BCpXjRnL6L\";s:9:\"reference\";s:14:\"PAY-KDI3IRUCHB\";s:7:\"montant\";d:2500;s:13:\"type_paiement\";s:7:\"acompte\";s:9:\"client_id\";i:6;}', 1776257324);

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `adresse` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_user_id_unique` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id`, `user_id`, `adresse`, `ville`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(2, 3, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(3, 6, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(4, 9, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(5, 10, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(6, 11, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(7, 12, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56'),
(8, 14, NULL, NULL, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56');

-- --------------------------------------------------------

--
-- Structure de la table `contrats`
--

DROP TABLE IF EXISTS `contrats`;
CREATE TABLE IF NOT EXISTS `contrats` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `bien_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `type_contrat` enum('location','vente') COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut_contrat` enum('en_attente','actif','termine','annule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `date_contrat` date NOT NULL,
  `montant_total_location` decimal(15,2) DEFAULT NULL,
  `date_reserv_location` datetime DEFAULT NULL,
  `date_limite_solde_location` datetime DEFAULT NULL,
  `montant_total_vente` decimal(15,2) DEFAULT NULL,
  `date_reserv_vente` datetime DEFAULT NULL,
  `date_limite_solde_vente` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contrats_bien_id_foreign` (`bien_id`),
  KEY `contrats_client_id_foreign` (`client_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `contrats`
--

INSERT INTO `contrats` (`id`, `bien_id`, `client_id`, `type_contrat`, `statut_contrat`, `date_contrat`, `montant_total_location`, `date_reserv_location`, `date_limite_solde_location`, `montant_total_vente`, `date_reserv_vente`, `date_limite_solde_vente`, `created_at`, `updated_at`) VALUES
(1, 10, 6, 'location', 'annule', '2026-04-12', 25500.00, '2026-04-12 14:00:21', '2026-05-11 00:00:00', NULL, NULL, NULL, '2026-04-12 12:00:21', '2026-04-12 12:06:34'),
(2, 10, 9, 'location', 'annule', '2026-04-12', 25500.00, '2026-04-12 19:39:58', '2026-05-12 00:00:00', NULL, NULL, NULL, '2026-04-12 17:39:58', '2026-04-12 18:33:25'),
(3, 10, 6, 'location', 'annule', '2026-04-14', 25500.00, '2026-04-14 12:49:07', '2026-05-12 00:00:00', NULL, NULL, NULL, '2026-04-14 10:49:07', '2026-04-14 12:27:41'),
(4, 10, 6, 'vente', 'actif', '2026-04-14', NULL, NULL, NULL, 25500.00, '2026-04-14 17:04:40', NULL, '2026-04-14 15:04:40', '2026-04-14 15:04:40'),
(5, 11, 6, 'location', 'annule', '2026-04-14', 25000.00, '2026-04-14 17:21:55', NULL, NULL, NULL, NULL, '2026-04-14 15:21:56', '2026-04-15 10:02:12'),
(6, 11, 6, 'location', 'en_attente', '2026-04-15', 25000.00, '2026-04-15 17:45:52', '2026-05-11 00:00:00', NULL, NULL, NULL, '2026-04-15 15:45:52', '2026-04-15 15:45:52'),
(7, 12, 12, 'location', 'annule', '2026-04-17', 25666.00, '2026-04-17 12:02:07', '2026-05-17 00:00:00', NULL, NULL, NULL, '2026-04-17 10:02:07', '2026-04-17 10:03:54'),
(8, 12, 12, 'location', 'annule', '2026-04-17', 25666.00, '2026-04-17 12:04:40', NULL, NULL, NULL, NULL, '2026-04-17 10:04:40', '2026-04-17 10:05:24'),
(9, 12, 12, 'location', 'annule', '2026-04-17', 25666.00, '2026-04-17 12:07:06', '2026-05-17 00:00:00', NULL, NULL, NULL, '2026-04-17 10:07:06', '2026-04-17 10:07:42'),
(10, 12, 10, 'location', 'actif', '2026-04-17', 25666.00, '2026-04-17 12:38:03', NULL, NULL, NULL, NULL, '2026-04-17 10:38:03', '2026-04-17 10:38:03');

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

DROP TABLE IF EXISTS `favoris`;
CREATE TABLE IF NOT EXISTS `favoris` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `bien_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favoris_user_id_bien_id_unique` (`user_id`,`bien_id`),
  KEY `favoris_bien_id_foreign` (`bien_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `favoris`
--

INSERT INTO `favoris` (`id`, `user_id`, `bien_id`, `created_at`, `updated_at`) VALUES
(13, 2, 7, '2026-04-10 11:42:37', '2026-04-10 11:42:37'),
(15, 2, 1, '2026-04-10 11:42:57', '2026-04-10 11:42:57'),
(14, 2, 3, '2026-04-10 11:42:45', '2026-04-10 11:42:45');

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_28_113405_create_permission_tables', 1),
(5, '2026_03_28_200000_create_agences_table', 1),
(6, '2026_03_28_200001_add_agence_to_users_table', 1),
(7, '2026_03_28_200002_create_type_biens_table', 1),
(8, '2026_03_28_200003_create_biens_table', 1),
(9, '2026_03_28_200004_create_bien_photos_table', 1),
(10, '2026_03_28_200005_create_contrats_table', 1),
(11, '2026_03_28_200006_create_paiements_table', 1),
(12, '2026_03_28_200007_create_favoris_table', 1),
(13, '2026_03_28_200008_create_notifications_table', 1),
(14, '2026_03_28_300000_add_fedapay_to_paiements_table', 1),
(15, '2026_03_30_000001_add_whatsapp_to_users_table', 1),
(16, '2026_03_30_100000_create_activity_logs_table', 1),
(17, '2026_03_30_200000_add_adresse_to_users_table', 1),
(18, '2026_04_01_000001_create_class_table_inheritance', 1),
(19, '2026_04_01_000002_migrate_users_to_typed_tables', 1),
(20, '2026_04_01_100000_add_fedapay_to_agences_table', 1),
(21, '2026_04_09_192606_create_personal_access_tokens_table', 1),
(22, '2026_04_10_000001_replace_fedapay_with_kkiapay_in_agences', 2),
(23, '2026_04_12_000001_rename_fedapay_to_kkiapay_in_paiements_table', 2),
(24, '2026_04_17_000001_cleanup_users_table_move_to_typed_tables', 3),
(25, '2026_04_17_000002_fix_orphan_users_typed_tables', 4);

-- --------------------------------------------------------

--
-- Structure de la table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 13),
(3, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 6),
(3, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 10),
(3, 'App\\Models\\User', 11),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 14);

-- --------------------------------------------------------

--
-- Structure de la table `notifications_immogo`
--

DROP TABLE IF EXISTS `notifications_immogo`;
CREATE TABLE IF NOT EXISTS `notifications_immogo` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `titre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lien` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_immogo_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications_immogo`
--

INSERT INTO `notifications_immogo` (`id`, `user_id`, `titre`, `message`, `lien`, `lu`, `created_at`, `updated_at`) VALUES
(1, 2, 'Réservation initiée', 'Votre réservation pour le bien \"sdfghjk\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/1', 0, '2026-04-10 11:41:01', '2026-04-10 11:41:01'),
(2, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 1, '2026-04-10 12:23:43', '2026-04-11 11:02:21'),
(3, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Maison luxe\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/8', 1, '2026-04-11 11:02:49', '2026-04-11 11:03:50'),
(4, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 1, '2026-04-12 06:53:18', '2026-04-12 12:05:32'),
(5, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 1, '2026-04-12 06:53:26', '2026-04-12 12:05:32'),
(6, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Maison luxe\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/8', 1, '2026-04-12 06:55:09', '2026-04-12 12:05:32'),
(7, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Maison luxe\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/8', 1, '2026-04-12 06:55:16', '2026-04-12 12:05:32'),
(8, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Maison luxe\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/8', 1, '2026-04-12 06:58:06', '2026-04-12 12:05:32'),
(9, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 1, '2026-04-12 11:28:37', '2026-04-12 12:05:32'),
(10, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 1, '2026-04-12 11:41:53', '2026-04-12 12:05:32'),
(11, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 1, '2026-04-12 11:51:27', '2026-04-12 12:05:32'),
(12, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 1, '2026-04-12 11:58:55', '2026-04-12 12:05:32'),
(13, 6, 'Réservation confirmée ✓', 'Votre réservation pour \"Test kkiapay\" est confirmée. Acompte : 2 550 FCFA. Réf: PAY-8RAVTHV80L', '/client/historique', 1, '2026-04-12 12:00:21', '2026-04-12 12:05:32'),
(14, 6, 'Contrat annulé', 'Votre contrat pour \"Test kkiapay\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/10', 0, '2026-04-12 12:06:34', '2026-04-12 12:06:34'),
(15, 9, 'Réservation confirmée ✓', 'Votre réservation pour \"Test kkiapay\" est confirmée. Acompte : 2 550 FCFA. Réf: PAY-VE2EEWSPYR', 'http://127.0.0.1:8000/client/historique', 0, '2026-04-12 17:39:58', '2026-04-12 17:39:58'),
(16, 6, 'Contrat annulé', 'Votre contrat pour \"Test kkiapay\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/10', 0, '2026-04-12 18:33:25', '2026-04-12 18:33:25'),
(17, 9, 'Contrat annulé', 'Votre contrat pour \"Test kkiapay\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/10', 0, '2026-04-12 18:33:25', '2026-04-12 18:33:25'),
(18, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 0, '2026-04-14 07:35:56', '2026-04-14 07:35:56'),
(19, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 0, '2026-04-14 10:45:39', '2026-04-14 10:45:39'),
(20, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 0, '2026-04-14 10:48:35', '2026-04-14 10:48:35'),
(21, 6, 'Réservation confirmée ✓', 'Votre réservation pour \"Test kkiapay\" est confirmée. Acompte : 2 550 FCFA. Réf: PAY-O7ODK7YBKM', '/client/historique', 0, '2026-04-14 10:49:07', '2026-04-14 10:49:07'),
(22, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 0, '2026-04-14 12:23:29', '2026-04-14 12:23:29'),
(23, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 0, '2026-04-14 12:23:42', '2026-04-14 12:23:42'),
(24, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 0, '2026-04-14 12:23:49', '2026-04-14 12:23:49'),
(25, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 0, '2026-04-14 12:24:01', '2026-04-14 12:24:01'),
(26, 6, 'Contrat annulé', 'Votre contrat pour \"Test kkiapay\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/10', 0, '2026-04-14 12:27:41', '2026-04-14 12:27:41'),
(27, 9, 'Contrat annulé', 'Votre contrat pour \"Test kkiapay\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/10', 0, '2026-04-14 12:27:41', '2026-04-14 12:27:41'),
(28, 6, 'Contrat annulé', 'Votre contrat pour \"Test kkiapay\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/10', 0, '2026-04-14 12:27:42', '2026-04-14 12:27:42'),
(29, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 0, '2026-04-14 12:28:19', '2026-04-14 12:28:19'),
(30, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Test kkiapay\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/10', 0, '2026-04-14 14:44:49', '2026-04-14 14:44:49'),
(31, 6, 'Paiement complet confirmé ✓', 'Paiement complet pour \"Test kkiapay\" confirmé. Réf: PAY-OFR69UT62U', '/client/historique', 0, '2026-04-14 15:04:40', '2026-04-14 15:04:40'),
(32, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Sergio AHOSSAN\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/11', 0, '2026-04-14 15:16:53', '2026-04-14 15:16:53'),
(33, 6, 'Paiement complet confirmé ✓', 'Paiement complet pour \"Sergio AHOSSAN\" confirmé. Réf: PAY-DAMPPOKM25', '/client/historique', 0, '2026-04-14 15:21:56', '2026-04-14 15:21:56'),
(34, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Appartement pour riche\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/9', 0, '2026-04-15 09:59:22', '2026-04-15 09:59:22'),
(35, 6, 'Contrat annulé', 'Votre contrat pour \"Sergio AHOSSAN\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/11', 0, '2026-04-15 10:02:12', '2026-04-15 10:02:12'),
(36, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Sergio AHOSSAN\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/11', 0, '2026-04-15 10:02:54', '2026-04-15 10:02:54'),
(37, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Sergio AHOSSAN\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/11', 0, '2026-04-15 10:18:43', '2026-04-15 10:18:43'),
(38, 6, 'Réservation initiée', 'Votre réservation pour le bien \"Sergio AHOSSAN\" a été initiée. Veuillez procéder au paiement de l\'acompte.', '/biens/11', 0, '2026-04-15 15:45:25', '2026-04-15 15:45:25'),
(39, 6, 'Réservation confirmée ✓', 'Votre réservation pour \"Sergio AHOSSAN\" est confirmée. Acompte : 2 500 FCFA. Réf: PAY-RYVZQXVNFT', '/client/historique', 0, '2026-04-15 15:45:53', '2026-04-15 15:45:53'),
(40, 12, 'Réservation confirmée ✓', 'Votre réservation pour \"Maison\" est confirmée. Acompte : 2 567 FCFA. Réf: PAY-XD6SEWKPKT', 'http://127.0.0.1:8000/client/historique', 1, '2026-04-17 10:02:07', '2026-04-17 10:02:31'),
(41, 12, 'Contrat annulé', 'Votre contrat pour \"Maison\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/12', 1, '2026-04-17 10:03:54', '2026-04-17 10:05:38'),
(42, 12, 'Paiement complet confirmé ✓', 'Paiement complet pour \"Maison\" confirmé. Réf: PAY-DICJRFKBSQ', 'http://127.0.0.1:8000/client/historique', 1, '2026-04-17 10:04:40', '2026-04-17 10:05:38'),
(43, 12, 'Contrat annulé', 'Votre contrat pour \"Maison\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/12', 1, '2026-04-17 10:05:24', '2026-04-17 10:05:38'),
(44, 12, 'Contrat annulé', 'Votre contrat pour \"Maison\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/12', 1, '2026-04-17 10:05:24', '2026-04-17 10:05:38'),
(45, 12, 'Réservation confirmée ✓', 'Votre réservation pour \"Maison\" est confirmée. Acompte : 2 567 FCFA. Réf: PAY-CKUQ4U6WZV', 'http://127.0.0.1:8000/client/historique', 0, '2026-04-17 10:07:06', '2026-04-17 10:07:06'),
(46, 12, 'Contrat annulé', 'Votre contrat pour \"Maison\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/12', 0, '2026-04-17 10:07:42', '2026-04-17 10:07:42'),
(47, 12, 'Contrat annulé', 'Votre contrat pour \"Maison\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/12', 0, '2026-04-17 10:07:42', '2026-04-17 10:07:42'),
(48, 12, 'Contrat annulé', 'Votre contrat pour \"Maison\" a été annulé par l\'agence. Le bien est de nouveau disponible.', 'http://127.0.0.1:8000/biens/12', 0, '2026-04-17 10:07:42', '2026-04-17 10:07:42'),
(49, 10, 'Paiement complet confirmé ✓', 'Paiement complet pour \"Maison\" confirmé. Réf: PAY-C70OIZYQY3', '/client/historique', 0, '2026-04-17 10:38:04', '2026-04-17 10:38:04');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `contrat_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `date_paiement` datetime NOT NULL,
  `type_paiement` enum('acompte','solde','complet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode_paiement` enum('mobile_money','virement','especes','carte') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mobile_money',
  `reference` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kkiapay_transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` enum('en_attente','confirme','echoue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paiements_reference_unique` (`reference`),
  KEY `paiements_contrat_id_foreign` (`contrat_id`),
  KEY `paiements_client_id_foreign` (`client_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `contrat_id`, `client_id`, `montant`, `date_paiement`, `type_paiement`, `mode_paiement`, `reference`, `kkiapay_transaction_id`, `statut`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 2550.00, '2026-04-12 14:00:21', 'acompte', 'mobile_money', 'PAY-8RAVTHV80L', '0', 'confirme', '2026-04-12 12:00:21', '2026-04-12 12:00:21'),
(2, 2, 9, 2550.00, '2026-04-12 19:39:58', 'acompte', 'mobile_money', 'PAY-VE2EEWSPYR', '0', 'confirme', '2026-04-12 17:39:58', '2026-04-12 17:39:58'),
(3, 3, 6, 2550.00, '2026-04-14 12:49:07', 'acompte', 'mobile_money', 'PAY-O7ODK7YBKM', '0', 'confirme', '2026-04-14 10:49:07', '2026-04-14 10:49:07'),
(4, 4, 6, 25500.00, '2026-04-14 17:04:40', 'complet', 'mobile_money', 'PAY-OFR69UT62U', '1', 'confirme', '2026-04-14 15:04:40', '2026-04-14 15:04:40'),
(5, 5, 6, 25000.00, '2026-04-14 17:21:56', 'complet', 'mobile_money', 'PAY-DAMPPOKM25', '0', 'confirme', '2026-04-14 15:21:56', '2026-04-14 15:21:56'),
(6, 6, 6, 2500.00, '2026-04-15 17:45:52', 'acompte', 'mobile_money', 'PAY-RYVZQXVNFT', '0', 'confirme', '2026-04-15 15:45:52', '2026-04-15 15:45:52'),
(7, 7, 12, 2566.60, '2026-04-17 12:02:07', 'acompte', 'mobile_money', 'PAY-XD6SEWKPKT', '0', 'confirme', '2026-04-17 10:02:07', '2026-04-17 10:02:07'),
(8, 8, 12, 25666.00, '2026-04-17 12:04:40', 'complet', 'mobile_money', 'PAY-DICJRFKBSQ', '0', 'confirme', '2026-04-17 10:04:40', '2026-04-17 10:04:40'),
(9, 9, 12, 2566.60, '2026-04-17 12:07:06', 'acompte', 'mobile_money', 'PAY-CKUQ4U6WZV', '0', 'confirme', '2026-04-17 10:07:06', '2026-04-17 10:07:06'),
(10, 10, 10, 25666.00, '2026-04-17 12:38:03', 'complet', 'mobile_money', 'PAY-C70OIZYQY3', '0', 'confirme', '2026-04-17 10:38:03', '2026-04-17 10:38:03');

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(3, 'App\\Models\\User', 10, 'immogo-mobile', 'b8b16a7ad8fb093afac75bbd7050416ea122ad8cbbff69bc514ced2b29d5566c', '[\"*\"]', '2026-04-17 10:39:12', NULL, '2026-04-15 15:49:04', '2026-04-17 10:39:12');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(2, 'admin_agence', 'web', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(3, 'client', 'web', '2026-04-10 10:02:50', '2026-04-10 10:02:50');

-- --------------------------------------------------------

--
-- Structure de la table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('M8TFMupuksJ265IFCZalZs6Y5WDfzsgvMeZUsv5N', 14, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaGRYeWVCOEpYYjI4SXJFZjlxWU1kN1BVYlhvbzdtdW1XdE9YUFdUbCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTQ7fQ==', 1776428372);

-- --------------------------------------------------------

--
-- Structure de la table `super_admins`
--

DROP TABLE IF EXISTS `super_admins`;
CREATE TABLE IF NOT EXISTS `super_admins` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `whatsapp` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `super_admins_user_id_unique` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `super_admins`
--

INSERT INTO `super_admins` (`id`, `user_id`, `whatsapp`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '2026-04-17 10:16:56', '2026-04-17 10:16:56');

-- --------------------------------------------------------

--
-- Structure de la table `type_biens`
--

DROP TABLE IF EXISTS `type_biens`;
CREATE TABLE IF NOT EXISTS `type_biens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `type_biens`
--

INSERT INTO `type_biens` (`id`, `libelle`, `created_at`, `updated_at`) VALUES
(1, 'Appartement', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(2, 'Maison', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(3, 'Villa', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(4, 'Parcelle', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(5, 'Loft', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(6, 'Studio', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(7, 'Duplex', '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(8, 'Bureau', '2026-04-10 10:02:50', '2026-04-10 10:02:50');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('super_admin','admin_agence','client') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `prenom`, `email`, `telephone`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'HESSOU', 'Euloge Grâcien', 'hessoueulogegracien@gmail.com', '+22901000000', 'super_admin', NULL, '$2y$12$axeOelYTpfeD4610fUXgb.t5Lxe.JJxHzaFsXLSKVByifYdgJl.q2', NULL, '2026-04-10 10:02:50', '2026-04-10 10:02:50'),
(2, 'hfhf', 'sergio', 'se@gmail.com', '656559999', 'client', NULL, '$2y$12$9K5R42qolm89FGvP29/6UusaWhpyM.RMyWJDPmKHSTTYGigl2Chim', NULL, '2026-04-10 10:04:00', '2026-04-10 10:04:00'),
(3, 'sergio', 'dfff', 'sergio@gmailcom', '1255553', 'client', NULL, '$2y$12$5vz1Y3nj.y6S0567EHKrPOWeJQeNx23IaS4nVYdkMLs4bGcCGhelG', NULL, '2026-04-10 10:12:44', '2026-04-10 10:12:44'),
(4, 'sdfghj', 'sdghjk', 'az@gmail.com', '4563205', 'admin_agence', NULL, '$2y$12$8sFjKd5jI6XYzHGCofVlDuPOXogjxHuWthBk5nJFIk5KA55P/Gu7u', '0SwIrRjbxi5M5f4PJyxX2VPnThD26YPXzJiDpInNqMaKe2nnY3okU2gNJoYr', '2026-04-10 10:15:27', '2026-04-10 10:15:27'),
(5, 'Admin', 'Agence', 'admin@immogo.bj', '+22901234567', 'admin_agence', NULL, '$2y$12$PYDQvgPcD0oIzhPhuEuLZe7XGP19RkiPcY9vfd7SUnYeSKFrjFv6a', NULL, '2026-04-10 10:34:27', '2026-04-10 10:34:27'),
(6, 'ahossan', 'sergio', 'sergioahossan@gmail.com', '4589666688', 'client', NULL, '$2y$12$Q/6CSK3L6Gsfjhh6WXIbfOtEdlNqVwYLWTiHn9QVNYjEJVA3McZ1m', NULL, '2026-04-10 12:02:42', '2026-04-12 12:04:48'),
(7, 'Yves', 'AKPA', 'pk1@gmail.com', '+22956945872', 'admin_agence', NULL, '$2y$12$MDZkaR40ZjDEiyjvRqI.Y.FIFwCHHQzptsXeT0zIPVVe4HnaNPRLG', 'q2oNibQNTUhU8oZl4jEcjEsJZJjwDEf1zBzSfVVRyrifNOxiA5QUxqBnpG00', '2026-04-10 12:14:36', '2026-04-10 12:14:36'),
(8, 'ABA', 'Yves', 'pk3@gmail.com', '+22956945872', 'admin_agence', NULL, '$2y$12$QTBe/lrb8UAMjSuuBwhtVOyqf.S7RZHc4ApxOMSFfG04ASVhY2Esy', 'HGUKMR2oOACiw1foI4O0Bt9eEVqdVNKyeWwRAdIF9s9btiwcYoKM7ISwyMV5', '2026-04-12 11:35:18', '2026-04-12 11:35:18'),
(9, 'AHO', 'Zouliath', 'z@gmail.com', '+2290145554885', 'client', NULL, '$2y$12$nq4cKla2n/HyT4RAL11w2.AMSAiz5uQh/GJsC7xL0g2dAnsKFEEne', NULL, '2026-04-12 17:38:20', '2026-04-12 17:38:20'),
(10, 'hjjj', 'ghjj', 'w@gmail.com', '88866685888', 'client', NULL, '$2y$12$2xMClrWVc5KIcC993RUH3egc0rbdEsWDyhWMwxl/zs32QBxOaKTDq', NULL, '2026-04-15 15:49:04', '2026-04-15 15:49:04'),
(11, 'Dupont', 'Juste', 'dupont@gmail.com', '0197000000', 'client', NULL, '$2y$12$cOMgJlD3Z1nkyfEtpNjgP.bdnTWOzpLIJUGalBh.4Mc2qExTAFmWu', NULL, '2026-04-17 09:13:59', '2026-04-17 09:13:59'),
(12, 'Mart', 'Lass', 'mart@gmail.com', '0145789875', 'client', NULL, '$2y$12$5JTesZK2B8RhnixcpwZNxuFaI6K98Mzby98G2zKyIWlHItcy0ehCC', NULL, '2026-04-17 09:19:52', '2026-04-17 09:19:52'),
(13, 'Immo', 'admin', 'immo@gmail.com', '0197552265', 'admin_agence', NULL, '$2y$12$9inO0RsJc.R87MLvJeSfWuF3dMkbYOmatXkBtYys1744Yd0ZhW8Ji', NULL, '2026-04-17 09:34:41', '2026-04-17 09:34:41'),
(14, 'dfgh', 'dfghjk', 'cli@gmail.com', '0145789875', 'client', NULL, '$2y$12$7bv2udmd.fbAlGv5ZpiFQuHGdFdvpCHx1d4Ha6shqCQPqs7BRPgre', NULL, '2026-04-17 10:09:34', '2026-04-17 10:09:34');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
