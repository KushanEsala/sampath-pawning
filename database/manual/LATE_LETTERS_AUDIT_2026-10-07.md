# Late Letters visibility audit — 2026-10-07

Read-only review of `kreethya_sampath_prod_20261004`. No Laravel migration, SQL update, or historical letter event was run or created during the audit. Counts are a snapshot and will change with payments, printing, and time.

## Findings

1. **First-letter waiting period.** The active receipt-type versions currently set `letter_1_days` to **14** for A/SILVER and **21** for B/C. Existing pawn receipts hold their own saved interval: 129 have 14 days and 18,398 have 21 days. Previously the list excluded expired receipts until that interval elapsed. The corrected list shows them in the first-letter tab immediately on expiry, marked **Scheduled**, but keeps Print unavailable until each receipt's saved first-letter due date. Changing the configured print wait to zero is a separate business-rule decision; the user was asked before any data update.
2. **Missing-looking Silver rows.** Silver letter expiry is calculated from `To_Date` or its saved term/current transaction cycle, whereas the list was ordered by `Final_date`. Silver receipts with an early effective expiry but a much later `Final_date` were buried on later pages. The list now orders by the same effective expiry it displays.
3. **Unstable pages.** The list previously ordered only by `Final_date`. Many receipts share that date (33 active unprinted first-letter candidates in branch 001 had `Final_date=2026-10-01`), so pages had no deterministic tie order. Ordering now adds `t_pawn_sums.id` as a unique tie-breaker. After printing, the browser returns to page 1 because a printed receipt can leave the current stage and shift offset-paginated rows.
4. **Search could appear empty.** Receipt-number search retained the selected tab, receipt type, and “Hide Printed” setting. An existing receipt in another letter stage therefore returned an empty list. Exact search now locates the receipt's current stage across types, shows printed rows, and explains closed, not-yet-due, or reminder/forfeit-stage results.
5. **Data status audit.** Active receipt flags were not broadly missing: no active row had a letter print date without its matching flag, or a flag without its print date. One active receipt, branch 001 / number 93, has `is_letter_2=1` while `is_letter_1` is NULL; its current `Final_date` is 2027-05-05, so it is not among today's expired first-letter omissions. It was not modified.

## Active, unprinted first letters whose effective expiry has passed

| Branch | Type group | Expired and unprinted | Previously excluded by saved first-letter wait | First letter already due |
|---|---|---:|---:|---:|
| 001 | A/B/C | 197 | 158 | 39 |
| 001 | SILVER | 65 | 20 | 45 |
| 002 | A/B/C | 229 | 164 | 65 |
| 002 | SILVER | 144 | 36 | 108 |
| **Total** | | **635** | **378** | **257** |

The Silver numbers use the same dynamic expiry rule as the application, including the historical receipt-type fallback for four receipts with no saved valid-day period. These 378 records are not lost; the corrected first-letter list shows them as Scheduled while their configured 14/21-day print wait runs. The other 257 were already printable at the time of the audit. No letter print events were manufactured.

## Implementation and verification

The listing uses one latest part-payment/repawn transaction per receipt for Silver expiry, calculated by a ranked derived table. Matching to the pawn summary uses the transaction table's collation; a `BINARY` join was measured at about 5.5 seconds for a branch count, while the collation-compatible join was about 0.06 seconds on this database. Sorting by effective expiry and unique receipt ID was likewise verified with a read-only page query. These changes need no new table, index, or SQL script.

The first-letter waiting values were **not** changed. If the approved policy is “first letter printable on expiry,” prepare a separate, reviewed manual MySQL script for the active receipt-type rows and existing pawn snapshots; preserve genuine historical letter print dates and events. Do not run Laravel migrations.
