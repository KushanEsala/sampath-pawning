-- READ ONLY. Run against each target database before changing legacy Silver dates.
-- Silver letter expiry is To_Date, not Final_date. Older part payments and
-- repawns may have left To_Date behind the current cycle; inspect those rows.
SELECT
    BC, Receipt_Number, Invoice_Number, Receipt_Type, receiptname,
    DATE(Receipt_Date) AS receipt_date, DATE(To_Date) AS configured_to_date,
    DATE(Final_date) AS stored_final_date,
    DATE(COALESCE(RePawning_date, Pawn_Date, Receipt_Date)) AS current_cycle_start,
    Valid_Period AS chosen_months, validPeriod AS type_valid_days,
    period3 AS interest_period_days,
    COALESCE(letter_1_days, 21) AS first_letter_days,
    DATE_ADD(DATE(To_Date), INTERVAL COALESCE(letter_1_days, 21) DAY) AS stored_first_letter_due,
    is_letter_1, DATE(letter_1_date) AS first_letter_printed,
    is_letter_2, DATE(letter_2_date) AS second_letter_printed,
    is_letter_3, DATE(letter_3_date) AS third_letter_printed,
    IsRedeemed, isForfeit,
    CASE
        WHEN COALESCE(IsRedeemed, 0) <> 0 THEN 'CLOSED: redeemed'
        WHEN COALESCE(isForfeit, 0) <> 0 THEN 'CLOSED: forfeited'
        WHEN To_Date IS NULL THEN 'REVIEW: no Silver expiry'
        WHEN DATE(To_Date) < DATE(COALESCE(RePawning_date, Pawn_Date, Receipt_Date)) THEN 'REVIEW: stale expiry after part payment/repawn; application calculates from latest transaction'
        WHEN DATE(To_Date) > CURRENT_DATE() THEN 'NOT EXPIRED'
        WHEN DATE_ADD(DATE(To_Date), INTERVAL COALESCE(letter_1_days, 21) DAY) > CURRENT_DATE() THEN 'WAITING FOR FIRST-LETTER INTERVAL'
        WHEN COALESCE(is_letter_1, 0) = 0 THEN 'FIRST LETTER DUE'
        ELSE 'FIRST LETTER PRINTED / LATER STAGE'
    END AS letter_status
FROM t_pawn_sums
WHERE UPPER(TRIM(COALESCE(receiptname, ''))) = 'SILVER'
   OR UPPER(TRIM(COALESCE(Receipt_Type, ''))) = 'SILVER'
ORDER BY BC, To_Date, Receipt_Number;
