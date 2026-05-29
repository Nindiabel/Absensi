# ************************************************************
# Antares - SQL Client
# Version 0.7.35
# 
# https://antares-sql.app/
# https://github.com/antares-sql/antares
# 
# Host: 127.0.0.1 (mariadb.org binary distribution 10.4.32)
# Database: sm-sekolah
# Generation time: 2026-04-08T17:05:15+07:00
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table absensi_member
# ------------------------------------------------------------

DROP TABLE IF EXISTS `absensi_member`;

CREATE TABLE `absensi_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'ID guru atau karyawan',
  `foto_absensi` varchar(255) DEFAULT NULL COMMENT 'Path foto wajah saat absensi',
  `foto_pulang` varchar(255) DEFAULT NULL COMMENT 'Path foto wajah saat scan pulang',
  `tanggal_absensi` date NOT NULL COMMENT 'Tanggal absensi',
  `jam_masuk` time DEFAULT NULL COMMENT 'Jam masuk kerja',
  `jam_pulang` time DEFAULT NULL COMMENT 'Jam pulang kerja',
  `status_kehadiran` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `status_final` enum('hadir','terlambat','izin','sakit','alpha') DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_member_tanggal` (`member_id`,`tanggal_absensi`),
  CONSTRAINT `absensi_member_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `absensi_member`
(member_id, foto_absensi, foto_pulang, tanggal_absensi, jam_masuk, jam_pulang, status_kehadiran, status_final)
VALUES
(2, 'uploads/absensi/nafi.png',    NULL, '2025-01-10', '07:00:00', '16:00:00', 'hadir', 'Hadir'),
(3, 'uploads/absensi/prayogi.png', NULL, '2025-01-11', '08:25:00', '16:10:00', 'hadir', 'terlambat'),
(2, NULL, NULL, '2025-02-05', NULL, NULL, 'izin', 'izin'),
(3, 'uploads/absensi/prayogi2.png', NULL, '2025-02-06', '07:00:00', '16:05:00', 'hadir', 'Hadir'),
(2, 'uploads/absensi/nafi2.png',   NULL, '2025-03-01', '08:30:00', '16:00:00', 'hadir', 'terlambat'),
(3, NULL, NULL, '2025-03-02', NULL, NULL, 'sakit', 'sakit'),
(2, 'uploads/absensi/nafi.png',    NULL, '2025-01-15', '07:50:00', '16:00:00', 'hadir', 'terlambat'),
(3, NULL, NULL, '2025-02-20', NULL, NULL, 'izin', 'izin');        

DROP TABLE IF EXISTS `absensi_settings`;
CREATE TABLE `absensi_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jam_masuk` time NOT NULL COMMENT 'Jam mulai masuk kerja',
  `jam_pulang` time NOT NULL COMMENT 'Jam pulang kerja',
  `toleransi_terlambat` int(11) NOT NULL COMMENT 'Toleransi keterlambatan dalam menit',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `absensi_settings` (`jam_masuk`, `jam_pulang`, `toleransi_terlambat`)
VALUES ('07:00:00', '16:00:00', 30);


# Dump of table apps
# ------------------------------------------------------------

DROP TABLE IF EXISTS `apps`;

CREATE TABLE `apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `apps` WRITE;
/*!40000 ALTER TABLE `apps` DISABLE KEYS */;

INSERT INTO `apps` (`id`, `name`, `slug`, `url`) VALUES
	(1, "Smart Keuangan", "smart-keuangan", "localhost:8001"),
	(2, "Smart Persuratan", "smart-surat", "localhost:8001");

/*!40000 ALTER TABLE `apps` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table cache
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table cache_locks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table classes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `classes`;

CREATE TABLE `classes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `enrollment_year` year(4) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;

INSERT INTO `classes` (`id`, `name`, `description`, `enrollment_year`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
	(1, "RPL-1", "NSNJDNMSM CJDS CNDSCSJCNJDC NSJNCJKDN", 2022, "2026-04-08 16:13:24", 8, NULL, NULL, NULL, NULL);

/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table data_wajah_member
# ------------------------------------------------------------

DROP TABLE IF EXISTS `data_wajah_member`;

CREATE TABLE `data_wajah_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'ID guru atau karyawan',
  `data_embedding_wajah` longtext NOT NULL COMMENT 'Data hasil ekstraksi fitur wajah',
  `foto_wajah` varchar(255) DEFAULT NULL COMMENT 'Foto wajah saat pendaftaran',
  `status_aktif` tinyint(1) DEFAULT 1 COMMENT 'Status data wajah aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_member_wajah` (`member_id`),
  CONSTRAINT `data_wajah_member_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO data_wajah_member
(member_id, data_embedding_wajah, foto_wajah, status_aktif, created_at, updated_at)
VALUES
(2, 'embedding_dummy_guru_1', 'uploads/wajah/guru_1.jpg', 1, NOW(), NOW()),
(3, 'embedding_dummy_tendik_3', 'uploads/wajah/tendik_3.jpg', 1, NOW(), NOW());


# Dump of table failed_jobs
# ------------------------------------------------------------

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





# Dump of table job_batches
# ------------------------------------------------------------

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





# Dump of table jobs
# ------------------------------------------------------------

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





# Dump of table libraries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `libraries`;

CREATE TABLE `libraries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `libraries` WRITE;
/*!40000 ALTER TABLE `libraries` DISABLE KEYS */;

