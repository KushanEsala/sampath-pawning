-- Read-only audit for existing Silver receipts. Do not use the result as an
-- automatic update: historical contract dates, extensions and prior part
-- payments must be reviewed receipt by receipt before changing stored dates.
SELECT
    `BC`, `Receipt_Number`, `Invoice_Number`,
    `Receipt_Type`, `Pawn_Date`, `RePawning_date`, `Final_date`,
    COALESCE(NULLIF(`validPeriod`, 0), NULLIF(`period3`, 0), 30) AS `configured_silver_days`,
    DATEDIFF(DATE(`Final_date`), DATE(COALESCE(`RePawning_date`, `Pawn_Date`, `Receipt_Date`))) AS `stored_cycle_days`,
    `is_letter_1`, `is_letter_2`, `is_letter_3`, `forfeit_queued_at`
FROM `t_pawn_sums`
WHERE UPPER(COALESCE(NULLIF(`receiptname`, ''), `Receipt_Type`)) = 'SILVER'
  AND COALESCE(`IsRedeemed`, 0) = 0
  AND COALESCE(`isForfeit`, 0) = 0
  AND `Final_date` IS NOT NULL
  AND DATEDIFF(DATE(`Final_date`), DATE(COALESCE(`RePawning_date`, `Pawn_Date`, `Receipt_Date`)))
      <> COALESCE(NULLIF(`validPeriod`, 0), NULLIF(`period3`, 0), 30)
ORDER BY `BC`, `Receipt_Number`;
