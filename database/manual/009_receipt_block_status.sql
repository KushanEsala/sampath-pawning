-- Manual one-time schema change. Back up both tables before running.
-- Never run this through Laravel migrations.
ALTER TABLE `t_pawn_sums`
  ADD COLUMN `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `blocked_at` DATETIME NULL,
  ADD COLUMN `blocked_by` VARCHAR(255) NULL,
  ADD COLUMN `block_reason` TEXT NULL;

ALTER TABLE `t_opening_pawn_sums`
  ADD COLUMN `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `blocked_at` DATETIME NULL,
  ADD COLUMN `blocked_by` VARCHAR(255) NULL,
  ADD COLUMN `block_reason` TEXT NULL;
