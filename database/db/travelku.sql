-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 19, 2026 at 04:58 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travelku`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `travel_package_id` bigint UNSIGNED NOT NULL,
  `booking_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departure_date` date NOT NULL,
  `participants` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `price_per_person` decimal(12,2) NOT NULL,
  `total_price` decimal(14,2) GENERATED ALWAYS AS ((`participants` * `price_per_person`)) STORED COMMENT 'Total otomatis',
  `status` enum('Menunggu','Dikonfirmasi','Selesai','Dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_booking_number_unique` (`booking_number`),
  KEY `bookings_status_index` (`status`),
  KEY `bookings_departure_date_index` (`departure_date`),
  KEY `idx_pkg_status` (`travel_package_id`,`status`),
  KEY `bookings_booking_number_index` (`booking_number`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `travel_package_id`, `booking_number`, `name`, `contact`, `departure_date`, `participants`, `price_per_person`, `status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'BK-20260001', 'Budi Santoso', '081234567890', '2026-06-15', 4, 2500000.00, 'Dikonfirmasi', 'Minta kamar non-smoking', '2026-05-15 20:52:55', '2026-05-15 20:52:55', NULL),
(2, 2, 'BK-20260002', 'Siti Rahayu', 'siti@email.com', '2026-07-10', 2, 1800000.00, 'Dibatalkan', NULL, '2026-05-17 20:52:55', '2026-05-18 20:54:21', NULL),
(3, 3, 'BK-20260003', 'Ahmad Fauzi', '085678901234', '2026-05-25', 6, 950000.00, 'Selesai', 'Group tour mahasiswa', '2026-05-13 20:52:55', '2026-05-13 20:52:55', NULL),
(4, 4, 'BK-20260004', 'Dewi Kusuma', 'dewi@gmail.com', '2026-08-02', 3, 6500000.00, 'Menunggu', 'Honeymoon package', '2026-05-16 20:52:55', '2026-05-16 20:52:55', NULL),
(5, 5, 'BK-20260005', 'Hendra Wijaya', '081999888777', '2026-09-14', 8, 3200000.00, 'Dikonfirmasi', 'Grup kantor', '2026-05-14 20:52:55', '2026-05-14 20:52:55', NULL),
(6, 6, 'BK-20260006', 'Rina Hastuti', 'rina.h@mail.id', '2026-06-01', 2, 2900000.00, 'Dibatalkan', 'Pembatalan karena sakit', '2026-05-12 20:52:55', '2026-05-12 20:52:55', NULL),
(7, 10, 'BK-20260007', 'Yudha', 'yudha048@gmail.com', '2026-05-19', 1, 2222222.00, 'Menunggu', NULL, '2026-05-18 20:57:12', '2026-05-18 20:57:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_status_logs`
--

DROP TABLE IF EXISTS `booking_status_logs`;
CREATE TABLE IF NOT EXISTS `booking_status_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` bigint UNSIGNED NOT NULL,
  `old_status` enum('Menunggu','Dikonfirmasi','Selesai','Dibatalkan') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` enum('Menunggu','Dikonfirmasi','Selesai','Dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_status_logs_booking_id_index` (`booking_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_status_logs`
--

INSERT INTO `booking_status_logs` (`id`, `booking_id`, `old_status`, `new_status`, `changed_by`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 'Menunggu', 'Dibatalkan', 'Staff Agen', NULL, '2026-05-18 20:54:21', '2026-05-18 20:54:21'),
(2, 7, NULL, 'Menunggu', 'Staff Agen', NULL, '2026-05-18 20:57:12', '2026-05-18 20:57:12');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Pantai & Bahari', 'pantai-bahari', 'Destinasi pantai, selam, snorkeling', 'waves', 1, '2026-05-18 20:52:55', '2026-05-18 20:52:55'),
(2, 'Budaya & Sejarah', 'budaya-sejarah', 'Candi, museum, seni tradisional', 'landmark', 1, '2026-05-18 20:52:55', '2026-05-18 20:52:55'),
(3, 'Petualangan', 'petualangan', 'Hiking, arung jeram, off-road', 'mountain', 1, '2026-05-18 20:52:55', '2026-05-18 20:52:55'),
(4, 'Honeymoon', 'honeymoon', 'Paket romantis untuk pasangan', 'heart', 1, '2026-05-18 20:52:55', '2026-05-18 20:52:55'),
(5, 'Family Tour', 'family-tour', 'Paket ramah keluarga & anak-anak', 'users', 1, '2026-05-18 20:52:55', '2026-05-18 20:52:55'),
(6, 'City Tour', 'city-tour', 'Wisata kota, kuliner, belanja', 'map-pin', 1, '2026-05-18 20:52:55', '2026-05-18 20:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000001_create_categories_table', 1),
(5, '2024_01_01_000002_create_travel_packages_table', 1),
(6, '2024_01_01_000003_create_bookings_table', 1),
(7, '2024_01_01_000004_create_booking_status_logs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
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
-- Table structure for table `sessions`
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
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('YncglFhjHx8L0OPaE3KlAQMYeIFsJRLagN70e6RJ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMGZMMzhRUEFSMnB0cFhVcDZhSmUwMlBabnZoYkF2NVoxeko4cFBUdyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wYWNrYWdlcyI7czo1OiJyb3V0ZSI7czoxNDoicGFja2FnZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779166576),
('vujky97fuLqANj0X1ItEdUSIuOihdx46xWyEAhyo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibDRmQWg5YmNRY2lhSWl2bkdzdmhWa0dsM2Y0QmlzdWdvcFFnZUpsViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoxNDoidHJhdmVsa3UuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779166632);

-- --------------------------------------------------------

--
-- Table structure for table `travel_packages`
--

DROP TABLE IF EXISTS `travel_packages`;
CREATE TABLE IF NOT EXISTS `travel_packages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint UNSIGNED NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(170) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_days` tinyint UNSIGNED NOT NULL,
  `duration_nights` tinyint UNSIGNED NOT NULL,
  `base_price` decimal(12,2) NOT NULL,
  `price_weekend` decimal(12,2) DEFAULT NULL,
  `price_holiday` decimal(12,2) DEFAULT NULL,
  `min_participants` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `max_participants` tinyint UNSIGNED NOT NULL DEFAULT '100',
  `short_desc` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `includes` text COLLATE utf8mb4_unicode_ci,
  `excludes` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` smallint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `travel_packages_code_unique` (`code`),
  UNIQUE KEY `travel_packages_slug_unique` (`slug`),
  KEY `travel_packages_is_active_deleted_at_index` (`is_active`,`deleted_at`),
  KEY `travel_packages_category_id_index` (`category_id`),
  KEY `travel_packages_is_featured_is_active_index` (`is_featured`,`is_active`),
  KEY `travel_packages_sort_order_index` (`sort_order`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `travel_packages`
--

INSERT INTO `travel_packages` (`id`, `category_id`, `code`, `name`, `slug`, `destination`, `duration_days`, `duration_nights`, `base_price`, `price_weekend`, `price_holiday`, `min_participants`, `max_participants`, `short_desc`, `description`, `includes`, `excludes`, `cover_image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'PKG-001', 'Bali 4D3N – Tropical Paradise', 'bali-4d3n-tropical-paradise', 'Bali', 4, 3, 2500000.00, 2800000.00, 3000000.00, 1, 20, 'Jelajahi keindahan Bali: pantai, pura, dan budaya dalam 4 hari.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 1, 1, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL),
(2, 1, 'PKG-002', 'Lombok 3D2N – Pearl of the East', 'lombok-3d2n-pearl-of-the-east', 'Lombok', 3, 2, 1800000.00, 2000000.00, 2200000.00, 1, 30, 'Pantai bening, Gili Islands, dan kaki Rinjani dalam satu paket.', NULL, NULL, NULL, NULL, 0, 1, 2, '2026-05-18 20:52:55', '2026-05-18 20:54:23', NULL),
(10, 2, 'PKG-010', 'Japannese', 'japannese', 'Bali', 4, 3, 2222222.00, NULL, NULL, 1, 20, 'RWS', NULL, NULL, NULL, NULL, 1, 0, 0, '2026-05-18 20:56:16', '2026-05-18 20:56:16', NULL),
(3, 2, 'PKG-003', 'Yogyakarta 2D1N – Warisan Budaya', 'yogyakarta-2d1n-warisan-budaya', 'Yogyakarta', 2, 1, 950000.00, 1100000.00, 1200000.00, 2, 40, 'Candi Borobudur, Prambanan, Keraton, dan kuliner khas Jogja.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 0, 3, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL),
(4, 1, 'PKG-004', 'Raja Ampat 5D4N – Surga Bawah Laut', 'raja-ampat-5d4n-surga-bawah-laut', 'Raja Ampat', 5, 4, 5800000.00, 6500000.00, 7000000.00, 2, 15, 'Menyelam di perairan terkaya biodiversitas laut di dunia.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 1, 4, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL),
(5, 3, 'PKG-005', 'Labuan Bajo 4D3N – Komodo & Beyond', 'labuan-bajo-4d3n-komodo-beyond', 'Labuan Bajo', 4, 3, 3200000.00, 3600000.00, 3800000.00, 2, 20, 'Bertemu komodo, snorkeling, dan menikmati sunset terbaik di NTT.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 1, 5, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL),
(6, 3, 'PKG-006', 'Komodo 3D2N – Naga Terakhir Bumi', 'komodo-3d2n-naga-terakhir-bumi', 'Komodo', 3, 2, 2900000.00, 3200000.00, 3400000.00, 2, 18, 'Perjalanan eksklusif ke Pulau Komodo dan Pink Beach.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 0, 6, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL),
(7, 1, 'PKG-007', 'Manado 4D3N – Bunaken Adventure', 'manado-4d3n-bunaken-adventure', 'Manado', 4, 3, 2200000.00, 2500000.00, 2700000.00, 1, 25, 'Menyelam di Taman Nasional Bunaken.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 0, 7, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL),
(8, 1, 'PKG-008', 'Bunaken 3D2N – Wall Dive Paradise', 'bunaken-3d2n-wall-dive-paradise', 'Bunaken', 3, 2, 1900000.00, 2100000.00, 2300000.00, 2, 16, 'Paket khusus penyelaman di dinding karang Bunaken.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 0, 8, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL),
(9, 1, 'PKG-009', 'Wakatobi 4D3N – Coral Triangle', 'wakatobi-4d3n-coral-triangle', 'Wakatobi', 4, 3, 3500000.00, 3900000.00, 4200000.00, 2, 12, 'Eksplorasi Segitiga Terumbu Karang Dunia.', NULL, 'Hotel, makan pagi, transport AC, guide lokal', 'Tiket pesawat, pengeluaran pribadi', NULL, 1, 0, 9, '2026-05-18 20:52:55', '2026-05-18 20:52:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `travel_packages`
--
ALTER TABLE `travel_packages` ADD FULLTEXT KEY `idx_pkg_search` (`name`,`destination`,`short_desc`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
