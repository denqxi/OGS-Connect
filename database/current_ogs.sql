-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for ogs
CREATE DATABASE IF NOT EXISTS `ogs` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `ogs`;

-- Dumping structure for table ogs.accounts
CREATE TABLE IF NOT EXISTS `accounts` (
  `account_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operating_start_time` time DEFAULT NULL COMMENT 'Company operating hours start time (e.g., 07:00:00 for GLS)',
  `operating_end_time` time DEFAULT NULL COMMENT 'Company operating hours end time (e.g., 15:30:00 for GLS)',
  `company_rules` text COLLATE utf8mb4_unicode_ci COMMENT 'Company-specific rules and restrictions',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pay_rate` enum('50','120') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '50',
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.accounts: ~4 rows (approximately)
INSERT INTO `accounts` (`account_id`, `account_name`, `description`, `industry`, `operating_start_time`, `operating_end_time`, `company_rules`, `created_at`, `updated_at`, `pay_rate`) VALUES
	(1, 'GLS', 'Global Learning Solutions', 'Education', '07:00:00', '15:30:00', 'GLS operates from 7:00 AM to 3:30 PM only. No weekend availability.', '2025-11-19 19:59:40', '2025-11-19 19:59:40', '50'),
	(2, 'Tutlo', NULL, 'Education', NULL, NULL, 'Open hours - no time restrictions.', '2025-11-22 03:31:02', '2025-11-22 03:31:08', '50'),
	(3, 'Babilala', NULL, 'Education', '20:00:00', '22:00:00', 'Babilala operates from 8:00 PM to 10:00 PM only. Evening hours only.', '2025-11-22 03:31:29', '2025-11-22 03:31:30', '50'),
	(4, 'Talk915', NULL, 'Education', NULL, NULL, 'Open hours - no time restrictions.', '2025-11-22 03:31:50', '2025-11-22 03:31:51', '50');

-- Dumping structure for table ogs.applicants
CREATE TABLE IF NOT EXISTS `applicants` (
  `applicant_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ms_teams` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interview_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`applicant_id`),
  UNIQUE KEY `applicants_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.applicants: ~3 rows (approximately)
INSERT INTO `applicants` (`applicant_id`, `first_name`, `middle_name`, `last_name`, `birth_date`, `address`, `contact_number`, `email`, `ms_teams`, `interview_time`, `created_at`, `updated_at`) VALUES
	(2, 'James', NULL, 'Omosay', '2005-12-09', 'kbiyv', '09658475823', 'jamesomosay@gmail.com', 'eddiegeeeez', '2025-11-24 11:00:00', '2025-11-19 19:00:50', '2025-11-19 19:00:50'),
	(3, 'Test', 'Tutor', 'User', '1995-01-15', '123 Test Street, Test City', '09171234567', 'test.tutor@example.com', 'test.tutor@example.com', '2025-11-20 03:59:40', '2025-11-19 19:59:40', '2025-11-19 19:59:40'),
	(10, 'James', NULL, 'Omosay', '2005-12-09', 'kbiyv', '09658475823', 'jamesomosaytite@gmail.com', 'eddiegeeeez', '2025-11-21 14:57:00', '2025-11-19 22:58:59', '2025-11-19 22:58:59'),
	(11, 'don', 'd', 'don', '2004-01-14', 'd', '09123456789', 'princerandygonzales@gmail.com', '', '2025-12-05 23:31:00', '2025-12-04 07:27:54', '2025-12-04 08:00:06'),
	(12, 'John', 'Paul', 'Smith', '1990-01-15', '123 Example Street, Test City', '09171111111', 'john.smith@example.com', 'john.smith@example.com', '2025-12-07 12:30:48', '2025-12-07 04:30:48', '2025-12-07 04:30:48'),
	(13, 'Maria', 'Grace', 'Garcia', '1990-01-15', '123 Example Street, Test City', '09172222222', 'maria.garcia@example.com', 'maria.garcia@example.com', '2025-12-07 12:31:03', '2025-12-07 04:31:03', '2025-12-07 04:31:03'),
	(14, 'Sarah', 'Ann', 'Johnson', '1990-01-15', '123 Example Street, Test City', '09173333333', 'sarah.johnson@example.com', 'sarah.johnson@example.com', '2025-12-07 12:31:21', '2025-12-07 04:31:21', '2025-12-07 04:31:21'),
	(15, 'Michael', 'James', 'Brown', '1990-01-15', '123 Example Street, Test City', '09174444444', 'michael.brown@example.com', 'michael.brown@example.com', '2025-12-07 12:31:21', '2025-12-07 04:31:21', '2025-12-07 04:31:21'),
	(16, 'Emily', 'Rose', 'Davis', '1990-01-15', '123 Example Street, Test City', '09175555555', 'emily.davis@example.com', 'emily.davis@example.com', '2025-12-07 12:31:21', '2025-12-07 04:31:21', '2025-12-07 04:31:21'),
	(17, 'David', 'Robert', 'Wilson', '1990-01-15', '123 Example Street, Test City', '09176666666', 'david.wilson@example.com', 'david.wilson@example.com', '2025-12-07 12:31:21', '2025-12-07 04:31:21', '2025-12-07 04:31:21'),
	(18, 'Angela', 'Marie', 'Martinez', '1990-01-15', '123 Example Street, Test City', '09177777777', 'angela.martinez@example.com', 'angela.martinez@example.com', '2025-12-07 12:31:22', '2025-12-07 04:31:22', '2025-12-07 04:31:22'),
	(19, 'James', 'Edward', 'Taylor', '1990-01-15', '123 Example Street, Test City', '09178888888', 'james.taylor@example.com', 'james.taylor@example.com', '2025-12-07 12:31:22', '2025-12-07 04:31:22', '2025-12-07 04:31:22'),
	(20, 'Jennifer', 'Lynn', 'Anderson', '1990-01-15', '123 Example Street, Test City', '09179999999', 'jennifer.anderson@example.com', 'jennifer.anderson@example.com', '2025-12-07 12:31:22', '2025-12-07 04:31:22', '2025-12-07 04:31:22'),
	(21, 'Christopher', 'Lee', 'White', '1990-01-15', '123 Example Street, Test City', '09170000000', 'christopher.white@example.com', 'christopher.white@example.com', '2025-12-07 12:31:22', '2025-12-07 04:31:22', '2025-12-07 04:31:22'),
	(22, 'Lisa', 'Victoria', 'Harris', '1990-01-15', '123 Example Street, Test City', '09171010101', 'lisa.harris@example.com', 'lisa.harris@example.com', '2025-12-07 12:31:22', '2025-12-07 04:31:22', '2025-12-07 04:31:22');

-- Dumping structure for table ogs.applications
CREATE TABLE IF NOT EXISTS `applications` (
  `application_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `attempt_count` int NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `interviewer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `term_agreement` tinyint(1) NOT NULL DEFAULT '0',
  `application_date_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`application_id`),
  KEY `application_applicant_id_foreign` (`applicant_id`),
  CONSTRAINT `application_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.applications: ~1 rows (approximately)
INSERT INTO `applications` (`application_id`, `applicant_id`, `attempt_count`, `status`, `interviewer`, `notes`, `term_agreement`, `application_date_time`, `created_at`, `updated_at`, `source`) VALUES
	(1, 2, 0, 'pending', NULL, NULL, 1, '2025-11-20 03:00:50', '2025-11-19 19:00:50', '2025-11-19 19:00:50', NULL);

-- Dumping structure for table ogs.archive
CREATE TABLE IF NOT EXISTS `archive` (
  `archive_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `archive_by` bigint unsigned NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archive_date_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `archive_applicant_id_foreign` (`applicant_id`),
  KEY `archive_archive_by_foreign` (`archive_by`),
  CONSTRAINT `archive_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  CONSTRAINT `archive_archive_by_foreign` FOREIGN KEY (`archive_by`) REFERENCES `supervisors` (`supervisor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.archive: ~0 rows (approximately)

-- Dumping structure for table ogs.archived_applications
CREATE TABLE IF NOT EXISTS `archived_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ms_teams` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `esl_experience` text COLLATE utf8mb4_unicode_ci,
  `resume_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro_video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `speedtest` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `backup_device` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `platforms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `can_teach` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `interview_time` timestamp NULL DEFAULT NULL,
  `status` enum('recommended','not_recommended','pending','declined','no_answer','no_answer_3_attempts','re_schedule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `assigned_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interviewer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `attempt_count` int NOT NULL DEFAULT '0',
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `archived_applications_chk_1` CHECK (json_valid(`days`)),
  CONSTRAINT `archived_applications_chk_2` CHECK (json_valid(`platforms`)),
  CONSTRAINT `archived_applications_chk_3` CHECK (json_valid(`can_teach`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.archived_applications: ~0 rows (approximately)

-- Dumping structure for table ogs.assigned_daily_data
CREATE TABLE IF NOT EXISTS `assigned_daily_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_daily_data_id` bigint unsigned NOT NULL,
  `class_status` enum('active','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `main_tutor` bigint unsigned DEFAULT NULL,
  `backup_tutor` bigint unsigned DEFAULT NULL,
  `assigned_supervisor` bigint unsigned DEFAULT NULL,
  `finalized_at` timestamp NULL DEFAULT NULL,
  `finalized_by` bigint unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assigned_daily_data_schedule_daily_data_id_foreign` (`schedule_daily_data_id`),
  CONSTRAINT `assigned_daily_data_schedule_daily_data_id_foreign` FOREIGN KEY (`schedule_daily_data_id`) REFERENCES `schedules_daily_data` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.assigned_daily_data: ~0 rows (approximately)

-- Dumping structure for table ogs.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.cache: ~0 rows (approximately)

-- Dumping structure for table ogs.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.cache_locks: ~0 rows (approximately)

-- Dumping structure for table ogs.daily_data
CREATE TABLE IF NOT EXISTS `daily_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int NOT NULL DEFAULT '25',
  `date` date NOT NULL,
  `day` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_jst` time DEFAULT NULL,
  `time_pht` time DEFAULT NULL,
  `number_required` int NOT NULL DEFAULT '1',
  `schedule_status` enum('draft','tentative','finalized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `finalized_at` timestamp NULL DEFAULT NULL,
  `finalized_by` bigint unsigned DEFAULT NULL,
  `assigned_supervisor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `class_status` enum('active','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_data_school_class_date_time_jst_unique` (`school`,`class`,`date`,`time_jst`),
  KEY `daily_data_date_index` (`date`),
  KEY `daily_data_schedule_status_index` (`schedule_status`),
  KEY `daily_data_class_status_index` (`class_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.daily_data: ~0 rows (approximately)

-- Dumping structure for table ogs.demos
CREATE TABLE IF NOT EXISTS `demos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('onboarding','hired','rejected','pending') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.demos: ~0 rows (approximately)

-- Dumping structure for table ogs.employee_payment_information
CREATE TABLE IF NOT EXISTS `employee_payment_information` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `employee_type` enum('tutor','supervisor') NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_method_uppercase` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `gcash_number` varchar(50) DEFAULT NULL,
  `gcash_name` varchar(100) DEFAULT NULL,
  `paypal_email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_employee` (`employee_id`,`employee_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ogs.employee_payment_information: ~0 rows (approximately)

-- Dumping structure for table ogs.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table ogs.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.jobs: ~0 rows (approximately)

-- Dumping structure for table ogs.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.job_batches: ~0 rows (approximately)

-- Dumping structure for table ogs.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.migrations: ~45 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_11_19_004437_create_applicants_table', 1),
	(5, '2025_11_19_011754_create_qualification_table', 1),
	(6, '2025_11_19_012938_create_requirement_table', 1),
	(7, '2025_11_19_023232_create_referral_table', 1),
	(8, '2025_11_19_023648_create_application_table', 1),
	(9, '2025_11_19_030859_create_supervisor_table', 1),
	(10, '2025_11_19_045013_create_accounts_table', 1),
	(11, '2025_11_19_045108_create_screening_table', 1),
	(12, '2025_11_19_060822_create_archive_table', 1),
	(13, '2025_11_20_005353_create_work_preferences_table', 1),
	(14, '2025_11_20_015922_create_onboardings_table', 2),
	(15, '2025_11_20_020809_create_tutor_table', 3),
	(16, '2025_11_20_030000_add_interviewer_notes_to_application_table', 4),
	(17, '2025_11_20_040000_add_auth_fields_to_tutor_table', 4),
	(18, '2025_11_20_050000_add_auth_fields_to_supervisor_table', 4),
	(19, '2025_11_20_030000_modify_sessions_userid_to_string', 5),
	(20, '2025_11_20_080000_create_tutor_accounts_table', 6),
	(21, '2025_11_20_090000_create_daily_data_table', 7),
	(22, '2025_11_20_091000_create_tutor_assignments_table', 8),
	(23, '2025_11_20_092000_create_schedule_history_table', 8),
	(24, '2025_11_20_093000_fix_tutor_assignments_foreign_keys', 8),
	(25, '2025_11_20_095000_create_archived_applications_table', 9),
	(26, '2025_11_24_052459_create_tuto_details_table', 10),
	(27, '2025_11_24_052939_create_security_questions_table', 11),
	(28, '2025_11_24_000000_update_archived_applications_final_status', 12),
	(29, '2025_11_24_000001_create_notifications_table', 12),
	(30, '2025_11_24_000002_rename_final_status_to_status', 12),
	(31, '2025_11_24_000003_extend_archive_table', 12),
	(32, '2025_11_28_000000_create_tutor_availability_submissions_table', 12),
	(33, '2025_11_28_010000_add_status_to_tutor_work_details_table', 13),
	(34, '2025_11_28_000001_add_notifiable_columns_to_notifications_table', 14),
	(35, '2025_11_28_144934_create_tutor_work_details_table', 15),
	(36, '2025_11_28_153231_add_screenshot_to_tutor_work_details_table', 16),
	(37, '2025_11_29_000001_create_tutor_work_detail_approvals_table', 17),
	(38, '2025_11_29_000002_add_note_to_tutor_work_detail_approvals_table', 18),
	(39, '2025_11_29_000003_make_supervisor_id_nullable_in_tutor_work_detail_approvals', 18),
	(40, '2025_11_29_000004_modify_status_on_tutor_work_details', 19),
	(41, '2025_11_29_112232_update_status_enum_on_tutor_work_details_table', 19),
	(42, '2025_12_01_000000_cleanup_tutor_accounts_table', 20),
	(43, '2025_12_01_100000_restructure_tutor_accounts_and_add_company_rules', 20),
	(44, '2025_12_01_120000_remove_unused_tutor_columns', 21),
	(56, '2025_12_05_050815_create_payroll_history_table', 22),
	(57, '2025_12_05_120000_add_total_amount_to_payroll_history_table', 22),
	(58, '2025_12_04_160433_fix_tutors_table_column_names', 23);

-- Dumping structure for table ogs.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notifiable_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_is_read_index` (`is_read`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.notifications: ~17 rows (approximately)
INSERT INTO `notifications` (`id`, `notifiable_type`, `notifiable_id`, `type`, `title`, `message`, `icon`, `color`, `data`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
	(1, NULL, NULL, 'info', 'New Application Submitted', 'A new application has been submitted by don d don (princerandygonzales@gmail.com). Please review the application in the hiring & onboarding section.', 'fas fa-user-plus', 'blue', '{"submitted_at": "2025-12-04T15:27:54.319645Z", "applicant_name": "don d don", "application_id": 3, "applicant_email": "princerandygonzales@gmail.com"}', 1, '2025-12-04 07:39:21', '2025-12-04 07:27:54', '2025-12-04 07:39:21'),
	(2, NULL, NULL, 'success', 'Application Passed - Moved to Demo', 'Application for don don has passed and been moved to demo stage. Assigned to gls account.', 'fas fa-check-circle', 'green', '{"moved_at": "2025-12-04T15:31:19.728952Z", "new_status": "demo", "interviewer": "John M. Doe", "demo_schedule": "2025-12-05T23:31", "applicant_name": "don don", "application_id": null, "assigned_account": "gls"}', 1, '2025-12-04 07:39:13', '2025-12-04 07:31:19', '2025-12-04 07:39:13'),
	(3, NULL, NULL, 'success', 'Moved to Onboarding', 'Application for don don has been moved to onboarding phase.', 'fas fa-check-circle', 'green', '{"new_phase": "onboarding", "old_phase": "demo", "updated_at": "2025-12-04T16:00:11.795661Z", "onboarding_id": 2, "applicant_name": "don don", "assigned_account": "GLS"}', 1, '2025-12-04 20:40:19', '2025-12-04 08:00:11', '2025-12-04 20:40:19'),
	(5, NULL, NULL, 'success', 'New Tutor Registered', 'don don has been successfully registered as a tutor.', 'fas fa-user-plus', 'green', NULL, 1, '2025-12-04 20:40:19', '2025-12-04 08:08:44', '2025-12-04 20:40:19'),
	(6, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 1, "work_detail_id": 35}', 1, '2025-12-05 04:06:04', '2025-12-05 04:05:48', '2025-12-05 04:06:04'),
	(7, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 2, "work_detail_id": 35}', 1, '2025-12-05 04:06:09', '2025-12-05 04:05:48', '2025-12-05 04:06:09'),
	(8, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 1, "work_detail_id": 36}', 1, '2025-12-05 04:08:14', '2025-12-05 04:06:25', '2025-12-05 04:08:14'),
	(9, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 2, "work_detail_id": 36}', 1, '2025-12-05 04:08:15', '2025-12-05 04:06:25', '2025-12-05 04:08:15'),
	(10, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 1, "work_detail_id": 37}', 1, '2025-12-05 04:08:55', '2025-12-05 04:08:38', '2025-12-05 04:08:55'),
	(11, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 2, "work_detail_id": 37}', 1, '2025-12-05 04:08:55', '2025-12-05 04:08:38', '2025-12-05 04:08:55'),
	(12, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 1, "work_detail_id": 38}', 1, '2025-12-05 04:22:41', '2025-12-05 04:10:19', '2025-12-05 04:22:41'),
	(13, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 2, "work_detail_id": 38}', 1, '2025-12-05 04:22:41', '2025-12-05 04:10:19', '2025-12-05 04:22:41'),
	(14, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 1, "work_detail_id": 39}', 1, '2025-12-05 04:22:41', '2025-12-05 04:17:38', '2025-12-05 04:22:41'),
	(15, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "supervisor_id": 2, "work_detail_id": 39}', 1, '2025-12-05 04:22:41', '2025-12-05 04:17:38', '2025-12-05 04:22:41'),
	(16, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "work_detail_id": 40}', 1, '2025-12-05 04:22:41', '2025-12-05 04:21:07', '2025-12-05 04:22:41'),
	(17, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "work_detail_id": 40}', 1, '2025-12-05 04:22:41', '2025-12-05 04:21:07', '2025-12-05 04:22:41'),
	(18, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'don don has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0005", "work_type": "per class", "account_id": 1, "work_detail_id": 42, "supervisor_count": 0}', 1, '2025-12-05 04:32:37', '2025-12-05 04:28:20', '2025-12-05 04:32:37'),
	(19, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'David Wilson has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0011", "work_type": "per class", "account_id": 4, "work_detail_id": 43, "supervisor_count": 0}', 1, '2025-12-07 04:54:47', '2025-12-07 04:54:08', '2025-12-07 04:54:47'),
	(20, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'David Wilson has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0011", "work_type": "per class", "account_id": 4, "work_detail_id": 44, "supervisor_count": 0}', 1, '2025-12-07 05:26:07', '2025-12-07 05:10:05', '2025-12-07 05:26:07'),
	(21, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'Christopher White has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0015", "work_type": "hourly", "account_id": 2, "work_detail_id": 45, "supervisor_count": 0}', 0, NULL, '2025-12-07 05:44:32', '2025-12-07 05:44:32'),
	(22, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'Michael Brown has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0009", "work_type": "per class", "account_id": 4, "work_detail_id": 46, "supervisor_count": 0}', 0, NULL, '2025-12-07 05:46:54', '2025-12-07 05:46:54'),
	(23, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'Jennifer Anderson has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0014", "work_type": "hourly", "account_id": 2, "work_detail_id": 47, "supervisor_count": 0}', 0, NULL, '2025-12-07 05:52:04', '2025-12-07 05:52:04'),
	(24, NULL, NULL, 'work_detail_submitted', 'New Work Detail Submitted', 'Jennifer Anderson has submitted new work details for approval.', 'fas fa-clock', 'blue', '{"tutor_id": "OGS-T0014", "work_type": "hourly", "account_id": 2, "work_detail_id": 48, "supervisor_count": 0}', 0, NULL, '2025-12-07 05:52:36', '2025-12-07 05:52:36');

-- Dumping structure for table ogs.onboardings
CREATE TABLE IF NOT EXISTS `onboardings` (
  `onboarding_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `phase` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `asessed_by` bigint unsigned DEFAULT NULL,
  `onbaording_date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`onboarding_id`),
  KEY `onboardings_applicant_id_foreign` (`applicant_id`),
  KEY `onboardings_account_id_foreign` (`account_id`),
  KEY `onboardings_asessed_by_foreign` (`asessed_by`),
  CONSTRAINT `onboardings_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  CONSTRAINT `onboardings_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  CONSTRAINT `onboardings_asessed_by_foreign` FOREIGN KEY (`asessed_by`) REFERENCES `supervisors` (`supervisor_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.onboardings: ~1 rows (approximately)
INSERT INTO `onboardings` (`onboarding_id`, `applicant_id`, `account_id`, `phase`, `notes`, `created_at`, `updated_at`, `asessed_by`, `onbaording_date_time`) VALUES
	(1, 2, 2, 'phase3', NULL, NULL, NULL, NULL, NULL);

-- Dumping structure for table ogs.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table ogs.payroll_history
CREATE TABLE IF NOT EXISTS `payroll_history` (
  `payroll_history_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tutor_id` bigint unsigned NOT NULL,
  `pay_period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `submission_type` enum('email','pdf','print') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','pending','failed','draft') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`payroll_history_id`),
  KEY `payroll_history_tutor_id_foreign` (`tutor_id`),
  CONSTRAINT `payroll_history_tutor_id_foreign` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`tutor_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.payroll_history: ~0 rows (approximately)
INSERT INTO `payroll_history` (`payroll_history_id`, `tutor_id`, `pay_period`, `total_amount`, `submission_type`, `status`, `recipient_email`, `notes`, `submitted_at`, `created_at`, `updated_at`) VALUES
	(1, 6, '2025-12', 150.00, 'email', 'sent', 'princerandygonzales@gmail.com', NULL, '2025-12-05 17:35:27', '2025-12-05 17:35:27', '2025-12-05 17:35:27');

-- Dumping structure for table ogs.qualification
CREATE TABLE IF NOT EXISTS `qualification` (
  `applicant_qualification_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `education` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `esl_experience` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`applicant_qualification_id`),
  KEY `qualification_applicant_id_foreign` (`applicant_id`),
  CONSTRAINT `qualification_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.qualification: ~2 rows (approximately)
INSERT INTO `qualification` (`applicant_qualification_id`, `applicant_id`, `education`, `esl_experience`, `created_at`, `updated_at`) VALUES
	(2, 2, 'bachelor', 'na', '2025-11-19 19:00:50', '2025-11-19 19:00:50'),
	(3, 10, 'college_undergrad', '1-2', '2025-11-19 22:58:59', '2025-11-19 22:58:59'),
	(4, 11, 'college_undergrad', 'na', '2025-12-04 07:27:54', '2025-12-04 07:27:54');

-- Dumping structure for table ogs.referral
CREATE TABLE IF NOT EXISTS `referral` (
  `applicant_referral_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `source` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referrer_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`applicant_referral_id`),
  KEY `referral_applicant_id_foreign` (`applicant_id`),
  CONSTRAINT `referral_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.referral: ~2 rows (approximately)
INSERT INTO `referral` (`applicant_referral_id`, `applicant_id`, `source`, `referrer_name`, `created_at`, `updated_at`) VALUES
	(2, 2, 'fb_boosting', NULL, '2025-11-19 19:00:50', '2025-11-19 19:00:50'),
	(3, 10, 'fb_boosting', NULL, '2025-11-19 22:58:59', '2025-11-19 22:58:59'),
	(4, 11, 'fb_boosting', NULL, '2025-12-04 07:27:54', '2025-12-04 07:27:54');

-- Dumping structure for table ogs.requirement
CREATE TABLE IF NOT EXISTS `requirement` (
  `applicant_requirement_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `resume_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intro_video` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `speedtest` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_devices` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `backup_devices` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`applicant_requirement_id`),
  KEY `requirement_applicant_id_foreign` (`applicant_id`),
  CONSTRAINT `requirement_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.requirement: ~2 rows (approximately)
INSERT INTO `requirement` (`applicant_requirement_id`, `applicant_id`, `resume_link`, `intro_video`, `work_type`, `speedtest`, `main_devices`, `backup_devices`, `created_at`, `updated_at`) VALUES
	(2, 2, 'http://samplepage.io/home167', 'https://samplepage.io/services108', 'work_at_site', NULL, NULL, NULL, '2025-11-19 19:00:50', '2025-11-19 19:00:50'),
	(3, 10, 'http://samplepage.io/home167', 'https://samplepage.io/services108', 'work_from_home', 'https://samplepage.io/services108', 'https://samplepage.io/services108', 'https://samplepage.io/services108', '2025-11-19 22:58:59', '2025-11-19 22:58:59'),
	(4, 11, 'https://docs.google.com/document/d/1GxSFtb2f7g8TcnGzKx--z0IfxQVm0UfVtynS_jtQpAQ/edit?usp=drive_link', 'https://docs.google.com/document/d/1GxSFtb2f7g8TcnGzKx--z0IfxQVm0UfVtynS_jtQpAQ/edit?usp=drive_link', 'work_at_site', NULL, NULL, NULL, '2025-12-04 07:27:54', '2025-12-04 07:27:54');

-- Dumping structure for table ogs.schedules_daily_data
CREATE TABLE IF NOT EXISTS `schedules_daily_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `day` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` time NOT NULL,
  `duration` int NOT NULL DEFAULT '25',
  `school` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedules_daily_data_school_class_date_time_unique` (`school`,`class`,`date`,`time`),
  KEY `schedules_daily_data_date_index` (`date`),
  KEY `schedules_daily_data_school_class_index` (`school`,`class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.schedules_daily_data: ~0 rows (approximately)

-- Dumping structure for table ogs.schedule_history
CREATE TABLE IF NOT EXISTS `schedule_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_date` date NOT NULL,
  `class_time` time DEFAULT NULL,
  `status` enum('draft','tentative','finalized','cancelled','rescheduled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `action` enum('created','updated','finalized','cancelled','rescheduled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'created',
  `performed_by` bigint unsigned DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule_history_class_id_action_index` (`class_id`,`action`),
  KEY `schedule_history_status_created_at_index` (`status`,`created_at`),
  KEY `schedule_history_performed_by_index` (`performed_by`),
  CONSTRAINT `schedule_history_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedule_history_chk_1` CHECK (json_valid(`old_data`)),
  CONSTRAINT `schedule_history_chk_2` CHECK (json_valid(`new_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.schedule_history: ~0 rows (approximately)

-- Dumping structure for table ogs.screening
CREATE TABLE IF NOT EXISTS `screening` (
  `screening_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `supervisor_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `phase` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `results` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `screening_date_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`screening_id`),
  KEY `screening_applicant_id_foreign` (`applicant_id`),
  KEY `screening_supervisor_id_foreign` (`supervisor_id`),
  KEY `screening_account_id_foreign` (`account_id`),
  CONSTRAINT `screening_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  CONSTRAINT `screening_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  CONSTRAINT `screening_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`supervisor_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.screening: ~0 rows (approximately)
INSERT INTO `screening` (`screening_id`, `applicant_id`, `supervisor_id`, `account_id`, `phase`, `results`, `notes`, `screening_date_time`, `created_at`, `updated_at`) VALUES
	(1, 2, 2, 2, 'phase3', 'pending', NULL, '2025-11-22 12:45:51', NULL, NULL);

-- Dumping structure for table ogs.security_questions
CREATE TABLE IF NOT EXISTS `security_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.security_questions: ~2 rows (approximately)
INSERT INTO `security_questions` (`id`, `user_type`, `user_id`, `question`, `answer_hash`, `created_at`, `updated_at`) VALUES
	(1, 'tutor', 'OGS-T0005', 'What is your favorite color?', '$2y$12$511HPnCxZ9e3xLMTa6I71uz9bZZtTrKm2MSRCTUtBxz.YDhoJSphe', '2025-12-05 03:54:10', '2025-12-05 03:54:10'),
	(2, 'tutor', 'OGS-T0005', 'What city were you born in?', '$2y$12$5HFfMonyuvEw3YPa2yUdfOnt5LqyQw492N9NkpEBHi.a.rkwWqjbq', '2025-12-05 03:54:10', '2025-12-05 03:54:10');

-- Dumping structure for table ogs.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('p7yidTnRDXwL1lNtEOdnGeEFDdw8PZPyOT1e3hOs', 'OGS-T0011', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoieE5KenVaMm1QcWpvbVRBTlBkNnlsU2luTjV2aWlaV1Z5NzdlckJaOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90dXRvci9ub3RpZmljYXRpb25zL2FwaT9saW1pdD0xMCI7fXM6NTI6ImxvZ2luX3R1dG9yXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO3M6OToiT0dTLVQwMDExIjtzOjIwOiJzdXBlcnZpc29yX2xvZ2dlZF9pbiI7YjowO3M6ODoidHV0b3JfaWQiO3M6OToiT0dTLVQwMDExIjtzOjE0OiJ0dXRvcl91c2VybmFtZSI7czoxMToiZGF2aWR3aWxzb24iO30=', 1765116889),
	('TxSr5N93lP5eCvSmElbyjgWMd8fSppO1oryDkvEC', 'OGS-S1006', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVGF3WGFFSkw4TndJNk9COUh6dTF0Q1UyRDltVVZkZkpkc3VVemZmcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zL2FwaSI7fXM6NTc6ImxvZ2luX3N1cGVydmlzb3JfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7czo5OiJPR1MtUzEwMDYiO3M6MjA6InN1cGVydmlzb3JfbG9nZ2VkX2luIjtiOjE7czoxMzoic3VwZXJ2aXNvcl9pZCI7czo5OiJPR1MtUzEwMDYiO30=', 1765116890);

-- Dumping structure for table ogs.supervisors
CREATE TABLE IF NOT EXISTS `supervisors` (
  `supervisor_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supID` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_account` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ms_teams` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shift` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`supervisor_id`),
  UNIQUE KEY `supervisor_email_unique` (`email`),
  UNIQUE KEY `supervisor_supid_unique` (`supID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.supervisors: ~2 rows (approximately)
INSERT INTO `supervisors` (`supervisor_id`, `supID`, `password`, `remember_token`, `status`, `first_name`, `middle_name`, `last_name`, `birth_date`, `email`, `contact_number`, `assigned_account`, `ms_teams`, `shift`, `created_at`, `updated_at`) VALUES
	(1, 'OGS-S1001', '$2y$12$cHOgqhKvEeWwK481GvEfkuDnyqSAHuTYYurl/RfcIqZnkran9pT5G', 'kLHvyn6skzllQUyEotJ5O0vmtxcVbaC8XFpFbDYk9apkLbp4BQH8fE4Qd5nv', 'active', 'Admin', 'A', 'Supervisor', '1985-03-15', 'admin@ogsconnect.com', '09171234567', 'GLS', 'admin.supervisor@ogsconnect.com', NULL, '2025-11-19 19:59:14', '2025-11-19 19:59:14'),
	(2, 'OGS-S1002', '$2y$12$K0czA5iMhe4F5VSyKk3aUu.DTxiJBpSjDJB/ACIImw.q1K9sIiiJS', NULL, 'active', 'John', 'M.', 'Doe', '1990-01-01', 'dummy@ogsconnect.com', '09123456789', 'GLS', 'john.doe@teams.com', NULL, '2025-11-21 19:03:57', '2025-11-21 19:03:57'),
	(4, 'OGS-S1003', '$2y$12$FwyzjDHqE8dRBl0hRtjKV.ZvVcMGUfckYzUYX2WNSXO1fm8ndVmwa', NULL, 'active', 'Robert', 'M', 'Johnson', '1985-05-20', 'robert.johnson@ogsconnect.com', '09171234567', 'GLS\r\n', 'robert.johnson@ogsconnect.com', 'day', '2025-12-07 04:30:42', '2025-12-07 04:30:42'),
	(5, 'OGS-S1004', '$2y$12$Clncr13Qzq4YVN.v44RCjuTnRyyRMkxUsiLQV8Fa6aYdWO6pcBd5a', NULL, 'active', 'Patricia', 'A', 'Williams', '1985-05-20', 'patricia.williams@ogsconnect.com', '09172345678', 'talk915', 'patricia.williams@ogsconnect.com', 'day', '2025-12-07 04:30:42', '2025-12-07 04:30:42'),
	(6, 'OGS-S1005', '$2y$12$U4Z8t3o2Z1uEAf254xma/OqnxzkgtjH6HIeNKlC/PEn9RVk32y.o2', NULL, 'active', 'Thomas', 'D', 'Brown', '1985-05-20', 'thomas.brown@ogsconnect.com', '09173456789', 'babilala', 'thomas.brown@ogsconnect.com', 'day', '2025-12-07 04:30:42', '2025-12-07 04:30:42'),
	(7, 'OGS-S1006', '$2y$12$GHbL3g7a2KIjQJeQO0yMQuoEewp3.nbmicj3eC2iF/ocDIyvZFH2G', NULL, 'active', 'Jennifer', 'K', 'Davis', '1985-05-20', 'jennifer.davis@ogsconnect.com', '09174567890', 'tutlo', 'jennifer.davis@ogsconnect.com', 'day', '2025-12-07 04:30:42', '2025-12-07 04:30:42');

-- Dumping structure for table ogs.tutors
CREATE TABLE IF NOT EXISTS `tutors` (
  `tutor_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tutorID` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applicant_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `hire_date_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `Column 16` int DEFAULT NULL,
  PRIMARY KEY (`tutor_id`),
  UNIQUE KEY `tutor_tutorid_unique` (`tutorID`),
  UNIQUE KEY `tutor_tusername_unique` (`username`),
  UNIQUE KEY `tutor_email_unique` (`email`),
  KEY `tutor_applicant_id_foreign` (`applicant_id`),
  KEY `tutor_account_id_foreign` (`account_id`),
  CONSTRAINT `tutor_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  CONSTRAINT `tutor_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.tutors: ~5 rows (approximately)
INSERT INTO `tutors` (`tutor_id`, `tutorID`, `username`, `email`, `password`, `phone_number`, `sex`, `date_of_birth`, `status`, `remember_token`, `applicant_id`, `account_id`, `hire_date_time`, `created_at`, `updated_at`, `Column 16`) VALUES
	(1, 'OGS-T0001', 'testtutor', 'test.tutor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09171234567', 'male', '1995-01-15', 'active', NULL, 3, 1, '2024-11-20 00:00:00', '2025-11-19 19:59:40', '2025-11-19 19:59:40', NULL),
	(2, 'OGS-T0002', 'dummy', 'dummy.tutor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09272393121', 'male', '2025-11-24', 'active', NULL, 2, 1, '2025-11-22 00:00:00', '2025-11-24 04:52:24', '2025-11-24 04:52:25', NULL),
	(4, 'OGS-T0004', 'idk', 'dummydumbbed@gmail.com', '$2b$12$D39IkyJW29SlxV5/FQ/pNO0ux6qqD61raS4BzgBU1UKm.afOySvzC', NULL, NULL, NULL, 'active', NULL, 2, 1, '2025-12-04 00:00:00', '2025-12-04 15:55:37', '2025-12-04 15:55:38', NULL),
	(6, 'OGS-T0003', NULL, 'princerandygonzales@gmail.com', '$2y$12$HYRZnwdGY4haMfN0Jj44.e3cf8HV8gnPQ7D2YOqnzE194x44zo596', NULL, NULL, NULL, 'active', 'PZCMMQVUauLlqKEzOvUjqKv7M7yT0paOVXPidMYTArW9Jncl4DWpFqsuTaOQ', 2, 1, '2023-11-22 00:00:00', '2025-11-23 21:16:52', '2025-11-27 16:30:33', NULL),
	(8, 'OGS-T0005', 'dondon', 'dondon@ogsconnect.com', '$2y$12$G.CpA2vpFz0Dmp2xUxtfe.15xHbmwtKD2fBo0wbkwMiEfkqllhR4y', NULL, NULL, NULL, 'active', NULL, 11, 1, NULL, '2025-12-04 08:08:44', '2025-12-04 08:08:44', NULL),
	(9, 'OGS-T0006', 'johnsmith', 'john.smith@example.com', '$2y$12$4nQojgNjzsKwhrXDej3L.O/b4yxN9gZQ6256IaC23oiDumL/RpkEy', NULL, NULL, NULL, 'active', NULL, 12, 1, NULL, '2025-12-07 04:30:48', '2025-12-07 04:30:48', NULL),
	(10, 'OGS-T0007', 'mariagarcia', 'maria.garcia@example.com', '$2y$12$miNnS/EZsPTb7zh1obNKbun7gOJWpFlAVnocOnNSaEu/LKUXqcIGO', NULL, NULL, NULL, 'active', NULL, 13, 1, NULL, '2025-12-07 04:31:03', '2025-12-07 04:31:03', NULL),
	(11, 'OGS-T0008', 'sarahjohnson', 'sarah.johnson@example.com', '$2y$12$9vnTq4aB8GPuCy.8zyWetuf5ZUVVKCDYb.eoJ5i0saoQWjgmSyO6K', NULL, NULL, NULL, 'active', NULL, 14, 1, NULL, '2025-12-07 04:31:21', '2025-12-07 04:31:21', NULL),
	(12, 'OGS-T0009', 'michaelbrown', 'michael.brown@example.com', '$2y$12$qo2vpNCdyJJfxyk6eULLIeArnxyHHszbj/VSnPE9mzD3fo4lKX/UO', NULL, NULL, NULL, 'active', NULL, 15, 4, NULL, '2025-12-07 04:31:21', '2025-12-07 04:31:21', NULL),
	(13, 'OGS-T0010', 'emilydavis', 'emily.davis@example.com', '$2y$12$aht7tf3GCjERN81agCCk1OITzHyfnTCKfqR2J/0Y/EN/ZGw6VXMYO', NULL, NULL, NULL, 'active', NULL, 16, 4, NULL, '2025-12-07 04:31:21', '2025-12-07 04:31:21', NULL),
	(14, 'OGS-T0011', 'davidwilson', 'david.wilson@example.com', '$2y$12$PlurrjTlBYR14MK9naDhU.xfEsJemnQFKuGTszH3LdWitinVCBygG', NULL, NULL, NULL, 'active', NULL, 17, 4, NULL, '2025-12-07 04:31:22', '2025-12-07 04:31:22', NULL),
	(15, 'OGS-T0012', 'angelamartinez', 'angela.martinez@example.com', '$2y$12$raVKYZWgAAiUA50L7SvBSueq4YVGuoasrrfhEqD9DrWsquw85stOC', NULL, NULL, NULL, 'active', NULL, 18, 3, NULL, '2025-12-07 04:31:22', '2025-12-07 04:31:22', NULL),
	(16, 'OGS-T0013', 'jamestaylor', 'james.taylor@example.com', '$2y$12$mPg5NWjR5Cs08Zuf8X2Hiu2gW3FmWx9xQQk9AiG0gsmrIBe4pIKtm', NULL, NULL, NULL, 'active', NULL, 19, 3, NULL, '2025-12-07 04:31:22', '2025-12-07 04:31:22', NULL),
	(17, 'OGS-T0014', 'jenniferanderson', 'jennifer.anderson@example.com', '$2y$12$h1E7FWTNxOKfNXqTrF.Vy..3JoXE0lUe/joqydE5tK1XDxDbkiLVC', NULL, NULL, NULL, 'active', NULL, 20, 2, NULL, '2025-12-07 04:31:22', '2025-12-07 04:31:22', NULL),
	(18, 'OGS-T0015', 'christopherwhite', 'christopher.white@example.com', '$2y$12$y93d1nkj2M0GHAP3wWgVQeuPhwUwu7FwSPPvfwOlXJIUOBmEhbX0q', NULL, NULL, NULL, 'active', NULL, 21, 2, NULL, '2025-12-07 04:31:22', '2025-12-07 04:31:22', NULL),
	(19, 'OGS-T0016', 'lisaharris', 'lisa.harris@example.com', '$2y$12$xHzRCHp23aqRqk/gdYDL6Ow/4cEJNCMQXDMzyF26stySk7oiXkEHa', NULL, NULL, NULL, 'active', NULL, 22, 2, NULL, '2025-12-07 04:31:23', '2025-12-07 04:31:23', NULL);

-- Dumping structure for table ogs.tutor_accounts
CREATE TABLE IF NOT EXISTS `tutor_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tutor_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `available_days` json DEFAULT NULL,
  `available_times` json DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tutor_accounts_tutor_id_account_id_unique` (`tutor_id`,`account_id`),
  KEY `tutor_accounts_account_id_foreign` (`account_id`),
  CONSTRAINT `tutor_accounts_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  CONSTRAINT `tutor_accounts_tutor_id_foreign` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`tutor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.tutor_accounts: ~0 rows (approximately)

-- Dumping structure for table ogs.tutor_assignments
CREATE TABLE IF NOT EXISTS `tutor_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `daily_data_id` bigint unsigned NOT NULL,
  `tutor_id` bigint unsigned NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `similarity_score` decimal(5,4) DEFAULT NULL,
  `is_backup` tinyint(1) NOT NULL DEFAULT '0',
  `was_promoted_from_backup` tinyint(1) NOT NULL DEFAULT '0',
  `replaced_tutor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `promoted_at` timestamp NULL DEFAULT NULL,
  `status` enum('assigned','confirmed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_assignments_daily_data_id_foreign` (`daily_data_id`),
  KEY `tutor_assignments_tutor_id_foreign` (`tutor_id`),
  CONSTRAINT `tutor_assignments_tutor_id_foreign` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`tutor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.tutor_assignments: ~0 rows (approximately)

-- Dumping structure for table ogs.tutor_availability_submissions
CREATE TABLE IF NOT EXISTS `tutor_availability_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tutor_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitted_days` json NOT NULL,
  `submitted_times` json NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `supervisor_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_availability_submissions_tutor_id_index` (`tutor_id`),
  KEY `tutor_availability_submissions_supervisor_id_index` (`supervisor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.tutor_availability_submissions: ~0 rows (approximately)

-- Dumping structure for table ogs.tutor_details
CREATE TABLE IF NOT EXISTS `tutor_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tutor_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `esl_experience` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_setup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_day_teaching` date DEFAULT NULL,
  `educational_attainment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_details_tutor_id_foreign` (`tutor_id`),
  CONSTRAINT `tutor_details_tutor_id_foreign` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`tutorID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.tutor_details: ~0 rows (approximately)

-- Dumping structure for table ogs.tutor_work_details
CREATE TABLE IF NOT EXISTS `tutor_work_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tutor_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_type` enum('hourly','per class') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `status` enum('pending','approved','reject') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `screenshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate_per_hour` decimal(20,2) DEFAULT '120.00',
  `rate_per_class` decimal(20,2) DEFAULT '50.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_work_details_tutor_id_foreign` (`tutor_id`),
  CONSTRAINT `tutor_work_details_tutor_id_foreign` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`tutorID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.tutor_work_details: ~26 rows (approximately)
INSERT INTO `tutor_work_details` (`id`, `tutor_id`, `work_type`, `start_time`, `end_time`, `duration_minutes`, `status`, `screenshot`, `rate_per_hour`, `rate_per_class`, `created_at`, `updated_at`, `note`) VALUES
	(11, 'OGS-T0003', 'per class', '14:32:00', '14:32:00', NULL, 'approved', 'tutor_work_screenshots/1764397937_Screenshot 2025-11-28 223446.png', 120.00, 50.00, '2025-11-28 22:32:17', '2025-11-29 02:08:26', NULL),
	(12, 'OGS-T0003', 'per class', '14:43:00', '14:46:00', NULL, 'approved', 'tutor_work_screenshots/1764398633_Screenshot 2025-11-24 134358.png', 120.00, 50.00, '2025-11-28 22:43:53', '2025-11-29 02:26:35', NULL),
	(13, 'OGS-T0003', 'per class', '14:47:00', '14:50:00', NULL, 'approved', 'tutor_work_screenshots/1764398874_Screenshot 2025-11-28 223446.png', 120.00, 50.00, '2025-11-28 22:47:54', '2025-11-29 17:35:11', NULL),
	(14, 'OGS-T0003', 'per class', '15:11:00', '15:11:00', NULL, 'approved', 'tutor_work_screenshots/1764400288_Screenshot 2025-10-02 191834.png', 120.00, 50.00, '2025-11-28 23:11:29', '2025-11-29 17:38:18', NULL),
	(15, 'OGS-T0003', 'per class', '17:51:00', '19:51:00', NULL, 'approved', 'tutor_work_screenshots/1764409882_Screenshot 2025-11-27 131246.png', 120.00, 50.00, '2025-11-29 01:51:22', '2025-11-29 02:30:20', NULL),
	(16, 'OGS-T0003', 'per class', '09:37:00', '09:37:00', NULL, 'approved', 'tutor_work_screenshots/1764466682_Screenshot 2025-11-05 150335.png', 120.00, 50.00, '2025-11-29 17:38:03', '2025-11-29 18:39:32', NULL),
	(19, 'OGS-T0003', 'per class', '10:49:00', '10:49:00', NULL, 'approved', 'tutor_work_screenshots/1764470977_Screenshot 2025-11-29 184236.png', 120.00, 50.00, '2025-11-29 18:49:37', '2025-11-29 18:50:36', NULL),
	(20, 'OGS-T0003', 'per class', '11:37:00', '11:37:00', NULL, 'approved', 'tutor_work_screenshots/1764473865_Screenshot 2025-11-29 184236.png', 120.00, 50.00, '2025-11-29 19:37:45', '2025-11-29 19:38:30', NULL),
	(21, 'OGS-T0003', 'per class', '18:29:00', '18:31:00', NULL, 'approved', 'tutor_work_screenshots/1764498571_Screenshot (8).png', 120.00, 50.00, '2025-11-30 02:29:31', '2025-11-30 02:32:06', NULL),
	(22, 'OGS-T0003', 'per class', '21:52:00', '21:52:00', NULL, 'approved', 'tutor_work_screenshots/1764510776_Screenshot (6).png', 120.00, 50.00, '2025-11-30 05:52:57', '2025-11-30 05:53:33', NULL),
	(23, 'OGS-T0003', 'per class', '13:11:00', '16:11:00', NULL, 'approved', 'tutor_work_screenshots/1764515479_Screenshot 2025-11-30 185752.png', 120.00, 50.00, '2025-11-30 07:11:19', '2025-11-30 07:15:13', NULL),
	(24, 'OGS-T0003', 'per class', '08:51:00', '11:51:00', NULL, 'approved', 'tutor_work_screenshots/1764550326_poco f7.jpg', 120.00, 50.00, '2025-11-30 16:52:08', '2025-12-01 00:03:00', NULL),
	(25, 'OGS-T0003', 'per class', '16:04:00', '16:05:00', NULL, 'approved', 'tutor_work_screenshots/1764576306_Frame 1 (2).png', 120.00, 50.00, '2025-12-01 00:05:07', '2025-12-01 00:05:27', NULL),
	(26, 'OGS-T0003', 'per class', '16:05:00', '16:05:00', NULL, 'approved', 'tutor_work_screenshots/1764576317_b8d84d67-3e82-4f85-9712-63f458399f0a.jpg', 120.00, 50.00, '2025-12-01 00:05:17', '2025-12-01 00:05:25', NULL),
	(27, 'OGS-T0005', 'per class', '00:10:00', '00:10:00', NULL, 'reject', 'tutor_work_screenshots/1764864613_emergency.jpg', 120.00, 50.00, '2025-12-04 08:10:13', '2025-12-05 03:30:13', NULL),
	(28, 'OGS-T0005', 'per class', '00:10:00', '00:10:00', NULL, 'approved', 'tutor_work_screenshots/1764864614_emergency.jpg', 120.00, 50.00, '2025-12-04 08:10:14', '2025-12-05 03:29:44', NULL),
	(29, 'OGS-T0005', 'per class', '00:11:00', '00:11:00', NULL, 'approved', 'tutor_work_screenshots/1764864680_poco f7.jpg', 120.00, 50.00, '2025-12-04 08:11:20', '2025-12-04 08:18:29', NULL),
	(30, 'OGS-T0005', 'per class', '19:44:00', '19:44:00', NULL, 'pending', 'tutor_work_screenshots/1764935048_emergency.jpg', 120.00, 50.00, '2025-12-05 03:44:08', '2025-12-05 03:44:08', NULL),
	(31, 'OGS-T0005', 'per class', '19:44:00', '19:44:00', NULL, 'pending', 'tutor_work_screenshots/1764935092_emergency.jpg', 120.00, 50.00, '2025-12-05 03:44:52', '2025-12-05 03:44:52', NULL),
	(32, 'OGS-T0005', 'per class', '19:45:00', '19:45:00', NULL, 'pending', 'tutor_work_screenshots/1764935133_emergency.jpg', 120.00, 50.00, '2025-12-05 03:45:33', '2025-12-05 03:45:33', NULL),
	(33, 'OGS-T0005', 'per class', '19:48:00', '20:47:00', NULL, 'pending', 'tutor_work_screenshots/1764935237_poco f7.jpg', 120.00, 50.00, '2025-12-05 03:47:17', '2025-12-05 03:47:17', NULL),
	(34, 'OGS-T0005', 'per class', '19:57:00', '19:57:00', NULL, 'pending', 'tutor_work_screenshots/1764935877_emergency.jpg', 120.00, 50.00, '2025-12-05 03:57:57', '2025-12-05 03:57:57', NULL),
	(35, 'OGS-T0005', 'per class', '20:05:00', '20:05:00', NULL, 'pending', 'tutor_work_screenshots/1764936348_b8d84d67-3e82-4f85-9712-63f458399f0a.jpg', 120.00, 50.00, '2025-12-05 04:05:48', '2025-12-05 04:05:48', NULL),
	(36, 'OGS-T0005', 'per class', '20:06:00', '20:06:00', NULL, 'pending', 'tutor_work_screenshots/1764936385_Frame 1 (2).png', 120.00, 50.00, '2025-12-05 04:06:25', '2025-12-05 04:06:25', NULL),
	(37, 'OGS-T0005', 'per class', '20:08:00', '20:08:00', NULL, 'pending', 'tutor_work_screenshots/1764936518_download (3).jpg', 120.00, 50.00, '2025-12-05 04:08:38', '2025-12-05 04:08:38', NULL),
	(38, 'OGS-T0005', 'per class', '20:10:00', '20:10:00', NULL, 'pending', 'tutor_work_screenshots/1764936619_Screenshot 2025-12-01 174825.png', 120.00, 50.00, '2025-12-05 04:10:19', '2025-12-05 04:10:19', NULL),
	(43, 'OGS-T0011', 'per class', '20:53:00', '20:54:00', NULL, 'approved', 'tutor_work_screenshots/1765112047_Screenshot (6).png', 120.00, 50.00, '2025-12-07 04:54:08', '2025-12-07 04:58:52', NULL),
	(44, 'OGS-T0011', 'per class', '21:09:00', '22:09:00', NULL, 'pending', 'tutor_work_screenshots/1765113005_Screenshot (6).png', 120.00, 50.00, '2025-12-07 05:10:05', '2025-12-07 05:10:05', NULL),
	(45, 'OGS-T0015', 'hourly', '21:43:00', '00:43:00', -180, 'pending', 'tutor_work_screenshots/1765115072_Screenshot (6).png', 120.00, 0.00, '2025-12-07 05:44:32', '2025-12-07 05:44:32', NULL),
	(46, 'OGS-T0009', 'per class', '21:45:00', '21:47:00', -2, 'pending', 'tutor_work_screenshots/1765115214_Screenshot (6).png', 0.00, 50.00, '2025-12-07 05:46:54', '2025-12-07 05:46:54', NULL),
	(47, 'OGS-T0014', 'hourly', '21:51:00', '12:51:00', 900, 'reject', 'tutor_work_screenshots/1765115524_Screenshot (6).png', 120.00, 0.00, '2025-12-07 05:52:04', '2025-12-07 05:57:05', NULL),
	(48, 'OGS-T0014', 'hourly', '21:52:00', '00:52:00', 180, 'pending', 'tutor_work_screenshots/1765115556_Screenshot (6).png', 120.00, 0.00, '2025-12-07 05:52:36', '2025-12-07 05:52:36', NULL);

-- Dumping structure for table ogs.tutor_work_detail_approvals
CREATE TABLE IF NOT EXISTS `tutor_work_detail_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `work_detail_id` bigint unsigned NOT NULL,
  `supervisor_id` bigint unsigned DEFAULT NULL,
  `old_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_work_detail_approvals_work_detail_id_foreign` (`work_detail_id`),
  KEY `tutor_work_detail_approvals_supervisor_id_foreign` (`supervisor_id`),
  CONSTRAINT `tutor_work_detail_approvals_supervisor_id_foreign` FOREIGN KEY (`supervisor_id`) REFERENCES `supervisors` (`supervisor_id`) ON DELETE CASCADE,
  CONSTRAINT `tutor_work_detail_approvals_work_detail_id_foreign` FOREIGN KEY (`work_detail_id`) REFERENCES `tutor_work_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.tutor_work_detail_approvals: ~17 rows (approximately)
INSERT INTO `tutor_work_detail_approvals` (`id`, `work_detail_id`, `supervisor_id`, `old_status`, `new_status`, `approved_at`, `note`, `created_at`, `updated_at`) VALUES
	(1, 12, 2, 'pending', 'approved', '2025-11-29 02:26:35', NULL, '2025-11-29 02:26:35', '2025-11-29 02:26:35'),
	(2, 15, 2, 'pending', 'approved', '2025-11-29 02:30:20', NULL, '2025-11-29 02:30:20', '2025-11-29 02:30:20'),
	(3, 14, 2, 'pending', 'reject', '2025-11-29 03:24:25', 'ytdgtyugihoj', '2025-11-29 03:24:25', '2025-11-29 03:24:25'),
	(4, 13, 2, 'pending', 'approved', '2025-11-29 17:35:11', NULL, '2025-11-29 17:35:11', '2025-11-29 17:35:11'),
	(5, 14, 2, 'reject', 'approved', '2025-11-29 17:38:18', NULL, '2025-11-29 17:38:18', '2025-11-29 17:38:18'),
	(6, 16, 2, 'pending', 'reject', '2025-11-29 17:43:13', 'di maklaro ang image', '2025-11-29 17:43:13', '2025-11-29 17:43:13'),
	(7, 16, NULL, 'reject', 'pending', '2025-11-29 18:37:00', 'Resubmitted by tutor', '2025-11-29 18:37:00', '2025-11-29 18:37:00'),
	(8, 16, 2, 'pending', 'approved', '2025-11-29 18:39:32', NULL, '2025-11-29 18:39:32', '2025-11-29 18:39:32'),
	(14, 19, 2, 'pending', 'reject', '2025-11-29 18:50:05', 'haha nang yan 114 problems, reject ka sakin', '2025-11-29 18:50:05', '2025-11-29 18:50:05'),
	(15, 19, NULL, 'reject', 'pending', '2025-11-29 18:50:22', 'Resubmitted by tutor', '2025-11-29 18:50:22', '2025-11-29 18:50:22'),
	(16, 19, 2, 'pending', 'approved', '2025-11-29 18:50:36', NULL, '2025-11-29 18:50:36', '2025-11-29 18:50:36'),
	(17, 20, 2, 'pending', 'reject', '2025-11-29 19:38:06', 'reject daghan error', '2025-11-29 19:38:06', '2025-11-29 19:38:06'),
	(18, 20, NULL, 'reject', 'pending', '2025-11-29 19:38:17', 'Resubmitted by tutor', '2025-11-29 19:38:17', '2025-11-29 19:38:17'),
	(19, 20, 2, 'pending', 'approved', '2025-11-29 19:38:30', NULL, '2025-11-29 19:38:30', '2025-11-29 19:38:30'),
	(20, 21, 2, 'pending', 'approved', '2025-11-30 02:32:06', NULL, '2025-11-30 02:32:06', '2025-11-30 02:32:06'),
	(21, 22, 2, 'pending', 'approved', '2025-11-30 05:53:33', NULL, '2025-11-30 05:53:33', '2025-11-30 05:53:33'),
	(22, 23, 2, 'pending', 'approved', '2025-11-30 07:15:13', NULL, '2025-11-30 07:15:13', '2025-11-30 07:15:13'),
	(23, 24, 2, 'pending', 'approved', '2025-12-01 00:03:00', NULL, '2025-12-01 00:03:00', '2025-12-01 00:03:00'),
	(24, 26, 2, 'pending', 'approved', '2025-12-01 00:05:25', NULL, '2025-12-01 00:05:25', '2025-12-01 00:05:25'),
	(25, 25, 2, 'pending', 'approved', '2025-12-01 00:05:27', NULL, '2025-12-01 00:05:27', '2025-12-01 00:05:27'),
	(26, 29, 2, 'pending', 'approved', '2025-12-04 08:18:29', NULL, '2025-12-04 08:18:29', '2025-12-04 08:18:29'),
	(27, 28, 2, 'pending', 'approved', '2025-12-05 03:29:44', NULL, '2025-12-05 03:29:44', '2025-12-05 03:29:44'),
	(28, 27, 2, 'pending', 'reject', '2025-12-05 03:30:13', 'saon ko manang jar', '2025-12-05 03:30:13', '2025-12-05 03:30:13'),
	(29, 43, 5, 'pending', 'approved', '2025-12-07 04:58:52', NULL, '2025-12-07 04:58:52', '2025-12-07 04:58:52'),
	(30, 47, 7, 'pending', 'reject', '2025-12-07 05:57:05', 'kuyawa sad ana oy 15 hours imong work??', '2025-12-07 05:57:05', '2025-12-07 05:57:05');

-- Dumping structure for table ogs.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Test User', 'test@example.com', '2025-12-07 04:28:38', '$2y$12$9V25QlfYSKwUPgcVTEbdZOMATPWUEvtJYL0ohVeMWLr7hYHR3Tb/.', 'mwvtRoZniB', '2025-12-07 04:28:38', '2025-12-07 04:28:38');

-- Dumping structure for table ogs.work_preferences
CREATE TABLE IF NOT EXISTS `work_preferences` (
  `applicant_preference_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint unsigned NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `days_available` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `platform` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `can_teach` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`applicant_preference_id`),
  KEY `work_preferences_applicant_id_foreign` (`applicant_id`),
  CONSTRAINT `work_preferences_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  CONSTRAINT `work_preferences_chk_1` CHECK (json_valid(`days_available`)),
  CONSTRAINT `work_preferences_chk_2` CHECK (json_valid(`platform`)),
  CONSTRAINT `work_preferences_chk_3` CHECK (json_valid(`can_teach`))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ogs.work_preferences: ~3 rows (approximately)
INSERT INTO `work_preferences` (`applicant_preference_id`, `applicant_id`, `start_time`, `end_time`, `days_available`, `platform`, `can_teach`, `created_at`, `updated_at`) VALUES
	(1, 2, '12:00:00', '14:00:00', '["wednesday","saturday"]', '["classin","ms_teams"]', '["kids"]', '2025-11-19 19:00:50', '2025-11-19 19:00:50'),
	(2, 10, '09:00:00', '12:00:00', '["monday","tuesday"]', '["classin","zoom"]', '["kids"]', '2025-11-19 22:58:59', '2025-11-19 22:58:59'),
	(3, 11, '00:00:00', '23:59:59', '"[\\"Monday\\",\\"Tuesday\\",\\"Wednesday\\",\\"Thursday\\",\\"Friday\\"]"', '["classin","voov","ms_teams","others"]', '["kids","teenager","adults"]', '2025-12-04 07:27:54', '2025-12-04 16:48:18');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
