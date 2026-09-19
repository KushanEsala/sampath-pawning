-- Run manually only after taking a full database backup.
CREATE TABLE `receipt_lifecycle_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pawn_sum_id` BIGINT NOT NULL,
  `BC` VARCHAR(50) NOT NULL,
  `receipt_number` BIGINT NULL,
  `event_type` VARCHAR(50) NOT NULL,
  `event_date` DATETIME NOT NULL,
  `amount` DECIMAL(15,2) NULL,
  `description` TEXT NULL,
  `event_data` LONGTEXT NULL,
  `created_by` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_receipt_lifecycle_receipt_branch` (`receipt_number`,`BC`),
  KEY `idx_receipt_lifecycle_pawn` (`pawn_sum_id`,`event_date`),
  KEY `idx_receipt_lifecycle_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

