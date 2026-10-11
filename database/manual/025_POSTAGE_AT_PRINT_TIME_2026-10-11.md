# Letter postage by actual print time

Never run Laravel migrations. This is a guarded data correction, not a schema change. Run `025_reconcile_postage_at_print_time.php` with no arguments for a dry run; `--apply` updates only supported open, current-cycle issued-letter rows.

## Rule and evidence

For a letter actually printed before a receipt-type postage edit, retain the old posted amount. A letter printed after the edit uses the new amount, even when the receipt was pawned earlier. Interest-rate snapshots remain untouched. The type version's `created_at` timestamp distinguishes an old print earlier on the same day from the later edit; `effective_from` alone cannot do that. For example, type A version 25 (Rs. 150) was created on 2026-10-06 at 17:32:12. Branch 001 receipt 10771 letter 1 was printed at 12:06:10 that day for Rs. 125 and must stay Rs. 125; its letter 2 was printed on 2026-10-09 for Rs. 125 and should be Rs. 150.

The previous `019` repair changed **629** zero-charge events to Rs. 125 using the pawn-cycle type value rather than the print-time value. Its private pre-change JSON file identifies the exact events. The 2026-10-11 read-only audit found 649 total letter events: 631 at Rs. 125 and 18 at zero. The two Rs. 125 events outside the `019` repair were the old-rate 2026-10-06 print and the later 2026-10-09 print of receipt 10771. The current forward-postage code returns Rs. 150 for a future print; it does not need a new migration or interest-rate change.

## Guarded correction

The `025` script resolves the receipt-type version actually created by each event's print timestamp. It only proposes a change when the receipt remains open, the event is in its current letter cycle, its saved `letter_pay_*` agrees with the event, its saved print date agrees, and no payment/repawn occurs on or after that print date. It skips zero-charge events, closed receipts, old cycles, and ambiguous payment allocations. The apply mode writes a private JSON before-state audit, then updates the event and matching receipt letter charge together in one transaction and records a `POSTAGE_RATE_CORRECTION` lifecycle audit event. It does not create historical letter events, change interest, or alter customer cash payments. An event printed before the edit is not changed.

Apply mode first streams a complete, consistent SQL backup to `/home/kreethya/db-backups`, prints its SHA-256, and saves the private per-row JSON audit before opening the update transaction. Retain both backups and record the dry-run/verification counts here. The old `019` script must not be rerun as a substitute for print-time pricing.
