-- Run manually only after taking a full database backup.
CREATE TABLE `forfeit_reminder_promises` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pawn_sum_id` BIGINT NOT NULL,
  `BC` VARCHAR(50) NOT NULL,
  `receipt_number` BIGINT NULL,
  `cycle_no` INT UNSIGNED NOT NULL DEFAULT 1,
  `promise_date` DATE NOT NULL,
  `remark` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  `previous_promise_id` BIGINT UNSIGNED NULL,
  `created_by` VARCHAR(100) NULL,
  `updated_by` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_forfeit_promise_receipt_branch` (`receipt_number`,`BC`),
  KEY `idx_forfeit_promise_current` (`pawn_sum_id`,`cycle_no`,`status`),
  KEY `idx_forfeit_promise_date` (`promise_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

