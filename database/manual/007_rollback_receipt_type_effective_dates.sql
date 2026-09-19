-- MANUAL ROLLBACK SCRIPT ONLY.
-- DO NOT RUN LARAVEL MIGRATIONS.
-- Removes the date-effective columns and index from recei__adds.

DROP INDEX `idx_recei_adds_lookup` ON `recei__adds`;

ALTER TABLE `recei__adds`
  DROP COLUMN `effective_from`,
  DROP COLUMN `effective_to`,
  DROP COLUMN `is_active`;
