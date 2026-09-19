-- MANUAL ROLLBACK ONLY. Export configured intervals before dropping them.
-- Restore the previous application release before applying rollbacks.
ALTER TABLE `recei__adds`
  DROP COLUMN `letter_1_days`,
  DROP COLUMN `letter_2_days`,
  DROP COLUMN `letter_3_days`,
  DROP COLUMN `forfeit_reminder_days`;
