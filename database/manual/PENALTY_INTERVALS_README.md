# Penalty intervals and manual forfeit review — 2026-09-05

No Laravel migrations or database scripts were executed by Codex.

## Apply manually

The previous 001–003 CREATE TABLE scripts are prerequisites for letter events, promises and lifecycle history. If already installed, do not recreate those tables.

Back up your selected MySQL database, then run these new scripts once, in order:

1. 004_receipt_type_penalty_intervals.sql
2. 005_pawn_penalty_intervals_and_forfeit_queue.sql

004 adds four integer day settings to Receipt Types and supplies 21 for all existing types. 005 supplies the same defaults to existing pawn receipts and adds the manual forfeit-list queue timestamp. Neither script queues or forfeits existing receipts. No existing financial/contact columns are removed.

New pawn receipts copy the four settings from their selected receipt type. Later type changes apply to new pawns; they do not rewrite settings already captured on existing pawns.

## Day definitions

| Setting | Due date |
|---|---|
| First letter days | Expiry date + first interval |
| Second letter days | Scheduled first-letter date + second interval |
| Third letter days | Scheduled second-letter date + third interval |
| Forfeit Reminder days | Scheduled third-letter due date + reminder interval, once the third letter is issued |

All four defaults are 21 calendar days, following the latest request. To keep a first letter on the expiry date, set First letter days to 0 for the relevant receipt type before creating a pawn.

With expiry 2026-01-01 and all defaults, the letter due dates are January 22, February 12 and March 5; the reminder is due March 26 after the third letter is issued. A late print does not move any of these due dates. A printed letter stays on its tab until the next scheduled stage is due; if that date already passed, it moves to the next tab immediately, provided the prior letter was issued. The reminder likewise requires an issued third letter.

For Silver receipts the expiry used by letters is `To_Date`, not `Final_date`. Older Silver rows whose `To_Date` was not refreshed after a part payment or repawn use the latest transaction date plus the receipt's saved valid-day period for letter scheduling.

### 2026-10-04 schedule correction

Letter print dates are retained as historical facts only; they no longer delay the next scheduled stage. This is a code-only calculation change: existing receipt-type and pawn interval values stay as saved, and no SQL script or data update is required. Receipts whose next scheduled date already passed may move to the next tab (or the Forfeit Reminder List after the issued third letter) as soon as the updated code is deployed.

## Revised workflow

- The Reminder List calculates eligibility when opened/refreshed; it requires no scheduled job. Unpaid receipts appear on the due calendar day, including that day.
- Lists use server pagination (10, 25, 50 or 100 receipts per page).
- An overdue promise stays in Reminder List until reviewed. A still-valid promise blocks moving to Forfeit List, including the promise date itself.
- After review, tick the per-receipt checkbox and submit Move to Forfeit List. Receipts with no promise may also be moved after the reminder waiting period.
- Moving is recorded and does not forfeit articles or create stock. Process Forfeit uses the existing final transaction and duplicate protection.
- The Forfeit Receipt List displays both manually queued and already-forfeited receipts, with a status filter. Each row expands to contact, dates, articles, financial breakdown, stock, promises and history.
- Full arrears payment clears letter flags and the forfeit queue, using the existing expiry/payment workflow. Redemption also clears the queue.

## Read-only verification after applying SQL

```sql
SELECT receiptname, letter_1_days, letter_2_days, letter_3_days,
       forfeit_reminder_days
FROM recei__adds;

SHOW COLUMNS FROM t_pawn_sums LIKE 'letter_%_days';
SHOW COLUMNS FROM t_pawn_sums LIKE 'forfeit_%';
```

## Verification performed

PHP syntax checks, JavaScript syntax check, date/eligibility/validation tests, and rendering tests use in-memory fixtures. No feature-writing test was run against the user's database. The unrelated existing home-page test requires MySQL; MySQL was unavailable during this work. Live browser verification was unavailable because the browser connection failed.
