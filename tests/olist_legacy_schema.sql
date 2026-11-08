SET FOREIGN_KEY_CHECKS=0;
CREATE TABLE `association_rules` (
  `rule_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `antecedent_product_id` varchar(50) NOT NULL,
  `consequent_product_id` varchar(50) NOT NULL,
  `support` decimal(8,6) DEFAULT NULL,
  `confidence` decimal(8,6) DEFAULT NULL,
  `lift` decimal(8,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rule_id`),
  KEY `fk_rules_user_ref` (`user_id`),
  KEY `fk_rules_antecedent_ref` (`antecedent_product_id`),
  KEY `fk_rules_consequent_ref` (`consequent_product_id`),
  CONSTRAINT `fk_rules_antecedent_ref` FOREIGN KEY (`antecedent_product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rules_consequent_ref` FOREIGN KEY (`consequent_product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rules_user_ref` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `bundle_recommendations` (
  `bundle_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `main_product_id` varchar(50) NOT NULL,
  `related_product_id` varchar(50) NOT NULL,
  `confidence` decimal(8,6) DEFAULT NULL,
  `lift` decimal(8,6) DEFAULT NULL,
  `discount_rate` decimal(5,2) DEFAULT NULL,
  `expected_revenue` decimal(12,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bundle_id`),
  KEY `fk_bundles_user_ref` (`user_id`),
  KEY `fk_bundles_main_ref` (`main_product_id`),
  KEY `fk_bundles_related_ref` (`related_product_id`),
  CONSTRAINT `fk_bundles_main_ref` FOREIGN KEY (`main_product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bundles_related_ref` FOREIGN KEY (`related_product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bundles_user_ref` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `customers` (
  `customer_id` varchar(50) NOT NULL,
  `customer_unique_id` varchar(50) NOT NULL,
  `customer_zip_code_prefix` int DEFAULT NULL,
  `customer_city` varchar(100) DEFAULT NULL,
  `customer_state` char(2) DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `datasets` (
  `dataset_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dataset_id`),
  KEY `fk_datasets_user` (`user_id`),
  CONSTRAINT `fk_datasets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `import_logs` (
  `import_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dataset_id` bigint unsigned NOT NULL,
  `uploaded_by` int NOT NULL,
  `data_type` enum('products','customers','orders','order_items','order_payments','order_reviews') NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_path` varchar(500) NOT NULL,
  `status` enum('uploaded','validating','importing','completed','failed') NOT NULL DEFAULT 'uploaded',
  `total_rows` int unsigned NOT NULL DEFAULT '0',
  `success_rows` int unsigned NOT NULL DEFAULT '0',
  `failed_rows` int unsigned NOT NULL DEFAULT '0',
  `skipped_rows` int unsigned NOT NULL DEFAULT '0',
  `error_message` text,
  `error_file_path` varchar(500) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`import_id`),
  KEY `idx_import_dataset_created` (`dataset_id`,`created_at`),
  KEY `fk_import_uploader` (`uploaded_by`),
  CONSTRAINT `fk_import_dataset` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_import_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `inventory_alerts` (
  `alert_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `related_product_id` varchar(50) DEFAULT NULL,
  `current_stock` int DEFAULT NULL,
  `recommended_stock` int DEFAULT NULL,
  `severity` enum('Low','Medium','High') DEFAULT 'Medium',
  `confidence` decimal(8,6) DEFAULT NULL,
  `recommendation` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`alert_id`),
  KEY `fk_alerts_user_ref` (`user_id`),
  KEY `fk_alerts_product_ref` (`product_id`),
  KEY `fk_alerts_related_ref` (`related_product_id`),
  CONSTRAINT `fk_alerts_product_ref` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_alerts_related_ref` FOREIGN KEY (`related_product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_alerts_user_ref` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `order_items` (
  `order_id` varchar(50) NOT NULL,
  `order_item_id` int NOT NULL,
  `product_id` varchar(50) DEFAULT NULL,
  `shipping_limit_date` datetime DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `freight_value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`order_id`,`order_item_id`),
  KEY `fk_items_product_ref` (`product_id`),
  CONSTRAINT `fk_items_order_ref` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_items_product_ref` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `order_payments` (
  `order_id` varchar(50) NOT NULL,
  `payment_sequential` int NOT NULL,
  `payment_type` varchar(30) DEFAULT NULL,
  `payment_installments` int DEFAULT NULL,
  `payment_value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`order_id`,`payment_sequential`),
  CONSTRAINT `fk_payments_order_ref` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `order_reviews` (
  `review_pk` bigint NOT NULL AUTO_INCREMENT,
  `review_id` varchar(50) DEFAULT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `review_score` int DEFAULT NULL,
  `review_comment_title` text,
  `review_comment_message` text,
  `review_creation_date` datetime DEFAULT NULL,
  `review_answer_timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`review_pk`),
  KEY `fk_reviews_order_ref` (`order_id`),
  CONSTRAINT `fk_reviews_order_ref` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `orders` (
  `order_id` varchar(50) NOT NULL,
  `customer_id` varchar(50) NOT NULL,
  `order_status` varchar(30) DEFAULT NULL,
  `order_approved_at` datetime DEFAULT NULL,
  `order_delivered_carrier_date` datetime DEFAULT NULL,
  `order_delivered_customer_date` datetime DEFAULT NULL,
  `order_estimated_delivery_date` datetime DEFAULT NULL,
  `order_purchase_timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `fk_orders_customer_ref` (`customer_id`),
  CONSTRAINT `fk_orders_customer_ref` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `products` (
  `product_id` varchar(50) NOT NULL,
  `product_category_name` varchar(100) DEFAULT NULL,
  `product_name_length` int DEFAULT NULL,
  `product_description_length` int DEFAULT NULL,
  `product_photos_qty` int DEFAULT NULL,
  `product_weight_g` decimal(10,2) DEFAULT NULL,
  `product_length_cm` decimal(10,2) DEFAULT NULL,
  `product_height_cm` decimal(10,2) DEFAULT NULL,
  `product_width_cm` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `fk_product_category` (`product_category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(150) NOT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `remember_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SET FOREIGN_KEY_CHECKS=1;
