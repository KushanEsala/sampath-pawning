-- MANUAL ROLLBACK ONLY. Export interval/queue values before dropping them.
-- Restore the previous application release before applying rollbacks.
ALTER TABLE `t_pawn_sums`
  DROP INDEX `idx_pawn_forfeit_queue`,
  DROP COLUMN `letter_1_days`,
  DROP COLUMN `letter_2_days`,
  DROP COLUMN `letter_3_days`,
  DROP COLUMN `forfeit_reminder_days`,
  DROP COLUMN `forfeit_queued_at`;
