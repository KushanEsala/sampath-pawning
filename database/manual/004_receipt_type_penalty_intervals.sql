-- MANUAL ONLY. Back up the database and run once in the selected database.
-- Every existing receipt type receives a 21-day default for each stage.
-- First letter = expiry + letter_1_days (set 0 for the day of expiry).
-- Second/third letters = previous scheduled letter date + their interval.
-- Reminder = actual third-letter issue date + forfeit_reminder_days.
ALTER TABLE `recei__adds`
  ADD COLUMN `letter_1_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21,
  ADD COLUMN `letter_2_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21,
  ADD COLUMN `letter_3_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21,
  ADD COLUMN `forfeit_reminder_days` SMALLINT UNSIGNED NOT NULL DEFAULT 21;
