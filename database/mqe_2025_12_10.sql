-- -------------------------------------------------------------
-- TablePlus 6.7.4(642)
--
-- https://tableplus.com/
--
-- Database: ariwirechat
-- Generation Time: 2025-12-10 09:30:54.3590
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;




CREATE TABLE `account_files` (
  `id` int unsigned NOT NULL,
  `account_id` int NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `accounts` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_status_id` int DEFAULT NULL,
  `document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_number` int DEFAULT NULL,
  `project_budget` bigint DEFAULT NULL,
  `fee_budget` bigint DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `action_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `outbound` int DEFAULT '0',
  `followup` int DEFAULT NULL,
  `weigth` int DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `color` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `actions` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `retell_call_id` varchar(191) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL,
  `object_id` int DEFAULT NULL,
  `customer_id` bigint DEFAULT NULL,
  `customer_owner_id` int DEFAULT NULL COMMENT 'a quien estaba asociado el usuario',
  `customer_createad_at` timestamp NULL DEFAULT NULL,
  `customer_updated_at` timestamp NULL DEFAULT NULL,
  `type_id` int NOT NULL,
  `creator_user_id` int DEFAULT NULL,
  `owner_user_id` int DEFAULT NULL COMMENT 'a quien se atribuye la venta',
  `sale_date` date DEFAULT NULL,
  `sale_amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_actions_retell_call_id` (`retell_call_id`),
  KEY `customer_id` (`customer_id`),
  KEY `type_id` (`type_id`),
  KEY `idx_actions_event_tag` (`customer_id`,`created_at`),
  KEY `idx_actions_note_tag` (`note`(191)),
  KEY `idx_actions_type` (`type_id`,`created_at`),
  KEY `idx_actions_customer_time` (`customer_id`,`created_at`),
  KEY `idx_actions_type_time` (`type_id`,`created_at`),
  KEY `actions_notified_at_index` (`notified_at`)
) ENGINE=InnoDB AUTO_INCREMENT=653086 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `actions_2025_12_09` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `retell_call_id` varchar(191) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `notified_at` datetime DEFAULT NULL,
  `object_id` int DEFAULT NULL,
  `customer_id` bigint DEFAULT NULL,
  `customer_owner_id` int DEFAULT NULL COMMENT 'a quien estaba asociado el usuario',
  `customer_createad_at` timestamp NULL DEFAULT NULL,
  `customer_updated_at` timestamp NULL DEFAULT NULL,
  `type_id` int NOT NULL,
  `creator_user_id` int DEFAULT NULL,
  `owner_user_id` int DEFAULT NULL COMMENT 'a quien se atribuye la venta',
  `sale_date` date DEFAULT NULL,
  `sale_amount` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_actions_retell_call_id` (`retell_call_id`),
  KEY `customer_id` (`customer_id`),
  KEY `type_id` (`type_id`),
  KEY `idx_actions_event_tag` (`customer_id`,`created_at`),
  KEY `idx_actions_note_tag` (`note`(191)),
  KEY `idx_actions_type` (`type_id`,`created_at`),
  KEY `idx_actions_customer_time` (`customer_id`,`created_at`),
  KEY `idx_actions_type_time` (`type_id`,`created_at`),
  KEY `actions_notified_at_index` (`notified_at`)
) ENGINE=InnoDB AUTO_INCREMENT=652887 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `audience_customer` (
  `id` int NOT NULL,
  `customer_id` bigint NOT NULL,
  `audience_id` int NOT NULL DEFAULT '2',
  `sended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `audiences` (
  `id` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `msg2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `type_id` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `campaign_customer_meta_data` (
  `id` int NOT NULL,
  `campaign_id` int DEFAULT NULL,
  `customer_meta_data_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `campaign_messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int DEFAULT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `campaigns` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `audience_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `campaigns_2025_12_05` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `audience_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `categories` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configs_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `countries` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `iso2` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `iso3` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `iso2` (`iso2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_cleaned_phones` (
  `id` int NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `formatted_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `cleaned_phone` varchar(20) NOT NULL,
  `source_priority` int DEFAULT NULL,
  PRIMARY KEY (`id`,`cleaned_phone`),
  KEY `idx_cleaned_phone` (`cleaned_phone`),
  KEY `idx_priority` (`source_priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `customer_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `action_id` int DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16827 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customer_files_2025_09_08` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `action_id` int DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14976 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customer_histories` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `status_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_customer_histories_customers` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=200986 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_last9_all` (
  `phone_last9` char(9) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`phone_last9`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_meta_datas` (
  `id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_metas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meta_data_type_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `meta_data_id` int DEFAULT NULL,
  `value` varchar(1000) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `audience_id` int DEFAULT NULL,
  `created_at` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `updated_at` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15471 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `redirect_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `active` int DEFAULT '1',
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `rd_source_en` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_stages` (
  `id` int NOT NULL,
  `name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `status_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_status_phases` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `parent_id` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_statuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `stage_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `followup` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `next_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_tag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `tag_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_tag_customer_id_tag_id_unique` (`customer_id`,`tag_id`),
  KEY `customer_tag_customer_id_index` (`customer_id`),
  KEY `customer_tag_tag_id_index` (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customer_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customer_unsubscribes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`contact_phone2`,9)) STORED,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone`,9)) STORED,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone2`,9)) STORED,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone_wp`,9)) STORED,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `business_email` (`business_email`),
  KEY `contact_email` (`contact_email`),
  KEY `phone` (`phone`),
  KEY `phone2` (`phone2`),
  KEY `phone_wp` (`phone_wp`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status_id` (`status_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_country` (`country`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_source_id` (`source_id`),
  KEY `idx_inquiry_product_id` (`inquiry_product_id`),
  KEY `idx_maker` (`maker`),
  KEY `idx_customers_notes_tag` (`notes`(191)),
  KEY `idx_customers_source` (`source_id`,`maker`),
  KEY `idx_contact_phone2_last9` (`contact_phone2_last9`),
  KEY `idx_contact_phone2` (`contact_phone2`),
  KEY `idx_phone_last9` (`phone_last9`),
  KEY `idx_phone2_last9` (`phone2_last9`),
  KEY `idx_phone_wp_last9` (`phone_wp_last9`)
) ENGINE=InnoDB AUTO_INCREMENT=672467 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_10_06` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`contact_phone2`,9)) STORED,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone`,9)) STORED,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone2`,9)) STORED,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone_wp`,9)) STORED,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `business_email` (`business_email`),
  KEY `contact_email` (`contact_email`),
  KEY `phone` (`phone`),
  KEY `phone2` (`phone2`),
  KEY `phone_wp` (`phone_wp`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status_id` (`status_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_country` (`country`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_source_id` (`source_id`),
  KEY `idx_inquiry_product_id` (`inquiry_product_id`),
  KEY `idx_maker` (`maker`),
  KEY `idx_customers_notes_tag` (`notes`(191)),
  KEY `idx_customers_source` (`source_id`,`maker`),
  KEY `idx_contact_phone2_last9` (`contact_phone2_last9`),
  KEY `idx_contact_phone2` (`contact_phone2`),
  KEY `idx_phone_last9` (`phone_last9`),
  KEY `idx_phone2_last9` (`phone2_last9`),
  KEY `idx_phone_wp_last9` (`phone_wp_last9`)
) ENGINE=InnoDB AUTO_INCREMENT=667140 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_10_27` (
  `id` int NOT NULL DEFAULT '0',
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_10_28` (
  `id` int NOT NULL DEFAULT '0',
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_11_10` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`contact_phone2`,9)) STORED,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone`,9)) STORED,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone2`,9)) STORED,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone_wp`,9)) STORED,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `business_email` (`business_email`),
  KEY `contact_email` (`contact_email`),
  KEY `phone` (`phone`),
  KEY `phone2` (`phone2`),
  KEY `phone_wp` (`phone_wp`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status_id` (`status_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_country` (`country`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_source_id` (`source_id`),
  KEY `idx_inquiry_product_id` (`inquiry_product_id`),
  KEY `idx_maker` (`maker`),
  KEY `idx_customers_notes_tag` (`notes`(191)),
  KEY `idx_customers_source` (`source_id`,`maker`),
  KEY `idx_contact_phone2_last9` (`contact_phone2_last9`),
  KEY `idx_contact_phone2` (`contact_phone2`),
  KEY `idx_phone_last9` (`phone_last9`),
  KEY `idx_phone2_last9` (`phone2_last9`),
  KEY `idx_phone_wp_last9` (`phone_wp_last9`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_11_11` (
  `id` int NOT NULL DEFAULT '0',
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_11_25` (
  `id` int NOT NULL DEFAULT '0',
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_11_26` (
  `id` int NOT NULL DEFAULT '0',
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_2025_12_10` (
  `id` int NOT NULL DEFAULT '0',
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `customers_clone` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int DEFAULT NULL,
  `maker` int DEFAULT NULL,
  `count_empanadas` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring` int DEFAULT NULL COMMENT '0 mal calificado, 1 para oportunidad',
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `custom_fields` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `rd_station_response` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `status_id` int DEFAULT NULL,
  `inquiry_product_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `lead_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `contact_phone2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `phone_wp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `area_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_document` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_area_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `business_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `contact_position` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `bought_products` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `technical_visit` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `gender` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `scoring_interest` int DEFAULT NULL,
  `scoring_profile` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rd_public_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `src` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cid` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vas` int DEFAULT NULL,
  `rd_source` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `creator_user_id` int DEFAULT NULL,
  `country2` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `number_venues` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `empanadas_size` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `utm_source` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_source to identify a search engine, newsletter name, or other source.',
  `utm_medium` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Use utm_medium to identify a medium such as email or cost-per-click.',
  `utm_campaign` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for keyword analysis. Use utm_campaign to identify a specific product promotion or strategic campaign.',
  `utm_term` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for paid search. Use utm_term to note the keywords for this ad.',
  `utm_content` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Used for A/B testing and content-targeted ads. Use utm_content to differentiate ads or links that point to the same URL.',
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `linkedin_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `company_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ad_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `adset_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `campaign_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `country_temp` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `facebook_id` bigint unsigned DEFAULT NULL,
  `total_sold` int DEFAULT NULL,
  `contact_phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`contact_phone2`,9)) STORED,
  `phone_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone`,9)) STORED,
  `phone2_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone2`,9)) STORED,
  `phone_wp_last9` char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci GENERATED ALWAYS AS (right(`phone_wp`,9)) STORED,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `business_email` (`business_email`),
  KEY `contact_email` (`contact_email`),
  KEY `phone` (`phone`),
  KEY `phone2` (`phone2`),
  KEY `phone_wp` (`phone_wp`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status_id` (`status_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_country` (`country`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_source_id` (`source_id`),
  KEY `idx_inquiry_product_id` (`inquiry_product_id`),
  KEY `idx_maker` (`maker`),
  KEY `idx_customers_notes_tag` (`notes`(191)),
  KEY `idx_customers_source` (`source_id`,`maker`),
  KEY `idx_contact_phone2_last9` (`contact_phone2_last9`),
  KEY `idx_contact_phone2` (`contact_phone2`),
  KEY `idx_phone_last9` (`phone_last9`),
  KEY `idx_phone2_last9` (`phone2_last9`),
  KEY `idx_phone_wp_last9` (`phone_wp_last9`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `duplicated_email` (
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `email_queue_statuses` (
  `id` int NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `email_queues` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `email_id` int NOT NULL,
  `subject` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `view` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `available_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sended_at` timestamp NULL DEFAULT NULL,
  `email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `email_types` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `logo_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `emails` (
  `id` int NOT NULL,
  `name` int DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `subject` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `view` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `audience_id` int NOT NULL DEFAULT '2',
  `query` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `type_id` int DEFAULT NULL,
  `active` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sended_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `employee_files` (
  `id` int unsigned NOT NULL,
  `employee_id` int NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_statuses` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `final_missing_records` (
  `Nombre` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `Teléfono` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `Teléfono celular` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `Fecha de la primera conversión` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_wp` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Fuente de la primera conversión` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_clean` (
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `phone` text COLLATE utf8mb3_unicode_ci,
  `phone2` text COLLATE utf8mb3_unicode_ci,
  `business` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `País` text COLLATE utf8mb3_unicode_ci,
  `Estado` text COLLATE utf8mb3_unicode_ci,
  `Ciudad` text COLLATE utf8mb3_unicode_ci,
  `Pais2` text COLLATE utf8mb3_unicode_ci,
  `Contact_phone2` text COLLATE utf8mb3_unicode_ci,
  `Production` text COLLATE utf8mb3_unicode_ci,
  `phone_wp` text COLLATE utf8mb3_unicode_ci,
  `phone3_last9` char(9) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_phone2` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_contact_phone2` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `main_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `main_last9` varchar(9) COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_crm_houston_2025_05_08` (
  `Celular` text COLLATE utf8mb3_unicode_ci,
  `Nombre` text COLLATE utf8mb3_unicode_ci,
  `Pais` text COLLATE utf8mb3_unicode_ci,
  `Email` text COLLATE utf8mb3_unicode_ci,
  `Comercial` text COLLATE utf8mb3_unicode_ci,
  `Estado` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_fb_2025_09_01` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci,
  KEY `idx_import_phone` (`phone`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_02` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci,
  KEY `idx_import_phone` (`phone`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_03` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci,
  KEY `idx_import_phone` (`phone`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_04` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_05` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_08` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci,
  KEY `idx_import_phone` (`phone`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_09` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci,
  KEY `idx_import_phone` (`phone`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_10` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_11` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_12` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci,
  KEY `idx_import_phone` (`phone`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_09_17` (
  `id` text COLLATE utf8mb3_unicode_ci,
  `created_time` text COLLATE utf8mb3_unicode_ci,
  `ad_id` text COLLATE utf8mb3_unicode_ci,
  `ad_name` text COLLATE utf8mb3_unicode_ci,
  `adset_id` text COLLATE utf8mb3_unicode_ci,
  `adset_name` text COLLATE utf8mb3_unicode_ci,
  `campaign_id` text COLLATE utf8mb3_unicode_ci,
  `campaign_name` text COLLATE utf8mb3_unicode_ci,
  `form_id` text COLLATE utf8mb3_unicode_ci,
  `form_name` text COLLATE utf8mb3_unicode_ci,
  `is_organic` tinyint(1) DEFAULT NULL,
  `platform` text COLLATE utf8mb3_unicode_ci,
  `full name` text COLLATE utf8mb3_unicode_ci,
  `phone` text COLLATE utf8mb3_unicode_ci,
  `lead_status` text COLLATE utf8mb3_unicode_ci,
  `phone_wp` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_fb_2025_09_19` (
  `id` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_time` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `ad_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `adset_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `campaign_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `form_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_organic` mediumtext COLLATE utf8mb4_unicode_ci,
  `platform` mediumtext COLLATE utf8mb4_unicode_ci,
  `full name` mediumtext COLLATE utf8mb4_unicode_ci,
  `phone` mediumtext COLLATE utf8mb4_unicode_ci,
  `lead_status` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_fb_2025_10_30` (
  `id` text COLLATE utf8mb3_unicode_ci,
  `created_time` text COLLATE utf8mb3_unicode_ci,
  `ad_id` text COLLATE utf8mb3_unicode_ci,
  `ad_name` text COLLATE utf8mb3_unicode_ci,
  `adset_id` text COLLATE utf8mb3_unicode_ci,
  `adset_name` text COLLATE utf8mb3_unicode_ci,
  `campaign_id` text COLLATE utf8mb3_unicode_ci,
  `campaign_name` text COLLATE utf8mb3_unicode_ci,
  `form_id` text COLLATE utf8mb3_unicode_ci,
  `form_name` text COLLATE utf8mb3_unicode_ci,
  `is_organic` text COLLATE utf8mb3_unicode_ci,
  `platform` text COLLATE utf8mb3_unicode_ci,
  `full_name` text COLLATE utf8mb3_unicode_ci,
  `phone_number` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `lead_status` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_fb_2025_11_10` (
  `id` text COLLATE utf8mb3_unicode_ci,
  `created_time` text COLLATE utf8mb3_unicode_ci,
  `ad_id` text COLLATE utf8mb3_unicode_ci,
  `ad_name` text COLLATE utf8mb3_unicode_ci,
  `adset_id` text COLLATE utf8mb3_unicode_ci,
  `adset_name` text COLLATE utf8mb3_unicode_ci,
  `campaign_id` text COLLATE utf8mb3_unicode_ci,
  `campaign_name` text COLLATE utf8mb3_unicode_ci,
  `form_id` text COLLATE utf8mb3_unicode_ci,
  `form_name` text COLLATE utf8mb3_unicode_ci,
  `is_organic` text COLLATE utf8mb3_unicode_ci,
  `platform` text COLLATE utf8mb3_unicode_ci,
  `¿cuantas_empanadas_produces_a_diario?` text COLLATE utf8mb3_unicode_ci,
  `full_name` text COLLATE utf8mb3_unicode_ci,
  `phone_number` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `email` text COLLATE utf8mb3_unicode_ci,
  `country` text COLLATE utf8mb3_unicode_ci,
  `lead_status` text COLLATE utf8mb3_unicode_ci,
  KEY `idx_import_phone` (`phone_number`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_getresponse_invertek2022` (
  `id` text COLLATE utf8mb3_unicode_ci,
  `campaign` text COLLATE utf8mb3_unicode_ci,
  `name` text COLLATE utf8mb3_unicode_ci,
  `email` text COLLATE utf8mb3_unicode_ci,
  `sign_up` text COLLATE utf8mb3_unicode_ci,
  `optin` text COLLATE utf8mb3_unicode_ci,
  `origin` text COLLATE utf8mb3_unicode_ci,
  `score` text COLLATE utf8mb3_unicode_ci,
  `engagement_score` text COLLATE utf8mb3_unicode_ci,
  `geo:ip` text COLLATE utf8mb3_unicode_ci,
  `geo:latitude` text COLLATE utf8mb3_unicode_ci,
  `geo:longitude` text COLLATE utf8mb3_unicode_ci,
  `geo:country` text COLLATE utf8mb3_unicode_ci,
  `geo:region` text COLLATE utf8mb3_unicode_ci,
  `geo:city` text COLLATE utf8mb3_unicode_ci,
  `geo:continent_code` text COLLATE utf8mb3_unicode_ci,
  `geo:country_code` text COLLATE utf8mb3_unicode_ci,
  `geo:region_code` text COLLATE utf8mb3_unicode_ci,
  `geo:postal_code` text COLLATE utf8mb3_unicode_ci,
  `geo:dma_code` text COLLATE utf8mb3_unicode_ci,
  `geo:time_zone` text COLLATE utf8mb3_unicode_ci,
  `tag:argentina` text COLLATE utf8mb3_unicode_ci,
  `tag:bolivia` text COLLATE utf8mb3_unicode_ci,
  `tag:brazil` text COLLATE utf8mb3_unicode_ci,
  `tag:chile` text COLLATE utf8mb3_unicode_ci,
  `tag:colombia` text COLLATE utf8mb3_unicode_ci,
  `tag:comercio` text COLLATE utf8mb3_unicode_ci,
  `tag:costarica` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_comercio` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_consultoria_construccion` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_fabricante_maquinaria` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_ferreteria` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_industria` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_ingenieria` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_instalaciones` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_mantenimiento` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_persona_natural` text COLLATE utf8mb3_unicode_ci,
  `tag:ebl_boletin_tablerista` text COLLATE utf8mb3_unicode_ci,
  `tag:ecuador` text COLLATE utf8mb3_unicode_ci,
  `tag:el_salvador` text COLLATE utf8mb3_unicode_ci,
  `tag:estados_unidos` text COLLATE utf8mb3_unicode_ci,
  `tag:florida` text COLLATE utf8mb3_unicode_ci,
  `tag:guatemala` text COLLATE utf8mb3_unicode_ci,
  `tag:guyana` text COLLATE utf8mb3_unicode_ci,
  `tag:honduras` text COLLATE utf8mb3_unicode_ci,
  `tag:ingles` text COLLATE utf8mb3_unicode_ci,
  `tag:mexico` text COLLATE utf8mb3_unicode_ci,
  `tag:nicaragua` text COLLATE utf8mb3_unicode_ci,
  `tag:otros_paises` text COLLATE utf8mb3_unicode_ci,
  `tag:panama` text COLLATE utf8mb3_unicode_ci,
  `tag:paraguay` text COLLATE utf8mb3_unicode_ci,
  `tag:peru` text COLLATE utf8mb3_unicode_ci,
  `tag:tablerista` text COLLATE utf8mb3_unicode_ci,
  `tag:uruguay` text COLLATE utf8mb3_unicode_ci,
  `tag:venezuela` text COLLATE utf8mb3_unicode_ci,
  `custom:actividad` text COLLATE utf8mb3_unicode_ci,
  `custom:age` text COLLATE utf8mb3_unicode_ci,
  `custom:birthdate` text COLLATE utf8mb3_unicode_ci,
  `custom:birthday` text COLLATE utf8mb3_unicode_ci,
  `custom:cedula` text COLLATE utf8mb3_unicode_ci,
  `custom:city` text COLLATE utf8mb3_unicode_ci,
  `custom:ciudad` text COLLATE utf8mb3_unicode_ci,
  `custom:comment` text COLLATE utf8mb3_unicode_ci,
  `custom:company` text COLLATE utf8mb3_unicode_ci,
  `custom:contacto` text COLLATE utf8mb3_unicode_ci,
  `custom:correo` text COLLATE utf8mb3_unicode_ci,
  `custom:country` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_city` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_ciudad` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_comment` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_contacto` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_direccion` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_manejo_datos` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_nit` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_razon_social` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_street` text COLLATE utf8mb3_unicode_ci,
  `custom:custom_telefono_` text COLLATE utf8mb3_unicode_ci,
  `custom:direccion` text COLLATE utf8mb3_unicode_ci,
  `custom:fax` text COLLATE utf8mb3_unicode_ci,
  `custom:gender` text COLLATE utf8mb3_unicode_ci,
  `custom:home_phone` text COLLATE utf8mb3_unicode_ci,
  `custom:http_referer` text COLLATE utf8mb3_unicode_ci,
  `custom:manejo_datos` text COLLATE utf8mb3_unicode_ci,
  `custom:mobile_phone` text COLLATE utf8mb3_unicode_ci,
  `custom:nit` text COLLATE utf8mb3_unicode_ci,
  `custom:numero_de_factura` text COLLATE utf8mb3_unicode_ci,
  `custom:origin` text COLLATE utf8mb3_unicode_ci,
  `custom:phone` text COLLATE utf8mb3_unicode_ci,
  `custom:postal_code` text COLLATE utf8mb3_unicode_ci,
  `custom:postcode` text COLLATE utf8mb3_unicode_ci,
  `custom:razon_social` text COLLATE utf8mb3_unicode_ci,
  `custom:ref` text COLLATE utf8mb3_unicode_ci,
  `custom:referencia` text COLLATE utf8mb3_unicode_ci,
  `custom:sitio_web` text COLLATE utf8mb3_unicode_ci,
  `custom:state` text COLLATE utf8mb3_unicode_ci,
  `custom:street` text COLLATE utf8mb3_unicode_ci,
  `custom:telefono` text COLLATE utf8mb3_unicode_ci,
  `custom:telefono_` text COLLATE utf8mb3_unicode_ci,
  `custom:telephone` text COLLATE utf8mb3_unicode_ci,
  `custom:ultima_actualizacion` text COLLATE utf8mb3_unicode_ci,
  `custom:url` text COLLATE utf8mb3_unicode_ci,
  `custom:work_phone` text COLLATE utf8mb3_unicode_ci,
  `gdpr:política de datos - uso app` text COLLATE utf8mb3_unicode_ci,
  `gdpr:términos y condiciones` text COLLATE utf8mb3_unicode_ci,
  `last_update` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_n8n_2025_07_22__2025_08_01` (
  `Telefono` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_n8n_2025_07_28__2025_08_01` (
  `Telefono` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_n8n_2025_07_28__2025_08_01_` (
  `Telefono` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_n8n_2025_07_28__2025_08_01__` (
  `Telefono` text COLLATE utf8mb3_unicode_ci,
  `name` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_n8n_2025_07_29__2025_08_01` (
  `Telefono` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `name` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_phone_new` (
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `phone` text COLLATE utf8mb3_unicode_ci,
  `phone2` text COLLATE utf8mb3_unicode_ci,
  `business` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `País` text COLLATE utf8mb3_unicode_ci,
  `Estado` text COLLATE utf8mb3_unicode_ci,
  `Ciudad` text COLLATE utf8mb3_unicode_ci,
  `Pais2` text COLLATE utf8mb3_unicode_ci,
  `Contact_phone2` text COLLATE utf8mb3_unicode_ci,
  `Production` text COLLATE utf8mb3_unicode_ci,
  `phone_wp` text COLLATE utf8mb3_unicode_ci,
  `phone3_last9` char(9) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_phone2` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_contact_phone2` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `main_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `main_last9` varchar(9) COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_quiz_2025_12_10` (
  `phone` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_2025_02_25` (
  `Email` varchar(255) DEFAULT NULL,
  `Nombre` varchar(255) DEFAULT NULL,
  `Teléfono` varchar(255) DEFAULT NULL,
  `Teléfono celular` varchar(255) DEFAULT NULL,
  `Facebook` varchar(255) DEFAULT NULL,
  `Twitter` varchar(255) DEFAULT NULL,
  `LinkedIn` varchar(255) DEFAULT NULL,
  `Sitio web` varchar(255) DEFAULT NULL,
  `Cargo` varchar(255) DEFAULT NULL,
  `Empresa` varchar(255) DEFAULT NULL,
  `País` varchar(255) DEFAULT NULL,
  `Estado` varchar(255) DEFAULT NULL,
  `Ciudad` varchar(255) DEFAULT NULL,
  `Biografía` varchar(255) DEFAULT NULL,
  `Etapa en el embudo` varchar(255) DEFAULT NULL,
  `Dueño del lead` varchar(255) DEFAULT NULL,
  `Fecha de la última oportunidad` varchar(255) DEFAULT NULL,
  `Fecha de la última venta` varchar(255) DEFAULT NULL,
  `Valor de la última venta` varchar(255) DEFAULT NULL,
  `Lead Scoring - Perfil` varchar(255) DEFAULT NULL,
  `Lead Scoring - Interés` int DEFAULT NULL,
  `Status de la comunicación vía email` varchar(255) DEFAULT NULL,
  `Tags` varchar(255) DEFAULT NULL,
  `URL pública` varchar(255) DEFAULT NULL,
  `Fecha de cumpleaños` varchar(255) DEFAULT NULL,
  `Base legal para comunicación` varchar(255) DEFAULT NULL,
  `Total de conversiones` int DEFAULT NULL,
  `Fecha de la primera conversión` varchar(255) DEFAULT NULL,
  `Fuente de la primera conversión` varchar(255) DEFAULT NULL,
  `Fecha de la última conversión` varchar(255) DEFAULT NULL,
  `Fuente de la última conversión` varchar(255) DEFAULT NULL,
  `Eventos (últimos 100)` varchar(255) DEFAULT NULL,
  `Celular del que pide` varchar(255) DEFAULT NULL,
  `Ciudad de envio` varchar(255) DEFAULT NULL,
  `Company` varchar(255) DEFAULT NULL,
  `Country` varchar(255) DEFAULT NULL,
  `Country2` varchar(255) DEFAULT NULL,
  `Daily production of empanadas` varchar(255) DEFAULT NULL,
  `Dirección` varchar(255) DEFAULT NULL,
  `Dirección2` varchar(255) DEFAULT NULL,
  `Dirección de envío` varchar(255) DEFAULT NULL,
  `Déjanos saber lo que piensas` varchar(255) DEFAULT NULL,
  `En que horario puede asistir` varchar(255) DEFAULT NULL,
  `Medio de pago` varchar(255) DEFAULT NULL,
  `Motivo de Perda no RD Station CRM` varchar(255) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Nombre del que pide` varchar(255) DEFAULT NULL,
  `Number of employees in your company` varchar(255) DEFAULT NULL,
  `Número de empleados en tu empresa` varchar(255) DEFAULT NULL,
  `Número de sedes de la empresa` varchar(255) DEFAULT NULL,
  `País2` varchar(255) DEFAULT NULL,
  `Phone` varchar(255) DEFAULT NULL,
  `Position` varchar(255) DEFAULT NULL,
  `Producción diaria de Empanadas` varchar(255) DEFAULT NULL,
  `Puesto` varchar(255) DEFAULT NULL,
  `Referencia` varchar(255) DEFAULT NULL,
  `Referencia del domicilio` varchar(255) DEFAULT NULL,
  `Referencia o pista` varchar(255) DEFAULT NULL,
  `Tamaño de las empanadas que fabrican` varchar(255) DEFAULT NULL,
  `Tipo de empresa` varchar(255) DEFAULT NULL,
  `ver` varchar(255) DEFAULT NULL,
  `¿Cuál es tu país?` varchar(255) DEFAULT NULL,
  `¿Estás interesado en comprar?` varchar(255) DEFAULT NULL,
  `¿Estás interesado en comprar2?` varchar(255) DEFAULT NULL,
  `¿Le interesa comprar?` varchar(255) DEFAULT NULL,
  `¿Qué productos hace hoy?` varchar(255) DEFAULT NULL,
  KEY `import_rd_2025_02_25_Email_idx` (`Email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `import_rd_2025_05_06` (
  `Email` text COLLATE utf8mb3_unicode_ci,
  `Nombre` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_2025_05_21` (
  `Email` text COLLATE utf8mb3_unicode_ci,
  `Nombre` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_2025_07_18` (
  `Email` mediumtext COLLATE utf8mb4_unicode_ci,
  `Nombre` mediumtext COLLATE utf8mb4_unicode_ci,
  `Teléfono` mediumtext COLLATE utf8mb4_unicode_ci,
  `Teléfono celular` mediumtext COLLATE utf8mb4_unicode_ci,
  `Lead Scoring - Perfil` mediumtext COLLATE utf8mb4_unicode_ci,
  `Lead Scoring - Interés` mediumtext COLLATE utf8mb4_unicode_ci,
  `Phone_wp` text COLLATE utf8mb4_unicode_ci,
  KEY `idx_phone_wp` (`Phone_wp`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `import_rd_2025_07_18_` (
  `Email` text COLLATE utf8mb3_unicode_ci,
  `Teléfono` text COLLATE utf8mb3_unicode_ci,
  `Teléfono celular` text COLLATE utf8mb3_unicode_ci,
  `Phone` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_2025_07_25_` (
  `Email` text COLLATE utf8mb3_unicode_ci,
  `Teléfono` text COLLATE utf8mb3_unicode_ci,
  `Teléfono celular` text COLLATE utf8mb3_unicode_ci,
  `Phone` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_2025_08_01` (
  `Email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Nombre` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Teléfono` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Teléfono celular` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_2025_11_24` (
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `phone` text COLLATE utf8mb3_unicode_ci,
  `phone2` text COLLATE utf8mb3_unicode_ci,
  `business` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `País` text COLLATE utf8mb3_unicode_ci,
  `Estado` text COLLATE utf8mb3_unicode_ci,
  `Ciudad` text COLLATE utf8mb3_unicode_ci,
  `Pais2` text COLLATE utf8mb3_unicode_ci,
  `Contact_phone2` text COLLATE utf8mb3_unicode_ci,
  `Production` text COLLATE utf8mb3_unicode_ci,
  `phone_wp` text COLLATE utf8mb3_unicode_ci,
  `phone3_last9` char(9) COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_2025_11_25` (
  `Email` text COLLATE utf8mb3_unicode_ci,
  `Teléfono` text COLLATE utf8mb3_unicode_ci,
  `Teléfono celular` text COLLATE utf8mb3_unicode_ci,
  `Phone` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_rd_spain_2025_08_14` (
  `Email` text COLLATE utf8mb3_unicode_ci,
  `Teléfono` text COLLATE utf8mb3_unicode_ci,
  `Teléfono celular` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `Phone` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_test_2025_12_11` (
  `Id` text COLLATE utf8mb3_unicode_ci,
  `phone` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `name` text COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `import_updates` (
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `phone` text COLLATE utf8mb3_unicode_ci,
  `phone2` text COLLATE utf8mb3_unicode_ci,
  `business` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `País` text COLLATE utf8mb3_unicode_ci,
  `Estado` text COLLATE utf8mb3_unicode_ci,
  `Ciudad` text COLLATE utf8mb3_unicode_ci,
  `Pais2` text COLLATE utf8mb3_unicode_ci,
  `Contact_phone2` text COLLATE utf8mb3_unicode_ci,
  `Production` text COLLATE utf8mb3_unicode_ci,
  `phone_wp` text COLLATE utf8mb3_unicode_ci,
  `phone3_last9` char(9) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_phone2` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `clean_contact_phone2` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `main_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `main_last9` varchar(9) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `customer_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=11510 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `machines_mqe` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` varchar(5000) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `inner_link` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `message_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `APIKEY` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `meta_data_types` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `create_at` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT 'CURRENT_TIMESTAMP',
  `updated_at` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_histories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `request` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `quantity` int DEFAULT NULL,
  `price` int DEFAULT NULL,
  `shippingCharges` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipperCode` int DEFAULT NULL,
  `IVA` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `IVAReturn` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_ip` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `user_agent` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request_url` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `unique_machine` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `updated_user_id` int DEFAULT NULL,
  `referal_user_id` int DEFAULT NULL,
  `authorizationResult` int DEFAULT NULL,
  `authorizationCode` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `errorCode` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `errorMessage` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `added_at` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `delivery_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_address` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_phone` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_to` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_from` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_message` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `payment_form` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `payment_id` int DEFAULT NULL,
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `order_products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `price` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `discount` int DEFAULT NULL,
  `total` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=223 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `order_statuses` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `color` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `status_id` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `order_statuses_old` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `color` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `order_transactions` (
  `id` int NOT NULL,
  `type_id` int DEFAULT NULL,
  `internal_id` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `date` date DEFAULT NULL,
  `url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `debit` double DEFAULT NULL,
  `credit` double DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `invoice_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` int DEFAULT NULL,
  `shippingCharges` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipperCode` int DEFAULT NULL,
  `IVA` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `IVAReturn` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `user_ip` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `user_agent` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request_url` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `request_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `unique_machine` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `user_id` int DEFAULT NULL,
  `updated_user_id` int DEFAULT NULL,
  `referal_user_id` int DEFAULT NULL,
  `authorizationResult` int DEFAULT NULL,
  `authorizationCode` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `errorCode` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `errorMessage` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `added_at` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `delivery_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_address` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_phone` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_city` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_country` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_to` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_postal_code` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_from` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `delivery_message` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `payment_form` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `payment_id` int DEFAULT NULL,
  `session_id` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `billing_name` varchar(250) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_document` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_phone` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_phone2` varchar(50) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_email` varchar(250) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_address` varchar(250) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_city` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_country` varchar(100) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=230 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `orders_customers` (
  `id` int unsigned NOT NULL,
  `order_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `orders_old` (
  `id` int unsigned NOT NULL,
  `status_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `subtotal` float DEFAULT NULL,
  `iva` float DEFAULT NULL,
  `total` float DEFAULT NULL,
  `discount` float DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `parking_id` int DEFAULT NULL,
  `deposit_id` int DEFAULT NULL,
  `good_standing_certificate` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` int unsigned NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `phone_validate` (
  `phone` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `polls` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `product_statuses` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `color` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `product_types` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `image_url` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `alias` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `active` int DEFAULT '1',
  `price` double DEFAULT NULL,
  `shipping` int DEFAULT NULL,
  `total` int DEFAULT NULL,
  `country` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `coin` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `colombia_price` int DEFAULT NULL,
  `america_price` double DEFAULT NULL,
  `america_shipping` int DEFAULT NULL,
  `ecuador_price` int DEFAULT NULL,
  `ecuador_shipping` int DEFAULT NULL,
  `ecuador_total_price` int DEFAULT NULL,
  `europa_price` int DEFAULT NULL,
  `europa_shipping` int DEFAULT NULL,
  `colombia_coin` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `europa_coin` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `america_coin` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `registration` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `description_to_print` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status_id` int DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `projects` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `weight` int DEFAULT NULL,
  `phone` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `color` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT '',
  `sales_man` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `quiz_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int DEFAULT NULL,
  `quiz_meta_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_score` decimal(8,2) DEFAULT NULL,
  `completed_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answers` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_results_slug_unique` (`slug`),
  KEY `quiz_results_customer_id_foreign` (`customer_id`),
  CONSTRAINT `quiz_results_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotes` (
  `id` int NOT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `references` (
  `id` int unsigned NOT NULL,
  `customer_id` int DEFAULT NULL,
  `value` bigint DEFAULT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `document_number` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT '',
  `status_id` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `billing_nationality` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_city` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_address` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_country` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_address` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_country` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `trm` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `request_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request` text COLLATE utf8mb3_unicode_ci,
  `action` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `phone` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `facebook_id` bigint DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=130517 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `requests` (
  `id` int unsigned NOT NULL,
  `customer_id` int DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `retell_inbox` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `call_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json NOT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `error` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `retell_inbox_call_id_unique` (`call_id`),
  KEY `retell_inbox_call_id_index` (`call_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int DEFAULT NULL,
  `menu_id` int DEFAULT NULL,
  `create` tinyint(1) DEFAULT '1',
  `read` tinyint(1) DEFAULT '1',
  `update` tinyint(1) DEFAULT '1',
  `delete` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `role_menus_id_idx` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `role_products` (
  `id` int NOT NULL,
  `role_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `sales` (
  `id` int unsigned NOT NULL,
  `date` date DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `value` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `service_files` (
  `id` int unsigned NOT NULL,
  `service_id` int NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_statuses` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_types` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
  `id` int unsigned NOT NULL,
  `account_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_type_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_status_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_budget` bigint DEFAULT NULL,
  `fee_budget` bigint DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stages` (
  `id` int unsigned NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_name_unique` (`name`),
  UNIQUE KEY `tags_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `temp` (
  `email` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `tmp_ordered` (
  `id` int NOT NULL DEFAULT '0',
  `rn` bigint unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `tmp_to_assign` (
  `id` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `user_message_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `message_source_id` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_message_sources_user_id_foreign` (`user_id`),
  KEY `user_message_sources_message_source_id_foreign` (`message_source_id`),
  CONSTRAINT `user_message_sources_message_source_id_foreign` FOREIGN KEY (`message_source_id`) REFERENCES `message_sources` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_message_sources_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_statuses` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `include_reports` int DEFAULT NULL,
  `last_assigned` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notify_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `assignable` int DEFAULT NULL,
  `image_url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `channels_id` int DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_roles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;





















CREATE TABLE `validates_phones` (
  `phone` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `whatsapp_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_account_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `whatsapp_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `whatsapp_account_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_templates_whatsapp_account_id_foreign` (`whatsapp_account_id`),
  CONSTRAINT `whatsapp_templates_whatsapp_account_id_foreign` FOREIGN KEY (`whatsapp_account_id`) REFERENCES `whatsapp_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wire_actions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actionable_id` bigint unsigned NOT NULL,
  `actionable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_id` bigint unsigned NOT NULL,
  `actor_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Some additional information about the action',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_actions_actionable_id_actionable_type_index` (`actionable_id`,`actionable_type`),
  KEY `wire_actions_actor_id_actor_type_index` (`actor_id`,`actor_type`),
  KEY `wire_actions_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wire_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attachable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachable_id` bigint unsigned NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_attachments_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`),
  KEY `wire_attachments_attachable_id_attachable_type_index` (`attachable_id`,`attachable_type`)
) ENGINE=InnoDB AUTO_INCREMENT=364 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wire_conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Private is 1-1 , group or channel',
  `disappearing_started_at` timestamp NULL DEFAULT NULL,
  `disappearing_duration` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_conversations_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wire_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `avatar_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private',
  `allow_members_to_send_messages` tinyint(1) NOT NULL DEFAULT '1',
  `allow_members_to_add_others` tinyint(1) NOT NULL DEFAULT '1',
  `allow_members_to_edit_group_info` tinyint(1) NOT NULL DEFAULT '0',
  `admins_must_approve_new_members` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'when turned on, admins must approve anyone who wants to join group',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_groups_conversation_id_foreign` (`conversation_id`),
  CONSTRAINT `wire_groups_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `wire_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wire_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned DEFAULT NULL,
  `sendable_id` bigint unsigned NOT NULL,
  `sendable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply_id` bigint unsigned DEFAULT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `kept_at` timestamp NULL DEFAULT NULL COMMENT 'filled when a message is kept from disappearing',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wire_messages_reply_id_foreign` (`reply_id`),
  KEY `wire_messages_conversation_id_index` (`conversation_id`),
  KEY `wire_messages_sendable_id_sendable_type_index` (`sendable_id`,`sendable_type`),
  CONSTRAINT `wire_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `wire_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wire_messages_reply_id_foreign` FOREIGN KEY (`reply_id`) REFERENCES `wire_messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4809 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wire_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `participantable_id` bigint unsigned NOT NULL,
  `participantable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exited_at` timestamp NULL DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `conversation_cleared_at` timestamp NULL DEFAULT NULL,
  `conversation_deleted_at` timestamp NULL DEFAULT NULL,
  `conversation_read_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conv_part_id_type_unique` (`conversation_id`,`participantable_id`,`participantable_type`),
  KEY `wire_participants_role_index` (`role`),
  KEY `wire_participants_exited_at_index` (`exited_at`),
  KEY `wire_participants_conversation_cleared_at_index` (`conversation_cleared_at`),
  KEY `wire_participants_conversation_deleted_at_index` (`conversation_deleted_at`),
  KEY `wire_participants_conversation_read_at_index` (`conversation_read_at`),
  CONSTRAINT `wire_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `wire_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2288 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wp_todos_usa` (
  `name` varchar(39) DEFAULT NULL,
  `phone` varchar(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `wp_usa_2025` (
  `id` int NOT NULL DEFAULT '0',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;















CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `RFM_2025` AS select `c`.`id` AS `customer_id`,`c`.`name` AS `name`,`getPhone3`(`c`.`phone`,`c`.`phone2`,`c`.`contact_phone2`) AS `phone`,`c`.`email` AS `email`,`c`.`maker` AS `maker`,coalesce(count(`a`.`id`),0) AS `total_actions`,(case when (count(`a`.`id`) = 0) then 'F0' when (count(`a`.`id`) = 1) then 'F1' when (count(`a`.`id`) = 2) then 'F2' when (count(`a`.`id`) between 3 and 5) then 'F3' when (count(`a`.`id`) between 6 and 10) then 'F4' when (count(`a`.`id`) between 11 and 20) then 'F5' else 'F6' end) AS `F_score`,max(`a`.`created_at`) AS `last_action_date`,(case when (max(`a`.`created_at`) is null) then NULL else (to_days(now()) - to_days(max(`a`.`created_at`))) end) AS `days_since_last_action`,(case when (max(`a`.`created_at`) is null) then 'R0' when ((to_days(now()) - to_days(max(`a`.`created_at`))) <= 90) then 'R1' when ((to_days(now()) - to_days(max(`a`.`created_at`))) between 91 and 180) then 'R2' when ((to_days(now()) - to_days(max(`a`.`created_at`))) between 181 and 365) then 'R3' else 'R4' end) AS `R_score`,(case when (`c`.`maker` is null) then 'M0' when (`c`.`maker` = 1) then 'M1' when (`c`.`maker` = 0) then 'M2' else 'M0' end) AS `M_score`,(case when (max(`a`.`created_at`) is null) then 1 when ((to_days(now()) - to_days(max(`a`.`created_at`))) > 90) then 1 else 0 end) AS `should_recycle` from (`customers` `c` left join `actions` `a` on((`a`.`customer_id` = `c`.`id`))) where ((year(`c`.`created_at`) = year(curdate())) and (`getPhone3`(`c`.`phone`,`c`.`phone2`,`c`.`contact_phone2`) is not null) and (`c`.`status_id` not in (8,9,62,42,18,53,27))) group by `c`.`id` order by `days_since_last_action` desc,`total_actions`;
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_es_customers` AS select `c`.`id` AS `customer_id` from `customers` `c` where (regexp_like(`c`.`phone`,'^\\+?34') or regexp_like(`c`.`phone2`,'^\\+?34') or regexp_like(`c`.`phone_wp`,'^\\+?34') or (`c`.`area_code` = '34') or (`c`.`country` in ('España','Spain')));
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_event_madrid2025_kpis` AS select count(0) AS `total_tagged`,sum(((`v_event_madrid2025_participation`.`wa_out` > 0) or (`v_event_madrid2025_participation`.`emails_out` > 0) or (`v_event_madrid2025_participation`.`calls_out` > 0) or (`v_event_madrid2025_participation`.`social_in` > 0) or (`v_event_madrid2025_participation`.`ai_interactions` > 0))) AS `reached`,sum(((`v_event_madrid2025_participation`.`wa_in` > 0) or (`v_event_madrid2025_participation`.`emails_in` > 0) or (`v_event_madrid2025_participation`.`email_open` > 0))) AS `engaged`,sum((((`v_event_madrid2025_participation`.`wa_in` > 0) or (`v_event_madrid2025_participation`.`emails_in` > 0) or (`v_event_madrid2025_participation`.`calls_out` > 0)) and ((`v_event_madrid2025_participation`.`score_flag` = 1) or (`v_event_madrid2025_participation`.`score_interest` >= 3)))) AS `qualified`,sum((`v_event_madrid2025_participation`.`rsvp_confirmed` = 1)) AS `rsvps`,sum((`v_event_madrid2025_participation`.`attended` = 1)) AS `attended`,sum((`v_event_madrid2025_participation`.`no_show` = 1)) AS `no_show`,sum(`v_event_madrid2025_participation`.`wa_out`) AS `wa_out_msgs`,sum(`v_event_madrid2025_participation`.`wa_in`) AS `wa_in_msgs`,sum(`v_event_madrid2025_participation`.`emails_out`) AS `emails_out_msgs`,sum(`v_event_madrid2025_participation`.`email_open`) AS `email_opens`,sum(`v_event_madrid2025_participation`.`calls_out`) AS `calls_made`,sum(`v_event_madrid2025_participation`.`ai_interactions`) AS `ai_sessions` from `v_event_madrid2025_participation`;
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_event_madrid2025_participation` AS select `c`.`id` AS `customer_id`,coalesce(convert(`c`.`business` using utf8mb4),`c`.`name`) AS `account_name`,`c`.`country` AS `country`,`c`.`city` AS `city`,`c`.`email` AS `email`,`c`.`phone` AS `phone`,`c`.`phone_wp` AS `phone_wp`,`c`.`user_id` AS `owner_id`,((0 <> (case when (`c`.`notes` like '%#Madrid2025%') then 1 else 0 end)) or exists(select 1 from `actions` `a2` where ((`a2`.`customer_id` = `c`.`id`) and (`a2`.`note` like '%#Madrid2025%')))) AS `tagged_madrid2025`,sum((`a`.`type_id` = 14)) AS `wa_out`,sum((`a`.`type_id` = 8)) AS `wa_in`,sum((`a`.`type_id` in (1,21,20))) AS `calls_out`,sum((`a`.`type_id` = 2)) AS `emails_out`,sum((`a`.`type_id` = 23)) AS `emails_in`,sum((`a`.`type_id` = 4)) AS `email_open`,sum((`a`.`type_id` in (6,7))) AS `social_in`,sum(((`a`.`note` like '%chatbot%') or (`a`.`note` like '%IA%'))) AS `ai_interactions`,max((`a`.`type_id` = 101)) AS `rsvp_confirmed`,max((`a`.`type_id` = 102)) AS `attended`,max((`a`.`type_id` = 103)) AS `no_show`,max((case when (`a`.`type_id` = 101) then `a`.`created_at` end)) AS `rsvp_at`,max((case when (`a`.`type_id` = 102) then `a`.`created_at` end)) AS `attended_at`,`c`.`scoring` AS `score_flag`,`c`.`scoring_interest` AS `score_interest`,`c`.`utm_source` AS `utm_source`,`c`.`utm_medium` AS `utm_medium`,`c`.`utm_campaign` AS `utm_campaign`,max(`a`.`created_at`) AS `last_action_at` from (`customers` `c` left join `actions` `a` on((`a`.`customer_id` = `c`.`id`))) where ((`c`.`notes` like '%#Madrid2025%') or (`a`.`note` like '%#Madrid2025%')) group by `c`.`id`;
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_event_status` AS select `c`.`id` AS `customer_id`,max((case when (`a`.`type_id` = 101) then `a`.`created_at` end)) AS `last_rsvp_at`,max((case when (`a`.`type_id` = 102) then `a`.`created_at` end)) AS `last_attended_at`,max((case when (`a`.`type_id` = 103) then `a`.`created_at` end)) AS `last_noshow_at` from (`customers` `c` left join `actions` `a` on((`a`.`customer_id` = `c`.`id`))) group by `c`.`id`;
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_madrid2025_es` AS select `c`.`id` AS `customer_id`,`c`.`name` AS `name`,`c`.`business` AS `business`,`c`.`country` AS `country`,`c`.`phone` AS `phone`,`c`.`phone2` AS `phone2`,`c`.`phone_wp` AS `phone_wp`,`c`.`area_code` AS `area_code`,`c`.`email` AS `email`,`c`.`notes` AS `notes`,`c`.`created_at` AS `created_at`,(case when regexp_like(`c`.`notes`,'#(España|España2025|Tour_Madrid_Pauta)') then 1 else 0 end) AS `tag_in_customer` from `customers` `c` where ((regexp_like(`c`.`phone`,'^\\+?34') or regexp_like(`c`.`phone2`,'^\\+?34') or regexp_like(`c`.`phone_wp`,'^\\+?34') or (`c`.`area_code` = '34') or (`c`.`country` in ('España','Spain'))) and (regexp_like(`c`.`notes`,'#(España|España2025|Tour_Madrid_Pauta)') or exists(select 1 from `actions` `a` where ((`a`.`customer_id` = `c`.`id`) and regexp_like(`a`.`note`,'#(España|España2025|Tour_Madrid_Pauta)')))));
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_madrid2025_es_consolidated` AS select `v`.`customer_id` AS `customer_id`,`v`.`name` AS `name`,`v`.`business` AS `business`,`v`.`country` AS `country`,coalesce(`v`.`phone_wp`,`v`.`phone`,`v`.`phone2`) AS `phone_main`,`v`.`email` AS `email`,`v`.`tag_in_customer` AS `tag_in_customer`,`es`.`last_rsvp_at` AS `last_rsvp_at`,`es`.`last_attended_at` AS `last_attended_at`,`es`.`last_noshow_at` AS `last_noshow_at`,`es`.`rsvp_cnt` AS `rsvp_cnt`,`es`.`attended_cnt` AS `attended_cnt`,`es`.`noshow_cnt` AS `noshow_cnt`,(select `at`.`name` from (`actions` `a2` join `action_types` `at` on((`at`.`id` = `a2`.`type_id`))) where (`a2`.`customer_id` = `v`.`customer_id`) order by `a2`.`created_at` desc limit 1) AS `last_action_name`,(select `a2`.`created_at` from `actions` `a2` where (`a2`.`customer_id` = `v`.`customer_id`) order by `a2`.`created_at` desc limit 1) AS `last_action_at` from (`v_madrid2025_es` `v` left join `v_madrid2025_event_status` `es` on((`es`.`customer_id` = `v`.`customer_id`)));
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_madrid2025_event_status` AS select `c`.`customer_id` AS `customer_id`,max((case when (`a`.`type_id` = 101) then `a`.`created_at` end)) AS `last_rsvp_at`,max((case when (`a`.`type_id` = 102) then `a`.`created_at` end)) AS `last_attended_at`,max((case when (`a`.`type_id` = 103) then `a`.`created_at` end)) AS `last_noshow_at`,sum((case when (`a`.`type_id` = 101) then 1 else 0 end)) AS `rsvp_cnt`,sum((case when (`a`.`type_id` = 102) then 1 else 0 end)) AS `attended_cnt`,sum((case when (`a`.`type_id` = 103) then 1 else 0 end)) AS `noshow_cnt` from (`v_madrid2025_es` `c` left join `actions` `a` on((`a`.`customer_id` = `c`.`customer_id`))) group by `c`.`customer_id`;
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_madrid2025_unified` AS select `c`.`id` AS `customer_id`,coalesce(convert(`c`.`business` using utf8mb4),`c`.`name`) AS `name`,`c`.`country` AS `country`,`getPhone3`(`c`.`phone`,`c`.`phone2`,`c`.`contact_phone2`) AS `phone`,`c`.`email` AS `email`,1 AS `is_es`,(`c`.`notes` like '%#EspañaPauta%') AS `is_pauta`,max(`a`.`created_at`) AS `last_action_at` from (`customers` `c` left join `actions` `a` on((`a`.`customer_id` = `c`.`id`))) where (((`c`.`phone` like '+34%') and (length(`c`.`phone`) = 12)) or ((`c`.`phone` like '34%') and (length(`c`.`phone`) = 11)) or ((`c`.`phone2` like '+34%') and (length(`c`.`phone2`) = 12)) or ((`c`.`phone2` like '34%') and (length(`c`.`phone2`) = 11)) or ((`c`.`contact_phone2` like '+34%') and (length(`c`.`contact_phone2`) = 12)) or ((`c`.`contact_phone2` like '34%') and (length(`c`.`contact_phone2`) = 11)) or ((length(`getPhone3`(`c`.`phone`,`c`.`phone2`,`c`.`contact_phone2`)) = 11) and (`c`.`country` in ('España','ES','Spain')))) group by `c`.`id`;
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_pauta_customers` AS select distinct `c`.`id` AS `customer_id` from (`customers` `c` left join `actions` `a` on((`a`.`customer_id` = `c`.`id`))) where ((`c`.`notes` like '%#Tour_Madrid_Pauta%') or (`a`.`note` like '%#Tour_Madrid_Pauta%'));
CREATE ALGORITHM=UNDEFINED DEFINER=`forge`@`%` SQL SECURITY DEFINER VIEW `v_phone_usa` AS select `customers`.`name` AS `name`,`customers`.`country` AS `country`,`getPhone3`(`customers`.`phone`,`customers`.`phone2`,`customers`.`contact_phone2`) AS `phone`,`customers`.`email` AS `email` from `customers` where (regexp_like(`customers`.`phone`,'^(\\+1|1)[2-9][0-9]{9}$') or regexp_like(`customers`.`phone2`,'^(\\+1|1)[2-9][0-9]{9}$') or regexp_like(`customers`.`contact_phone2`,'^(\\+1|1)[2-9][0-9]{9}$') or (`customers`.`country` like '%USA%') or (`customers`.`country` like '%United States%') or (`customers`.`country` like '%Estados Unidos%') or (`customers`.`country` like '%EEUU%'));
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION;
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION;
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION;
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION;
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION;
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION;
ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;