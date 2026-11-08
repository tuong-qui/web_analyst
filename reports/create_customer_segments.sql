-- Run in WAMP phpMyAdmin, with the olist_db database selected.
-- Re-running this script does not delete existing snapshots.
USE `olist_db`;

CREATE TABLE IF NOT EXISTS `customer_segments` (
    `dataset_id` BIGINT UNSIGNED NOT NULL,
    `analysis_date` DATE NOT NULL,
    `customer_unique_id` VARCHAR(50) NOT NULL,
    `last_purchase` DATE NOT NULL,
    `recency` INT UNSIGNED NOT NULL,
    `frequency` BIGINT UNSIGNED NOT NULL,
    `monetary` DECIMAL(20,2) NOT NULL,
    `r_score` TINYINT UNSIGNED NOT NULL,
    `f_score` TINYINT UNSIGNED NOT NULL,
    `m_score` TINYINT UNSIGNED NOT NULL,
    `segment` VARCHAR(20) NOT NULL,
    `analyzed_at` TIMESTAMP NOT NULL,
    PRIMARY KEY (`dataset_id`, `analysis_date`, `customer_unique_id`),
    KEY `customer_segments_group_index` (`dataset_id`, `analysis_date`, `segment`),
    CONSTRAINT `customer_segments_dataset_foreign`
        FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`dataset_id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- No FK to customers.customer_unique_id: a person may have multiple customer_id
-- records. The composite key stores one result per person, dataset and date.
