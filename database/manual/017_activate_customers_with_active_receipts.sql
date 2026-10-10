-- Kreethya production customer activation, 2026-10-10.
-- Run manually after taking a backup of the affected customers rows.
-- No Laravel migration. Existing pawn/count limits and every other customer field stay unchanged.
-- An active receipt is not redeemed and not forfeited, in either receipt table.
-- Match NIC using the database collation so old upper/lower-case NIC variants stay together.

UPDATE customers AS customer
SET customer.Status = 1
WHERE (customer.Status IS NULL OR customer.Status <> 1)
  AND (
    EXISTS (
      SELECT 1 FROM t_pawn_sums AS receipt
      WHERE receipt.Customer_NIC = customer.NIC
        AND receipt.IsRedeemed = 0
        AND receipt.isForfeit = 0
    )
    OR EXISTS (
      SELECT 1 FROM t_opening_pawn_sums AS opening
      WHERE opening.Customer_NIC = customer.NIC
        AND opening.IsRedeemed = 0
        AND opening.isForfeit = 0
    )
  );

-- Verification query: must return 0 after the update.
SELECT COUNT(*) AS inactive_customer_rows_with_active_receipts
FROM customers AS customer
WHERE (customer.Status IS NULL OR customer.Status <> 1)
  AND (
    EXISTS (
      SELECT 1 FROM t_pawn_sums AS receipt
      WHERE receipt.Customer_NIC = customer.NIC
        AND receipt.IsRedeemed = 0
        AND receipt.isForfeit = 0
    )
    OR EXISTS (
      SELECT 1 FROM t_opening_pawn_sums AS opening
      WHERE opening.Customer_NIC = customer.NIC
        AND opening.IsRedeemed = 0
        AND opening.isForfeit = 0
    )
  );
