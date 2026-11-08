CREATE DATABASE IF NOT EXISTS `olist_db_diagram` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `olist_db_diagram`;

-- Schema only from olist_db (2).sql. Creates a separate database for the diagram.

-- Includes all original tables, indexes and foreign keys. No row data.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `association_rules` (
  `rule_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `antecedent_category_name` varchar(100) NOT NULL,
  `consequent_category_name` varchar(100) NOT NULL,
  `support` decimal(20,12) DEFAULT NULL,
  `confidence` decimal(20,12) DEFAULT NULL,
  `lift` decimal(30,12) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `dataset_id` bigint UNSIGNED NOT NULL,
  `pair_count` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `fk_rules_user_ref` (`user_id`),
  KEY `idx_rules_dataset_antecedent` (`dataset_id`,`antecedent_category_name`),
  KEY `idx_rules_dataset_consequent` (`dataset_id`,`consequent_category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `bundle_recommendations` (
  `bundle_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `main_product_id` varchar(50) DEFAULT NULL,
  `related_product_id` varchar(50) DEFAULT NULL,
  `confidence` decimal(20,12) DEFAULT NULL,
  `lift` decimal(30,12) DEFAULT NULL,
  `discount_rate` decimal(5,2) DEFAULT NULL,
  `expected_revenue` decimal(12,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `dataset_id` bigint UNSIGNED NOT NULL,
  `main_category_name` varchar(100) DEFAULT NULL,
  `related_category_name` varchar(100) DEFAULT NULL,
  `support` decimal(20,12) DEFAULT NULL,
  `pair_count` bigint UNSIGNED DEFAULT NULL,
  `rank` tinyint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`bundle_id`),
  KEY `fk_bundles_user_ref` (`user_id`),
  KEY `fk_bundles_main_ref` (`main_product_id`),
  KEY `fk_bundles_related_ref` (`related_product_id`),
  KEY `fk_bundles_main_ref_dataset` (`dataset_id`,`main_product_id`),
  KEY `fk_bundles_related_ref_dataset` (`dataset_id`,`related_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` varchar(50) NOT NULL,
  `customer_unique_id` varchar(50) NOT NULL,
  `customer_zip_code_prefix` int DEFAULT NULL,
  `customer_city` varchar(100) DEFAULT NULL,
  `customer_state` char(2) DEFAULT NULL,
  `dataset_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`dataset_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `customer_segments` (
  `dataset_id` bigint UNSIGNED NOT NULL,
  `analysis_date` date NOT NULL,
  `customer_unique_id` varchar(50) NOT NULL,
  `last_purchase` date NOT NULL,
  `recency` int UNSIGNED NOT NULL,
  `frequency` bigint UNSIGNED NOT NULL,
  `monetary` decimal(20,2) NOT NULL,
  `r_score` tinyint UNSIGNED NOT NULL,
  `f_score` tinyint UNSIGNED NOT NULL,
  `m_score` tinyint UNSIGNED NOT NULL,
  `segment` varchar(20) NOT NULL,
  `analyzed_at` timestamp NOT NULL,
  PRIMARY KEY (`dataset_id`,`analysis_date`,`customer_unique_id`),
  KEY `customer_segments_group_index` (`dataset_id`,`analysis_date`,`segment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `datasets` (
  `dataset_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rules_version` bigint UNSIGNED NOT NULL DEFAULT '0',
  `rules_filters` json DEFAULT NULL,
  `recommendation_context` json DEFAULT NULL,
  PRIMARY KEY (`dataset_id`),
  KEY `fk_datasets_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `import_logs` (
  `import_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `dataset_id` bigint UNSIGNED NOT NULL,
  `uploaded_by` int NOT NULL,
  `data_type` enum('products','customers','orders','order_items','order_payments','order_reviews') NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_path` varchar(500) NOT NULL,
  `status` enum('uploaded','validating','importing','completed','failed') NOT NULL DEFAULT 'uploaded',
  `total_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `success_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `failed_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `skipped_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `error_message` text,
  `error_file_path` varchar(500) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`import_id`),
  KEY `idx_import_dataset_created` (`dataset_id`,`created_at`),
  KEY `fk_import_uploader` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` varchar(50) NOT NULL,
  `customer_id` varchar(50) NOT NULL,
  `order_status` varchar(30) DEFAULT NULL,
  `order_approved_at` datetime DEFAULT NULL,
  `order_delivered_carrier_date` datetime DEFAULT NULL,
  `order_delivered_customer_date` datetime DEFAULT NULL,
  `order_estimated_delivery_date` datetime DEFAULT NULL,
  `order_purchase_timestamp` datetime DEFAULT NULL,
  `dataset_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`dataset_id`,`order_id`),
  KEY `fk_orders_customer_ref` (`customer_id`),
  KEY `fk_orders_customer_ref_dataset` (`dataset_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `order_id` varchar(50) NOT NULL,
  `order_item_id` int NOT NULL,
  `product_id` varchar(50) DEFAULT NULL,
  `shipping_limit_date` datetime DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `freight_value` decimal(10,2) DEFAULT NULL,
  `dataset_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`dataset_id`,`order_id`,`order_item_id`),
  KEY `fk_items_product_ref` (`product_id`),
  KEY `fk_items_product_ref_dataset` (`dataset_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `order_payments` (
  `dataset_id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `payment_sequential` int NOT NULL,
  `payment_type` varchar(30) DEFAULT NULL,
  `payment_installments` int DEFAULT NULL,
  `payment_value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`dataset_id`,`order_id`,`payment_sequential`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `product_id` varchar(50) NOT NULL,
  `product_category_name` varchar(100) DEFAULT NULL,
  `product_name_length` int DEFAULT NULL,
  `product_description_length` int DEFAULT NULL,
  `product_photos_qty` int DEFAULT NULL,
  `product_weight_g` decimal(10,2) DEFAULT NULL,
  `product_length_cm` decimal(10,2) DEFAULT NULL,
  `product_height_cm` decimal(10,2) DEFAULT NULL,
  `product_width_cm` decimal(10,2) DEFAULT NULL,
  `dataset_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`dataset_id`,`product_id`),
  KEY `fk_product_category` (`product_category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(150) NOT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `remember_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `association_rules`
  ADD CONSTRAINT `association_rules_dataset_id_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rules_user_ref` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `bundle_recommendations`
  ADD CONSTRAINT `bundle_recommendations_dataset_id_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bundles_main_ref_dataset` FOREIGN KEY (`dataset_id`,`main_product_id`) REFERENCES `products` (`dataset_id`, `product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bundles_related_ref_dataset` FOREIGN KEY (`dataset_id`,`related_product_id`) REFERENCES `products` (`dataset_id`, `product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bundles_user_ref` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `customers`
  ADD CONSTRAINT `customers_dataset_id_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `customer_segments`
  ADD CONSTRAINT `customer_segments_dataset_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `datasets`
  ADD CONSTRAINT `fk_datasets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `import_logs`
  ADD CONSTRAINT `fk_import_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_import_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer_ref_dataset` FOREIGN KEY (`dataset_id`,`customer_id`) REFERENCES `customers` (`dataset_id`, `customer_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_dataset_id_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order_ref_dataset` FOREIGN KEY (`dataset_id`,`order_id`) REFERENCES `orders` (`dataset_id`, `order_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_product_ref_dataset` FOREIGN KEY (`dataset_id`,`product_id`) REFERENCES `products` (`dataset_id`, `product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_dataset_id_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `order_payments`
  ADD CONSTRAINT `order_payments_dataset_id_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `order_payments_dataset_id_order_id_foreign` FOREIGN KEY (`dataset_id`,`order_id`) REFERENCES `orders` (`dataset_id`, `order_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `products`
  ADD CONSTRAINT `products_dataset_id_foreign` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
