-- MANUAL ONLY. Run after 004; do not use Laravel migrations.
-- Existing pawns receive 21 days. Future pawns snapshot the selected type.
-- No receipt is queued for final forfeiture by this script.
ALTER TABLE `t_pawn_sums`
  ADD COLUMN `letter_1_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21,
  ADD COLUMN `letter_2_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21,
  ADD COLUMN `letter_3_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21,
  ADD COLUMN `forfeit_reminder_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21,
  ADD COLUMN `forfeit_queued_at` DATETIME NULL,
  ADD INDEX `idx_pawn_forfeit_queue` (`BC`, `isForfeit`, `forfeit_queued_at`);
