# Late Letters visibility audit — 2026-10-07

Read-only review of `kreethya_sampath_prod_20261004`. No Laravel migration, SQL update, or historical letter event was run or created during the audit. Counts are a snapshot and will change with payments, printing, and time.

## Findings

1. **First-letter waiting period (superseded below).** The active receipt-type versions stored `letter_1_days` as **14** for A/SILVER and **21** for B/C. Existing pawn receipts held their own saved interval: 129 had 14 days and 18,398 had 21 days. The initial visibility correction listed expired receipts as Scheduled during that wait. The client subsequently confirmed that the first letter must instead be printable on expiry; see the later rule confirmation below.
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

The Silver numbers use the same dynamic expiry rule as the application, including the historical receipt-type fallback for four receipts with no saved valid-day period. These 378 records were not lost; they were excluded only by the old saved first-letter wait at the time of this snapshot. The confirmed rule below now makes every eligible expired first letter printable on expiry. No letter print events were manufactured.

## Implementation and verification

The listing uses one latest part-payment/repawn transaction per receipt for Silver expiry, calculated by a ranked derived table. Matching to the pawn summary uses the transaction table's collation; a `BINARY` join was measured at about 5.5 seconds for a branch count, while the collation-compatible join was about 0.06 seconds on this database. Sorting by effective expiry and unique receipt ID was likewise verified with a read-only page query. These changes need no new table, index, or SQL script.

Historical first-letter waiting values were **not** changed in MySQL. The subsequently approved expiry-day print rule is implemented in code while preserving old snapshots and genuine historical print dates; no manual bulk SQL script is needed. Do not run Laravel migrations.

## Later rule confirmation on 2026-10-07

The client subsequently confirmed that Letter 1 must be printable on expiry for old and new receipts. The application now ignores historical `letter_1_days` values for scheduling, while retaining those saved values as audit data. No bulk database rewrite or historical letter event is required. Silver receipts with a newer payment/repawn cycle use that cycle date plus the receipt's valid-day term, even when a stale `To_Date` is later than the payment date. This supersedes the earlier pending-policy statement above; the audit counts remain a snapshot of the previous application state.

A read-only check of active Silver receipts on Kreethya found **zero** rows where a later stored `To_Date` differed from the latest payment/repawn date plus the saved valid-day term. The precedence fix protects this case without changing current active Silver expiry dates in that subset. The earlier audit's rows with an already-stale `To_Date` remain governed by the latest payment/repawn cycle.

The confirmed first-letter-on-expiry rule also moves the later scheduled stage dates forward by each receipt's old first-letter wait. A read-only snapshot of active **non-Silver** receipts found 98 printed-first receipts newly due for Letter 2, 34 printed-second receipts newly due for Letter 3, and 122 printed-third receipts newly due for Forfeit Reminder (both branches combined). This only changes list eligibility; it does **not** create a letter print, promise, manual forfeit queue, or article sale-stock record. A separate Silver approximation found one newly due second letter and one newly due third letter; eight Silver rows without saved valid days require the application's historical type fallback, so that approximation excludes their effective expiry.
