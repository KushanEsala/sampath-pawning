-- MANUAL ONLY. Back up the database and run once in the selected database.
-- DO NOT RUN LARAVEL MIGRATIONS.
-- This script adds date-effective versioning columns to recei__adds.
-- Existing receipt types are initialized with effective_from = '2020-01-01', effective_to = NULL, and is_active = 1.

ALTER TABLE `recei__adds`
  ADD COLUMN `effective_from` DATE NOT NULL DEFAULT '2020-01-01' AFTER `receiptname`,
  ADD COLUMN `effective_to` DATE NULL DEFAULT NULL AFTER `effective_from`,
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `effective_to`;

-- Index for high-performance date range and active status lookups
CREATE INDEX `idx_recei_adds_lookup` ON `recei__adds` (`receiptname`, `effective_from`, `effective_to`, `is_active`);
