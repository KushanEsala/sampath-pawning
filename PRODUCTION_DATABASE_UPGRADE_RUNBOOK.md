# Production database upgrade and data reconciliation

Last reviewed: 2026-10-03. This is the production run plan; it is **not** a
record that production was changed. Never run a Laravel migration. Never import
`E:/GAMESZZ/t_pawn_sums.sql`: it is a phpMyAdmin export of a calculated query,
not a pawn-table backup or repair script.

For the 2026-10-03 `smartom_sampath (2).sql` export and proposed replacement of
Kreethya's existing business data, follow the source-specific
`PRODUCTION_CUTOVER_PLAN_2026-10-03.md` before executing this general runbook.

## Incoming production export and existing Kreethya data

The new production SQL export must first be inspected and restored into a
**separate staging database**, never imported over `kreethya_sampath` directly.
Check its database name, `DROP`/`CREATE`/`INSERT` statements, schema version,
triggers, routines, character set, row counts and receipt-key uniqueness.
Compare source and destination table-by-table, including all pawn, payment,
repawn, redemption, article, customer, letter, promise and lifecycle records.
Identify records created on Kreethya after the source export; decide explicitly
whether they must be preserved and merged or whether the target will be fully
replaced. A full import otherwise risks deleting or duplicating those records.

Read-only Kreethya inventory on 2026-10-03: the target database was
`kreethya_sampath`, and the objects/columns/indexes from manual scripts
001–005, 007, 009 and 011 were present; `t_pawn_details.isForfeit` also existed.
The target held 26,296 customers, 16,835 pawn summaries, 19 letter events,
9 promise records and 112 lifecycle events. These counts will change with live
activity. Schema presence does **not** prove that optional article-status
reconciliation or repawning repair 008 was run on every source record.
`DatabaseSeeder` has no active seed operations; do not run `db:seed` during this
upgrade.

## 1. Freeze, identify, and back up

1. Schedule a maintenance window with no new pawn, payment, repawn, redemption,
   forfeiture, or customer-edit transactions. Record app revision, PHP version,
   database server/version, selected database name, and start time. Confirm the
   SQL client points at **production**, not the local `smartom_sampath` copy.
2. Take a complete consistent MySQL backup (schema, data, triggers, routines)
   and restore it to a separate staging instance. Verify row counts and that a
   sample receipt plus its payments, articles, and letter history can be read.
   Retain the pre-upgrade backup outside the web root.
3. Export a second receipt-type snapshot: all `recei__adds` rows, with IDs,
   rates, charges, effective dates, active flags, and timestamps. Once past
   edits overwrote a row in place, that row's previous values cannot be
   reconstructed from its new date; restore them only from a trustworthy older
   backup or audit log, never by guessing.

## 2. Read-only baseline and schema inventory

Run the checks in `database/manual/README.md` and save their output. Also run:

```sql
SELECT DATABASE(), VERSION();
SHOW CREATE TABLE `customers`;
SHOW CREATE TABLE `recei__adds`;
SHOW CREATE TABLE `t_pawn_sums`;
SHOW CREATE TABLE `t_pawn_details`;
SELECT TABLE_NAME, INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_in_order
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('customers','recei__adds')
GROUP BY TABLE_NAME, INDEX_NAME;
SELECT receiptname, COUNT(*) AS rows_total,
       SUM(CASE WHEN is_active = 1 AND effective_to IS NULL THEN 1 ELSE 0 END) AS active_rows
FROM recei__adds GROUP BY receiptname;
SELECT BC, COUNT(*) AS customer_count FROM customers GROUP BY BC;
```

If script 007 has not yet run, the active-version query must wait until after
its columns are added. Compare the production schema to each numbered manual
script. **Do not rerun an `ALTER` or `CREATE` for an existing object.** The
snapshot `smartom_sampath.sql` predates the updates and is not a reliable
description of production.

## 3. Staging rehearsal and ordered manual scripts

