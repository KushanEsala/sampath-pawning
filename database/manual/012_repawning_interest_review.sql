-- READ ONLY. Manual review aid, not a repair script and not a migration.
-- Zero posted interest can be valid (for example, a waiver), but may also
-- indicate a legacy calculation error. The present master type row may have
-- been edited after the transaction and is NOT proof of the historical rate.

SELECT repawn.BC,
       repawn.Receipt_Number,
       repawn.Redeem_Number,
       repawn.Redeem_Date,
       repawn.Payable_Pawn_Amount,
       repawn.Paid_Interest,
       repawn.Document_Charges,
       pawn.Receipt_Type AS current_receipt_type,
       pawn.rate1 AS latest_snapshot_rate1,
       pawn.rate2 AS latest_snapshot_rate2,
       pawn.rate3 AS latest_snapshot_rate3
FROM t_repawning_sums AS repawn
LEFT JOIN t_pawn_sums AS pawn
  ON pawn.BC COLLATE utf8mb4_unicode_ci
     = repawn.BC COLLATE utf8mb4_unicode_ci
 AND pawn.Receipt_Number = repawn.Receipt_Number
WHERE COALESCE(repawn.Paid_Interest, 0) = 0
ORDER BY repawn.BC, repawn.Receipt_Number, repawn.Redeem_Date;

-- Cross-check the two posted copies of each repawn's paid interest.
-- Missing/duplicate transaction keys require manual investigation; do not
-- generate correction UPDATEs from this report.
SELECT repawn.BC, repawn.Receipt_Number, repawn.Redeem_Number,
       repawn.Redeem_Date, repawn.Paid_Interest AS summary_interest,
       trans.id AS transaction_id,
       trans.Paided_Interest AS transaction_interest
FROM t_repawning_sums AS repawn
LEFT JOIN t_pawn_trans AS trans
  ON trans.BC COLLATE utf8mb4_unicode_ci
     = repawn.BC COLLATE utf8mb4_unicode_ci
 AND trans.code = repawn.Receipt_Number
 AND trans.trans_no = repawn.Redeem_Number
 AND DATE(trans.dDate) = DATE(repawn.Redeem_Date)
 AND UPPER(trans.trans_type) = 'REPAWNING'
WHERE trans.id IS NULL
   OR ABS(COALESCE(repawn.Paid_Interest, 0)
          - COALESCE(trans.Paided_Interest, 0)) > 0.01
ORDER BY repawn.BC, repawn.Receipt_Number, repawn.Redeem_Date;

-- An archived row with updated_at later than a transaction cannot establish
-- the rate that row held at transaction time.
SELECT receiptname, id, rate1, rate2, rate3,
       period1, period2, period3, service_charge,
       effective_from, effective_to, created_at, updated_at
FROM recei__adds
ORDER BY receiptname, effective_from, id;
