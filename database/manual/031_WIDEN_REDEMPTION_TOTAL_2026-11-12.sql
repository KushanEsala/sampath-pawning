-- Review only. Do not run without the owner's explicit approval to change
-- the production schema. No Laravel migration is involved.
-- Receipt 001/6763 quotes Rs. 1,487,961.00, while DECIMAL(8,2) stops at
-- Rs. 999,999.99. The other redemption payment columns already fit this amount.

ALTER TABLE `t_redeem_sums`
    MODIFY COLUMN `Payable_Total` DECIMAL(15,2) NULL DEFAULT NULL;
