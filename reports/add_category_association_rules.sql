-- Target: olist_db, MySQL 8 (matching the supplied SQL dump).
-- Adds category-level storage; does not alter existing tables or application code.
-- Existing product-level rules are deliberately not converted to category rules:
-- their support/confidence/lift cannot be reused after aggregating categories.
-- Application changes are required to populate this table automatically.
USE `olist_db`;

CREATE TABLE `category_association_rules` (
  `rule_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dataset_id` BIGINT UNSIGNED NOT NULL,
  `antecedent_category_name` VARCHAR(100) NOT NULL,
  `consequent_category_name` VARCHAR(100) NOT NULL,
  `support` DECIMAL(9,6) NOT NULL COMMENT 'Percentage from 0 to 100',
  `confidence` DECIMAL(9,6) NOT NULL COMMENT 'Percentage from 0 to 100',
  `lift` DECIMAL(18,6) NOT NULL,
  `co_purchase_count` BIGINT UNSIGNED NOT NULL,
  `basket_count` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rule_id`),
  KEY `idx_category_rules_dataset_created` (`dataset_id`, `created_at`),
  CONSTRAINT `fk_category_rules_dataset`
    FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_category_rules_support` CHECK (`support` BETWEEN 0 AND 100),
  CONSTRAINT `chk_category_rules_confidence` CHECK (`confidence` BETWEEN 0 AND 100),
  CONSTRAINT `chk_category_rules_lift` CHECK (`lift` >= 0),
  CONSTRAINT `chk_category_rules_counts`
    CHECK (`basket_count` > 0 AND `co_purchase_count` > 0 AND `co_purchase_count` <= `basket_count`),
  CONSTRAINT `chk_category_rules_names`
    CHECK (CHAR_LENGTH(TRIM(`antecedent_category_name`)) > 0
      AND CHAR_LENGTH(TRIM(`consequent_category_name`)) > 0
      AND `antecedent_category_name` = TRIM(`antecedent_category_name`)
      AND `consequent_category_name` = TRIM(`consequent_category_name`)
      AND `antecedent_category_name` <> `consequent_category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Names are snapshots of the same TRIM(product_category_name) values used by
-- AssociationRules.php. No new mandatory categories table is introduced, so
-- existing product imports continue to work without application changes.
-- Dataset ownership must still be verified by application code when reading
-- or writing rules. A foreign key alone does not authorize access.
-- Separate analysis-run tracking is intentionally outside this small patch.

SHOW CREATE TABLE `category_association_rules`;
