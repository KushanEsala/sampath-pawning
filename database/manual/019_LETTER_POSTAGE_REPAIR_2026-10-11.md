# Letter postage repair — 2026-10-11

Do not run Laravel migrations. This repair does not change the schema.

## Cause

Imported pawn receipts may have `t_pawn_sums.Postage_charge = 0` although their receipt-type version has a positive postage rate. Printing a letter used that zero snapshot, so the genuine `arrears_letter_events.postage_charge` and the corresponding `letter_pay_one/two/three` field were also written as zero. For example, branch 002 receipt 2241 has a genuine first-letter event with zero postage; its historical type C version at the May 2026 pawn-cycle date specifies Rs. 125. Branch 001 also has a receipt numbered 2241, so all corrections must use pawn-summary ID and branch, not receipt number alone.

## Code rule

Keep a positive receipt postage snapshot unchanged. If the saved snapshot is zero, resolve the type version effective at the receipt's current pawn-cycle date. Do not use today's active rate for an old receipt. The corrected calculator is used by letter issuance, the late-letter report, and payment totals.

## Existing zero events

`019_repair_zero_letter_postage.php` is dry-run by default. It repairs only genuine issued-letter events whose postage is zero, receipt is still active, event is in the current arrears cycle, and the saved letter charge, saved receipt charge, or historical type version establishes a positive amount. It also sets that letter's current `letter_pay_*` field if still zero. It does not create events, change receipt-type versions, modify service charges, or touch closed/ambiguous cycles.

Run after deploying the code and taking a full database backup:

```sh
php database/manual/019_repair_zero_letter_postage.php
php database/manual/019_repair_zero_letter_postage.php --apply
```

The apply run writes a timestamped JSON backup of every intended before/after field to `storage/app/letter_postage_backup_*.json` before starting a database transaction. Retain this private file. Review the dry-run's skipped event IDs separately; do not assign guessed fees to them.

## Production execution record

Pending live dry-run, apply, and verification. Add counts, backup path, and any skipped cases here after execution.