INSERT INTO `libraries` (`id`, `name`, `logo`) VALUES
	(1, "SMKN 8 Jember", NULL);

/*!40000 ALTER TABLE `libraries` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table log_absensi_face_recognition
# ------------------------------------------------------------

DROP TABLE IF EXISTS `log_absensi_face_recognition`;

CREATE TABLE `log_absensi_face_recognition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL COMMENT 'ID guru atau karyawan',
  `tanggal` date NOT NULL COMMENT 'Tanggal proses absensi',
  `waktu` time NOT NULL COMMENT 'Waktu proses absensi',
  `nilai_ear` decimal(5,3) DEFAULT NULL COMMENT 'Nilai Eye Aspect Ratio (kedipan mata)',
  `skor_liveness` decimal(5,2) DEFAULT NULL COMMENT 'Skor deteksi liveness',
  `status_liveness` tinyint(1) DEFAULT 0 COMMENT 'Validasi wajah hidup',
  `hasil_pengenalan` enum('berhasil','gagal','tidak_dikenal') NOT NULL,
  `nama_perangkat` varchar(100) DEFAULT NULL COMMENT 'Nama perangkat absensi',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_log_member` (`member_id`),
  CONSTRAINT `log_absensi_face_recognition_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO log_absensi_face_recognition
(member_id, tanggal, waktu, nilai_ear, skor_liveness, status_liveness, hasil_pengenalan, nama_perangkat, created_at)
VALUES
(2, '2025-01-10', '07:40:10', 0.251, 96.50, 1, 'berhasil', 'Camera Utama', NOW()),
(3, '2025-01-11', '08:25:15', 0.238, 93.20, 1, 'berhasil', 'Camera Utama', NOW()),

(2, '2025-02-05', '07:58:00', 0.210, 88.40, 1, 'berhasil', 'Camera Utama', NOW()),
(3, '2025-02-06', '07:55:08', 0.266, 97.10, 1, 'berhasil', 'Camera Utama', NOW()),

(2, '2025-03-01', '08:30:12', 0.229, 91.60, 1, 'berhasil', 'Camera Utama', NOW()),
(3, '2025-03-02', '08:05:00', 0.205, 86.30, 1, 'berhasil', 'Camera Utama', NOW()),

(2, '2025-01-15', '07:50:20', 0.263, 98.00, 1, 'berhasil', 'Camera Utama', NOW()),
(3, '2025-02-20', '08:00:00', 0.218, 89.70, 1, 'berhasil', 'Camera Utama', NOW());



# Dump of table member_categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `member_categories`;

CREATE TABLE `member_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `member_categories_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `member_categories_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `member_categories` WRITE;
/*!40000 ALTER TABLE `member_categories` DISABLE KEYS */;

INSERT INTO `member_categories` (`id`, `name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`, `deleted_by`) VALUES
	(1, "Guru", 8, 8, "2024-08-08 15:26:22", "2024-08-08 15:28:27", NULL, NULL),
	(2, "Siswa", 8, NULL, "2024-08-08 15:28:31", "2024-08-08 15:28:31", NULL, NULL),
	(3, "Tendik", 8, 8, "2024-08-08 15:28:59", "2024-10-22 20:15:50", NULL, NULL);

/*!40000 ALTER TABLE `member_categories` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table members
# ------------------------------------------------------------

DROP TABLE IF EXISTS `members`;

CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `class_id` int(11) unsigned DEFAULT NULL,
  `identity_no` varchar(255) DEFAULT NULL,
  `identity_type` int(11) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `join_year` year(4) DEFAULT NULL,
  `join_date` date DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique_member` (`category_id`,`identity_no`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `class_id` (`class_id`),
  KEY `members_ibfk_5` (`identity_type`),
  CONSTRAINT `members_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `member_categories` (`id`),
  CONSTRAINT `members_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `members_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `members_ibfk_5` FOREIGN KEY (`identity_type`) REFERENCES `member_categories` (`id`),
  CONSTRAINT `members_ibfk_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;

INSERT INTO `members` (`id`, `category_id`, `class_id`, `identity_no`, `identity_type`, `name`, `join_year`, `join_date`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`, `deleted_by`) VALUES
	(1, 2, 1, "235368", 2, "NINDI ABEL OCTAVIA", 2022, "2026-04-08", 8, NULL, "2026-04-08 16:29:26", "2026-04-08 16:29:26", NULL, NULL),
	(2, 1, NULL, "12456", 1, "NAFIATUL MUAWANAH", 2022, "2026-04-08", 8, NULL, "2026-04-08 16:30:19", "2026-04-08 16:30:19", NULL, NULL),
	(3, 3, NULL, "52679172", 3, "PRAYOGI", 2022, "2026-04-08", 8, NULL, "2026-04-08 16:31:01", "2026-04-08 16:31:01", NULL, NULL);

/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table migrations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, "0001_01_01_000000_create_users_table", 1),
	(2, "0001_01_01_000001_create_cache_table", 1),
	(3, "0001_01_01_000002_create_jobs_table", 1);

/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table password_reset_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;





# Dump of table sessions
# ------------------------------------------------------------

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

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	("SOJKEiNpu6JVud3qZqCVtB6CCCT39jXoyvBbeeJ5", 8, "127.0.0.1", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0", "YTo3OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo4O3M6MTE6ImFjY2Vzc190eXBlIjtpOjI7czoxMToidGVuYW50X25hbWUiO3M6MTM6IlNNS04gMiBKZW1iZXIiO3M6NjoiX3Rva2VuIjtzOjQwOiJxSTVPdE1KNlkyWHN3QzdwQ05nbzJ6Y3VwbFZ2OG96bHFzT0pJNDNsIjtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MDp7fX1zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL21lbWJlci1jYXRlZ29yeSI7fXM6MjI6IlBIUERFQlVHQkFSX1NUQUNLX0RBVEEiO2E6MDp7fX0=", 1775642237);

/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table tenant_apps
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tenant_apps`;

CREATE TABLE `tenant_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `tenant_apps` WRITE;
/*!40000 ALTER TABLE `tenant_apps` DISABLE KEYS */;