Deploy the application revision to staging with its restored production
database. Review and run only missing scripts from `database/manual/README.md`
in this dependency order: 001, 002, 003, 004, 005, 007, 009. Script 006 is a
separate optional article-status reconciliation; it is not part of the normal
schema sequence. Script 010 is **read-only** Silver expiry audit. Script 011 is
an **optional** customer index build, run only when the named indexes are absent
and measured page/search performance warrants it. These are manual MySQL
scripts, not Laravel migrations. Save execution timestamps and output per file.

Run `SHOW COLUMNS`/`SHOW INDEX` again and confirm there is one current type row
per type, including SILVER. Inspect any type with zero or multiple active rows
before editing it. Verify receipt-type history popup displays stored version
values and save timestamps. Test a same-day type edit in staging: it must add a
new row, retain the old row unchanged, and keep an already-issued pawn's rate
snapshot. A new pawn should use the new type values.

## 4. Data corrections are separate decisions

- **Repawning math (008):** Follow
  `database/manual/008_REPAWNING_MATH_REPAIR.md`. Run
  `php artisan repawning:repair-math` without `--apply` on staging first. Review
  each proposed row and the twelve known ambiguous receipt keys in the 008
  document. Apply only after approval, using `--apply`; archive the private
  before/after JSON and rollback SQL. Rerun dry-run and require zero automatic
  changes. The local repair result does **not** prove production is repaired.
- **Historical rates and posted interest (012):** Run the read-only
  `012_repawning_interest_review.sql`. The 008 principal replay uses the paid
  interest and charges already posted for each repawn. It is intentionally not
  a retrospective interest-repricing command. Check any suspect zero/incorrect
  paid-interest event against the receipt's rate snapshot, the master version
  that existed at the event, signed printout/transaction, and any waiver.
  Master rows overwritten before versioning do not establish their old rate.
  Do not bulk-update financial history from current master rates.
  In the local read-only check on 2026-09-24, receipt 001/4520 had no further
  008 principal corrections. Its 2026-09-09 repawn recorded Rs. 0.00 paid
  interest in both the summary and transaction, while the archived A master
  row was updated later on 2026-09-23. These facts do **not** prove what the
  original rate or waived interest was. The seven local zero-interest repawns
  require signed records/older backups before any financial data correction;
  do not reuse these counts as production results.
- **Article status (006):** The bulk `UPDATE ... JOIN` can wait on locks and has
  previously timed out. Check the target columns first. Use the documented
  small-batch helper in dry-run mode, review discrepancies, then apply during
  maintenance only if needed. Keep its backup. Do not retry the huge statement
  blindly or terminate unrelated sessions.
- **Silver expiry (010 and 013):** The client's confirmed letter-expiry basis is
  `To_Date`, with a configurable receipt-type day period, not a hardcoded
  30 days. Run both read-only audits and compare contract terms, selected type,
  renewal dates and actual interest period. The application derives an
  effective expiry for legacy Silver rows whose `To_Date` was not refreshed
  after a payment or repawn. Do not bulk-rewrite stored legacy dates without
  receipt-level review.
- **Receipt-type history:** New edits are versioned. Already-overwritten old
  values are not recoverable from current rows. Never synthesize prior rates or
  backdate a version without independent source evidence. Historical pawn rate
  snapshots must be preserved; do not bulk-copy today's master rates to them.

## 5. Production execution and acceptance

Repeat the *rehearsed* schema steps on production under the freeze; run only
approved data repairs. Keep a per-step log of database name, script checksum,
operator, start/end time, affected rows, verification query, backup identifier,
and any rollback file. Deploy code and clear/rebuild application caches only
after its required schema is present. Never run `php artisan migrate`.

Smoke-test at least one example in each branch: customer search/edit; A/B/C
and SILVER new pawn; old receipt retaining its rate; new receipt using changed
rate; same-day receipt-type version history; part payment; redemption;
repawning; arrears letters; reminder promise; manual forfeit; sale-stock
transfer; blocked receipt; ledger and monochrome print. Compare principal,
interest, service and postal charges to hand calculations. For any mutation
test on production, use a prearranged reversible test record and record its
rollback; prefer staging for write tests.

If a verification fails, stop subsequent steps. Restore from the verified
backup or use the specific reviewed rollback for the exact completed step.
Never run a broad rollback over later live transactions. Reconcile new writes
before restoring an old backup.
