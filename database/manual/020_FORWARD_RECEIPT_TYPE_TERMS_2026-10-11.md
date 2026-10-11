# Forward receipt-type terms — 2026-10-11

Never run Laravel migrations. No schema change is needed.

## Rule

- Existing receipts keep their saved interest rates and interest-period/validity settings for the current pawn cycle. Do not rewrite `rate1/2/3`, `period1/2/3`, `validPeriod`, interest, principal, transaction rows, or already-issued letter events.
- From a receipt-type edit onward, its service charge, postage, service-charge band values, and letter/reminder intervals apply to **all open receipts of that type**, regardless of when those receipts were pawned. Already posted service charges and positive `letter_pay_*`/`arrears_letter_events.postage_charge` amounts remain unchanged.
- First letter remains printable on expiry (`letter_1_days = 0`). Later dates are calculated from the prior **print date** plus the current type interval. A type edit may therefore move an unprinted next-stage due date, but it never edits the recorded prior print date.
- A printed legacy letter with no positive saved amount resolves postage from the type version effective on its saved print date, not from the newly updated receipt snapshot. Same-day legacy print rows without a time or charge cannot distinguish two edits on that day; do not fabricate a historical event or amount.
- Closed (redeemed or forfeited) receipts are not rewritten. A type with open receipts cannot be renamed, because that would break their historical-rate lineage.

## Live type-version evidence before correction

Read from `recei__adds` on Kreethya on 2026-10-11. Type A was briefly Rs. 30 service charge on 2026-10-04 (version 17), then restored to Rs. 60 the same day (version 19). A, B and Silver letter/reminder intervals changed from 21 to 14 days on 2026-10-06 (versions 22, 21 and 24). Postage changed from Rs. 125 to Rs. 150 on 2026-10-06 for A, B, C and Silver (active versions 25, 28, 26 and 27). C intervals remain 21 days. Interest rates were not changed in these active versions.

Before correction, active receipt counts / postage mismatches / second-letter interval mismatches were:

| Type | Open receipts | Postage mismatch | Second-letter mismatch |
| --- | ---: | ---: | ---: |
| A | 6,662 | 6,323 | 6,283 |
| B | 1,133 | 1,070 | 1,064 |
| C | 1,025 | 959 | 0 |
| SILVER | 352 | 334 | 332 |

There were 1,390 open receipts with printed flags and zero saved letter charges. None of their zero-charge printed dates was on/after 2026-10-06, and none lacked a print date. The repair must preserve their historical postage determination, not replace it with Rs. 150.

## One-time data correction

`020_sync_forward_receipt_type_terms.php` is dry-run by default. It compares active type versions with open receipt snapshots. `--apply` first writes a private JSON pre-change audit of every affected receipt to `storage/app/receipt_type_forward_terms_before_*.json`, then updates only the eight non-interest columns in batches. It is idempotent; rerunning dry-run should show zero mismatches. Take a full SQL backup before applying on production.

```sh
php database/manual/020_sync_forward_receipt_type_terms.php
php database/manual/020_sync_forward_receipt_type_terms.php --apply
php database/manual/020_sync_forward_receipt_type_terms.php
```

Future receipt-type edits synchronize these same non-interest columns to open receipts in the edit transaction. The financial calculator additionally uses the type version effective on the calculation/print date for open service/postage, so old rate snapshots and posted history stay independent.

## Production execution

Pending deployment, backup, apply and verification. Record exact commit, backup path/hash, audit path, per-type update counts and spot checks here after execution.