INSERT INTO `tenant_apps` (`id`, `tenant_id`, `app_id`) VALUES
	(1, 1, 1);

/*!40000 ALTER TABLE `tenant_apps` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table tenants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tenants`;

CREATE TABLE `tenants` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `logo` varchar(200) NOT NULL,
  `registered_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;

INSERT INTO `tenants` (`id`, `name`, `logo`, `registered_at`) VALUES
	(1, "SMKN 2 Jember", "smkn-2-jember", "2024-12-25 14:27:33");

/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table user_access
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_access`;

CREATE TABLE `user_access` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tenant_ids` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `user_access` WRITE;
/*!40000 ALTER TABLE `user_access` DISABLE KEYS */;

INSERT INTO `user_access` (`id`, `app_id`, `user_id`, `tenant_ids`) VALUES
	(1, 1, 8, "[1, 4, 5]");

/*!40000 ALTER TABLE `user_access` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `access_type` int(11) DEFAULT NULL COMMENT '1: Super Admin, 2: Kepala Sekolah, 3: Karyawan',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_member_id_foreign` (`member_id`),
  CONSTRAINT `users_member_id_foreign`
    FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` 
(`id`, `tenant_id`, `member_id`, `access_type`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `is_active`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) 
VALUES
(8, 1, NULL, 2, "Rahmat Kepala Sekolah", "asd@asd.asd", NULL, "$2y$12$PL77yoR4FJ/5ePX46Vwpt.xC4Z7Kd0zyUyQ8ZoRabiIwi/YrJwHWi", NULL, 1, NULL, 0, "2025-05-14 03:46:46", NULL, NULL, NULL),

(9, 1, NULL, 1, "asd", "sinergi@perpack.id", NULL, "$2y$12$lDv44zsET.vW0.vKauHqB.FwvgcdR/jAgjM0j3t/6uUamxFOWgMPe", NULL, 1, "2024-10-21 16:13:08", 8, NULL, NULL, NULL, NULL),

(10, 1, NULL, 2, "asd", "rahmatrdn.dev@gmail.com", NULL, "$2y$12$kWbTCha03U5yHX2cw28W8ujtoDwXA8ivhQNmNK74bnM2eP/MTQjYK", NULL, 1, "2024-10-21 16:18:54", 8, "2024-10-21 16:52:18", 8, NULL, NULL),

(11, 1, NULL, 1, "Test Kepala Sekolah", "asd@asd.asdxxxx", NULL, "$2y$12$RZ4kZMAZrRNZ8bxTM86L5.Gm0vmkm/K6R55sJdxoAZ.Fu6TuivGT2", NULL, 1, "2024-10-21 16:20:01", 8, "2024-10-21 16:23:58", 8, "2024-10-22 13:22:32", 8),

(12, 1, 3, 3, "Prayogi", "yogi@asd.asd", NULL, "$2y$12$PL77yoR4FJ/5ePX46Vwpt.xC4Z7Kd0zyUyQ8ZoRabiIwi/YrJwHWi", NULL, 1, "2024-10-21 16:20:01", 8, "2024-10-21 16:23:58", 8, NULL, NULL),

(13, 1, 2, 3, "Nafiatul", "nafi@asd.asd", NULL, "$2y$12$PL77yoR4FJ/5ePX46Vwpt.xC4Z7Kd0zyUyQ8ZoRabiIwi/YrJwHWi", NULL, 1, "2024-10-21 16:20:01", 8, "2024-10-21 16:23:58", 8, NULL, NULL);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of views
# ------------------------------------------------------------

# Creating temporary tables to overcome VIEW dependency errors


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

# Dump completed on 2026-04-08T17:05:16+07:00
