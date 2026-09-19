-- OPTIONAL MANUAL SQL ONLY. Take a full database backup first.
-- The original dump lacks t_pawn_details.isForfeit. Idempotently add it
-- and reconcile article flags with the branch-specific parent receipt.
-- Never run Laravel migrations for this project.
SET @pawn_status_ddl = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE `t_pawn_details` ADD COLUMN `isForfeit` TINYINT(1) NOT NULL DEFAULT 0',
        'SELECT ''t_pawn_details.isForfeit already exists'' AS status')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 't_pawn_details' AND COLUMN_NAME = 'isForfeit'
);
PREPARE pawn_status_statement FROM @pawn_status_ddl;
EXECUTE pawn_status_statement;
DEALLOCATE PREPARE pawn_status_statement;

UPDATE `t_pawn_details` AS details
JOIN `t_pawn_sums` AS receipt
  ON receipt.BC = details.BC AND receipt.Receipt_Number = details.Receipt_Number
SET details.IsRedeemed = COALESCE(receipt.IsRedeemed, 0),
    details.isForfeit = COALESCE(receipt.isForfeit, 0);

UPDATE `t_opening_pawn_details` AS details
JOIN `t_opening_pawn_sums` AS receipt
  ON receipt.BC = details.BC AND receipt.Receipt_Number = details.Receipt_Number
SET details.IsRedeemed = COALESCE(receipt.IsRedeemed, 0),
    details.isForfeit = COALESCE(receipt.isForfeit, 0);

-- Does not modify parent receipts, payments, inventory or financial history.
