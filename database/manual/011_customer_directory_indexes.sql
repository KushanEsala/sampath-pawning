-- OPTIONAL manual performance script. Never run through Laravel migrations.
-- Confirm the target is the intended production database, back it up, and check
-- information_schema.STATISTICS before running: each index must be absent.
-- Run during a maintenance window; building indexes may lock or slow writes.

CREATE INDEX `idx_customers_bc_id` ON `customers` (`BC`, `id`);
CREATE INDEX `idx_customers_bc_nic` ON `customers` (`BC`, `NIC`(32));
CREATE INDEX `idx_customers_bc_contact` ON `customers` (`BC`, `Contact_1`(16));
CREATE INDEX `idx_customers_code` ON `customers` (`Code`);
