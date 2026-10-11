# Receipt 001/256: historical part-payment letter-cycle reconciliation

No Laravel migration or schema change. Do not bulk-apply this receipt-specific repair to other receipts. Use the guarded script `024_reconcile_receipt_256_letter_cycle.php` with no arguments for a dry run; `--apply` commits the single receipt repair. The script checks the exact receipt, payment and transaction values and is idempotent.

## Retained evidence

- The actual 1st, 2nd, 3rd print dates on the receipt were 2026-04-20, 2026-05-05 and 2026-06-02. There were no `arrears_letter_events` rows, so no per-letter postage amount or historical operator may be invented.
- Part-payment transaction `t_pawn_trans.id=28027`, branch 001, receipt 256, was posted on 2026-07-07. Payment summary number 2363 records Rs. 9,265.00 received, Rs. 8,663.10 paid interest, Rs. 601.90 paid capital and **Rs. 600.00 total postage due**. The posted capital Rs. 96,548.10 equals Rs. 97,150.00 opening capital less Rs. 601.90 capital payment; the postage was neither paid nor capitalized.
- That payment started the 2026-07-08 cycle with `Final_date=2026-08-07`, but the pre-upgrade letter flags and print dates remained set. There are no later loan transactions or typed letter events for this receipt.

## Repair and verification

The repair adds only the **recorded aggregate Rs. 600.00 unpaid postage** to capital: Rs. 96,548.10 → Rs. 97,148.10. It updates the payment's post-payment capital snapshot, preserves actual printed dates as snapshots with unknown individual charges, records an auditable allocation/cycle reset, and clears the old current-cycle flags. It does not backdate an event, change the received cash, invent individual postage rates, or change the stored interest rates. With its 2026-08-07 expiry already past, receipt 256 should then appear in first letters. Its next actual letter can be printed in the new cycle, and later stages follow the configured intervals after each new print.

Before applying on another database, verify this exact evidence and take a full database backup. Run `php database/manual/024_reconcile_receipt_256_letter_cycle.php` and inspect its dry-run output, then `php database/manual/024_reconcile_receipt_256_letter_cycle.php --apply`. Repeat the dry run to verify it reports already reconciled. Validate receipt 256's letter tab, history and running balance afterwards.

The wider audit found 1,397 active receipts with at least one current letter flag dated before the current pawn cycle; 1,174 have a verified payment or repawn after their latest recorded print date. Those are candidates for separate per-receipt financial reconciliation, **not** authorization for a blind flag reset: older payment allocations and individual historical letter rates can be missing or inconsistent.

## Kreethya execution, 2026-10-11

Code commit `6e82a664` was pushed to `origin/master` and fast-forwarded on Kreethya. The guard script printed the expected dry-run figures and was then run once with `--apply` against the live database. No Laravel migration or schema change was run. Live read-only verification showed capital Rs. 97,148.10, all three current-cycle flags false, expiry/first-letter due 2026-08-07, letter 1 eligible, cycle number 2, all three real historical print dates still in timeline, and one Rs. 600.00 aggregate postage debit in history. The payment's cash, paid interest, and paid-capital values were not changed. The ledger's current balance differs from the redemption quote by the Rs. 30.00 service charge, which is assessed on redemption rather than on part payment.

Receipt Search and payment-history ledger also include the three original printed dates as non-financial rows (DR/CR zero); the aggregate Rs. 600.00 debit appears only once at the actual payment date. This displays known dates without inventing the unknown per-letter amounts.
