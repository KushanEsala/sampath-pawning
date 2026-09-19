-- Run manually only after taking a full database backup.
CREATE TABLE `arrears_letter_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pawn_sum_id` BIGINT NOT NULL,
  `BC` VARCHAR(50) NOT NULL,
  `receipt_number` BIGINT NULL,
  `cycle_no` INT UNSIGNED NOT NULL DEFAULT 1,
  `letter_no` TINYINT UNSIGNED NOT NULL,
  `due_date` DATE NOT NULL,
  `issued_at` DATETIME NOT NULL,
  `postage_charge` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `service_charge` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `issued_by` VARCHAR(100) NULL,
  `reprint_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_reprinted_at` DATETIME NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_arrears_letter_cycle` (`pawn_sum_id`,`cycle_no`,`letter_no`),
  KEY `idx_arrears_letter_receipt_branch` (`receipt_number`,`BC`),
  KEY `idx_arrears_letter_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

