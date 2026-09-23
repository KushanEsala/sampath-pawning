# Manual database upgrade register

Last reviewed: 2026-09-23

This project does not use Laravel migrations. Codex did not execute any file in
this directory. Back up the selected database, review each script, and run only
the scripts required for the target installation.

## Required execution order

| Order | File | Change | Required for |
|---|---|---|---|
| 001 | `001_create_arrears_letter_events.sql` | Creates the immutable arrears-letter issue/charge log. | Letter history, idempotent printing and operator history. |
| 002 | `002_create_forfeit_reminder_promises.sql` | Creates promise dates, remarks, extensions and promise status history. | Forfeit Reminder List. |
| 003 | `003_create_receipt_lifecycle_events.sql` | Creates general receipt lifecycle/audit events. | Receipt Search history and forfeited-article stock-transfer audit/reprint. |
| 004 | `004_receipt_type_penalty_intervals.sql` | Adds four day intervals to `recei__adds`; existing types default to 21 days. | Receipt-type letter/reminder timing setup. |
| 005 | `005_pawn_penalty_intervals_and_forfeit_queue.sql` | Snapshots the four intervals on `t_pawn_sums` and adds `forfeit_queued_at`. | Existing/new pawn timing and manual movement to the Forfeit List. |
| 006 | `006_sync_pawn_article_status.sql` | Optionally adds `t_pawn_details.isForfeit` and reconciles detail status with parent receipts. | Optional legacy-data repair only. |
| 007 | `007_receipt_type_effective_dates.sql` | Adds effective date/version status to `recei__adds`. | Correct historical/current receipt-type rate selection. |
| 009 | `009_receipt_block_status.sql` | Adds receipt-level block status and admin reason to pawn and opening-pawn summaries. | Admin block/unblock screen and redemption guard. |
| 011 | `011_customer_directory_indexes.sql` | Optional branch/customer lookup indexes; no data rewrite. | Large customer directories after page-level pagination. |

Data repair 008 is documented in `008_REPAWNING_MATH_REPAIR.md`. It is an
Artisan command backed by a Laravel service, not SQL and not a migration. Run it
in dry-run mode first; apply mode creates private audit and rollback files.

`010_silver_expiry_audit.sql` is a read-only report of active Silver receipts
whose stored expiry differs from the configured Silver day period. Review
contract dates and payment/repawn history individually before any data repair.
The `011` index script is optional and should only be run if the named indexes
are absent. Schedule index creation during a maintenance window.
`012_repawning_interest_review.sql` is read-only. It flags zero recorded
repawning interest and lists receipt-type versions for manual comparison. It
must never be imported as a data repair. The earlier 008 replay corrected
principal using posted interest/charges; it did not recalculate historical
paid interest from master rates.

Scripts 001–005, 007 and 009 are one-time schema scripts, not idempotent. Do not run
them again when their tables/columns already exist. Matching rollback scripts
are supplied for 001–005, 007 and 009, but rollback removes new structure and must
not be used on a live system without a reviewed recovery plan.

## Article-status repair and lock timeouts

Script 006 contains full-table `UPDATE ... JOIN` statements. A busy database
may return MySQL error 1205 while those statements wait on application locks.
Do not repeatedly launch the same bulk update and do not terminate sessions
without reviewing them.

- `inspect_article_status_locks.php` is read-only lock/process diagnostics.
- `sync_article_status_batches.php` performs a dry run by default and uses small
  transactions when explicitly invoked with `--apply`. It is restricted to the
  configured local `smartom_sampath` database and creates a private SQL backup
  before changing rows.
- The batch repair expects the `isForfeit` detail column to exist. If script 006
  timed out after its `ALTER TABLE`, verify the column before using the helper.

These helpers are manual maintenance tools, not Laravel migrations. They have
not been run as part of the application implementation.

## Read-only checks

```sql
SHOW TABLES LIKE 'arrears_letter_events';
SHOW TABLES LIKE 'forfeit_reminder_promises';
SHOW TABLES LIKE 'receipt_lifecycle_events';

SHOW COLUMNS FROM `recei__adds` LIKE 'letter_1_days';
SHOW COLUMNS FROM `recei__adds` LIKE 'effective_from';
SHOW COLUMNS FROM `t_pawn_sums` LIKE 'forfeit_queued_at';
SHOW COLUMNS FROM `t_pawn_sums` LIKE 'is_blocked';
SHOW COLUMNS FROM `t_opening_pawn_sums` LIKE 'is_blocked';
SHOW COLUMNS FROM `t_pawn_details` LIKE 'isForfeit';
```

## Updates that require no SQL

The following are application-code/report changes and do not modify the
database schema or existing data:

- Ledger-style Receipt Search and payment-page history (Date, Description, DR,
  CR, signed Balance, operator and printable totals).
- Correct historical principal/interest calculations and receipt-type
  effective-date lookup once script 007 has already been installed.
- Repawning, part-payment and redemption calculation/UI corrections.
- Closed-receipt payment-screen protection.
- Responsive/expandable arrears, reminder and forfeiture report layouts.

Do not create or run a placeholder SQL file for a code-only update.
