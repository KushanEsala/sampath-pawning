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

Deployed code commit `63d91e7e` to `origin/master` and fast-forwarded Kreethya at `/home/kreethya/public_html/sampath`. View cache compiled successfully. Local focused tests: 7 passed.

Before the data update, saved a full production SQL backup at `/home/kreethya/db-backups/letter_postage_pre_repair_20261011.sql.gz` (SHA-256 `8589d2627cd254103d2f5cfead30eb0beed86aa42f94100a2a1612e4908a67dc`). The repair additionally saved its exact affected-row backup at `/home/kreethya/public_html/sampath/storage/app/letter_postage_backup_20261011_184825.json`.

The live dry-run proposed **629** genuine issued-letter events and skipped **18**. The apply run updated those 629 events and their zero-valued current `letter_pay_*` fields in one transaction. Verification found 18 zero-charge events remaining out of 649 total; these are the skipped events, not newly created records. Branch 002 receipt 2241 (`t_pawn_sums.id=4400`, event 622) changed from zero to **Rs. 125** in both the issued-letter event and `letter_pay_one`. The live financial calculator now returns Rs. 125 for that letter. Its saved `Postage_charge` snapshot remains zero; the code resolves its May 2026 type-C version at Rs. 125 for future letters.

Of the 18 skipped events, **13** have a later pawn/payment cycle and **5** belong to closed receipts. Their historical charge/payment state must be reviewed individually; the repair deliberately did not invent a rate or modify them. The separate branch 001 receipt numbered 2241 was not part of the event repair; it has legacy letter flags but no matching `arrears_letter_events` row. Its transaction-derived postage remains subject to a separate historical review.
