# Letter postage by actual print time

Never run Laravel migrations. This is a guarded data correction, not a schema change. Run `025_reconcile_postage_at_print_time.php` with no arguments for a dry run; `--apply` updates only supported open, current-cycle issued-letter rows.

## Rule and evidence

For a letter actually printed before a receipt-type postage edit, retain the old posted amount. A letter printed after the edit uses the new amount, even when the receipt was pawned earlier. Interest-rate snapshots remain untouched. The type version's `created_at` timestamp distinguishes an old print earlier on the same day from the later edit; `effective_from` alone cannot do that. For example, type A version 25 (Rs. 150) was created on 2026-10-06 at 17:32:12. Branch 001 receipt 10771 letter 1 was printed at 12:06:10 that day for Rs. 125 and must stay Rs. 125; its letter 2 was printed on 2026-10-09 for Rs. 125 and should be Rs. 150.

The previous `019` repair changed **629** zero-charge events to Rs. 125 using the pawn-cycle type value rather than the print-time value. Its private pre-change JSON file identifies the exact events. The 2026-10-11 read-only audit found 649 total letter events: 631 at Rs. 125 and 18 at zero. The two Rs. 125 events outside the `019` repair were the old-rate 2026-10-06 print and the later 2026-10-09 print of receipt 10771. The current forward-postage code returns Rs. 150 for a future print; it does not need a new migration or interest-rate change.

## Guarded correction

The `025` script resolves the receipt-type version actually created by each event's print timestamp. It only proposes a change when the receipt remains open, the event is in its current letter cycle, its saved `letter_pay_*` agrees with the event, its saved print date agrees, and no payment/repawn occurs on or after that print date. It skips zero-charge events, closed receipts, old cycles, and ambiguous payment allocations. The apply mode writes a private JSON before-state audit, then updates the event and matching receipt letter charge together in one transaction and records a `POSTAGE_RATE_CORRECTION` lifecycle audit event. It does not create historical letter events, change interest, or alter customer cash payments. An event printed before the edit is not changed.

Apply mode first streams a complete, consistent SQL backup to `/home/kreethya/db-backups`, prints its SHA-256, and saves the private per-row JSON audit before opening the update transaction. Retain both backups and record the dry-run/verification counts here. The old `019` script must not be rerun as a substitute for print-time pricing.

## Production execution and verification (2026-10-11)

Deployed through commit `b0648181`. The production dry run planned exactly **629** corrections: A 328, B 74, C 39, SILVER 188, all from Rs. 125 to Rs. 150. Apply mode completed all 629 in one transaction. The subsequent dry run planned **zero** changes; 630 events now match their print-time version. No Laravel migrations were run.

The full private pre-change backup is `/home/kreethya/db-backups/letter_postage_print_time_pre_20261011_225736_f0e1e9.sql.gz`, SHA-256 `8e4abcdbf861f0523d1bbfd3a97b4b099c0fa29a1b8f18eb9764a8ebb22064cd`. `gzip -t` passed. The exact per-row pre-change audit is `storage/app/letter_postage_print_time_before_20261011_225737.json` on Kreethya. Retain both files for rollback/audit; neither belongs in Git.

Post-apply event distribution: 18 at zero, **2 at Rs. 125**, and **629 at Rs. 150** (649 total). Branch 001 receipt 10771 still has letter 1 Rs. 125 because it was printed before the edit on October 6; its October 9 letter 2 and saved `letter_pay_two` are now Rs. 150. Branch 002 receipt 2241's October 11 letter 1 and saved `letter_pay_one` are now Rs. 150. These were checked directly on the live database.

The **18 zero-charge historical events** require individual evidence review (13 from earlier cycles and 5 closed receipts); this script deliberately leaves them untouched. One other Rs. 125 event belongs to a receipt with a later pawn/payment cycle, so changing its already-allocated historical charge without reviewing the settlement would be unsafe. The old pre-edit Rs. 125 print is the second Rs. 125 event. No amounts were inferred for the skipped events.
