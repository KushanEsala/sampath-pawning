# Production database cutover plan — 2026-10-03

Status: **cutover executed on 2026-10-04 Asia/Colombo**; see the execution
record below. No Laravel migration or seeder was run. This plan supplements
`PRODUCTION_DATABASE_UPGRADE_RUNBOOK.md` and `database/manual/README.md`.

## 1. Source and destination evidence

- Source export: `C:/Users/kusha/Downloads/smartom_sampath (2).sql`, SHA-256
  `5B486C2F09F76CC152DAD73B12908B4B43A5CFEF3232369E0FF02CABD93EBCDF`,
  50,387,997 bytes. Its header says `smartom_sampath`, MySQL 8.0.38 and
  generation time 2026-10-03 12:45 PM (dump/server time zone not established).
- It contains 51 `CREATE TABLE` statements and data inserts. It has no
  `CREATE DATABASE`, `USE`, `DROP TABLE`, trigger, routine or event statement.
  Therefore it must be imported into an explicitly selected **empty** database;
  it is not a safe overlay for the existing Kreethya schema.
- The dump contains the original `migrations` table and its old history. That
  is source data, **not** permission or a need to execute Laravel migrations.
- The current Kreethya `kreethya_sampath` database already has upgrade schema
  and live records. The owner has said its current business data need not be
  retained for the production cutover; nevertheless, take a verified full
  backup before replacing or switching away from it.
- The source reports MySQL 8.0.38; Kreethya reports MariaDB 11.4.9. The dump
  uses `utf8mb4_bin`, `utf8mb4_general_ci` and `utf8mb4_unicode_ci`, not a
  MySQL-8-only `0900` collation. Cross-engine import still requires a complete
  staging restore and query-level verification before cutover.
- The deployed application revision observed on Kreethya was `0dc67daa`.
  Record the actual revision again at cutover; do not assume it is unchanged.

## 2. Missing upgrade objects in this export

| Step | Manual file or command | Decision for a fresh restore of this dump |
|---|---|---|
| 001 | `database/manual/001_create_arrears_letter_events.sql` | Required: table absent. |
| 002 | `database/manual/002_create_forfeit_reminder_promises.sql` | Required: table absent. |
| 003 | `database/manual/003_create_receipt_lifecycle_events.sql` | Required: table absent. |
| 004 | `database/manual/004_receipt_type_penalty_intervals.sql` | Required: four receipt-type interval columns absent; script gives existing types 21-day defaults. |
| 005 | `database/manual/005_pawn_penalty_intervals_and_forfeit_queue.sql` | Required: pawn interval snapshots, queue timestamp and index absent. |
| 006 | `database/manual/006_sync_pawn_article_status.sql` | **Do not run wholesale.** `t_pawn_details.isForfeit` and `t_opening_pawn_details.isForfeit` already exist in the dump. Audit parent/detail mismatches, then use a reviewed small-batch correction only if necessary. |
| 007 | `database/manual/007_receipt_type_effective_dates.sql` | Required: version dates/status/index absent. Four current type rows are A, B, C and SILVER. This initializes existing rows; it cannot reconstruct overwritten older rates. |
| 008 | `php artisan repawning:repair-math` | Data repair, **not** a migration or seeder. Dry-run on the restored copy, review row-level proposals and ambiguous receipt keys, then approve/apply with its private audit and rollback files only if needed. |
| 009 | `database/manual/009_receipt_block_status.sql` | Required: block columns absent from pawn and opening-pawn summaries. |
| 010, 012, 013 | Corresponding SQL audit files | Read-only diagnostics; never import them as repairs. |
| 011 | `database/manual/011_customer_directory_indexes.sql` | Optional performance indexes. Names are absent from the dump; check after restore and apply only if useful. |

`database/seeders/DatabaseSeeder.php` has no active seed operations. Do not run
`db:seed`. No other upgrade seeder is registered. Script 008 is the only
documented application command that changes legacy financial data. Its prior
local execution record (3,816 receipts corrected, 12 ambiguous left alone)
does **not** mean this newly exported production database has been repaired.

## 3. Rehearsal on a separate database

1. Confirm the source's transaction cutoff. If pawns/payments can continue
   after this export, use this file for rehearsal only and obtain a **fresh
   final export after writes are stopped** for cutover. Check its checksum,
   creation time and database identity. Do not mix rows from two snapshots.
2. Restore the export into a new, empty, isolated staging database. Capture
   import exit status and all SQL errors. The phpMyAdmin `START TRANSACTION`
   does not make table creation atomic in MySQL; on any error, discard the
   incomplete staging database and restart from a clean one.
3. Before changes, record exact per-table row counts for all 51 source tables,
   branch-level counts and money totals for pawn summaries, payments, repawns,
   redemptions and forfeits. Check duplicate `(BC, Receipt_Number)` pawn keys,
   orphaned details/transactions, active/redeemed/forfeited flags, and
   parent/detail `IsRedeemed` and `isForfeit` mismatches. Compare sample
   receipt histories across A/B/C/SILVER and both branches.
4. Apply missing schema scripts **001, 002, 003, 004, 005, 007, 009** in that
   order through a manual MySQL client. Before each file, confirm its target
   objects are absent. Record file SHA-256, start/end times and verification.
   Do not run Laravel migrations. Consider 011 only after checking indexes.
5. Run 010, 012 and 013 read-only audits. For Silver, letter expiry uses
   `To_Date` and the configured receipt-type period; do not hardcode 30 days
   or rewrite legacy contract dates. The source SILVER type currently has
   `validPeriod=0` and `period3=30`; verify its intended configuration.
6. Run 008 without `--apply` on staging. Review every proposed financial
   correction, especially zero-interest repawns and reused repawn keys.
   Compare recorded paid interest with source transaction/print evidence and
   historical receipt-rate snapshots. Apply only the unambiguous, approved
   corrections, preserving the command's JSON/rollback SQL outside the web
   root. Rerun dry-run and require zero remaining automatic corrections.
7. Audit article flags. Because 006's full-table `UPDATE ... JOIN` previously
   timed out and the batch helper is restricted to local `smartom_sampath`,
   do not run either against Kreethya unchanged. Prepare a target-safe,
   backed-up batch repair only if mismatch counts justify it.
8. Recheck every source table's count after schema upgrades; only approved
   repair columns/rows may change. Check the three new tables exist and are
   empty initially. Historical printed-letter flags/dates may exist without
   new `arrears_letter_events`; do not fabricate issue charges or operators.
   The existing flags prevent repeat issuance, while new events begin with
   future prints.

## 4. Application acceptance on the rehearsed database

- Confirm one current receipt and one old multi-payment/repawn receipt per
  branch reconcile in Receipt Search and payment history: capital, accrued
  interest, paid interest, service/postal charges and closing balance.
- Check A/B/C/SILVER receipt-type selection and historical rate snapshots.
  Script 007 creates one active row per type from the four present rows, not a
  genuine older version history. Do not backdate or invent former rates.
- Check letter eligibility, printed-row toggle, print ordinal, interval waits,
  Silver `To_Date`, reminder promise, manual forfeit and sale-stock transfer.
- Check blocked receipt restrictions, customer contact lookup, redemption,
  part payment and repawning. Use reversible test records only in staging;
  verify their side effects and rollback before production cutover.
- Confirm the imported `users` and roles provide the intended production
  accounts, then verify login and branch access without exposing credentials.

## 5. Cutover, verification and rollback gates

1. Agree a maintenance window and freeze **source and Kreethya** writes.
   Take a fresh final source export if there have been writes since this file.
   Rehearse that exact final file, not only the earlier snapshot.
2. Take and verify full backups of both databases. Prefer a blue/green
   database switch: prepare the upgraded source in a separate database on
   Kreethya, validate it there, then point the app to it. If the hosting
   permissions require an in-place replacement, use a separately approved,
   exact-target restore procedure after the backup. Never import this dump
   over the populated `kreethya_sampath` tables.
3. Repeat the verified manual scripts and only approved repairs on the final
   snapshot. Keep import logs, exact row counts, repair audit files and
   SHA-256 checksums. The final database must match the rehearsed schema and
   expected counts/aggregates after known repairs.
4. Switch the application database configuration only after checks pass.
   Rebuild Laravel caches as the `kreethya` site user, then run read-only
   route/report checks before releasing writes. No `php artisan migrate`.
5. If a gate fails, keep the old target backup and do not release writes.
   Roll back the database switch or restore the exact pre-cutover backup.
   Once new production writes begin, reconcile them before any rollback; a
   blind restore would lose those writes.

## 6. Execution record to complete during the cutover

For each step record operator, timestamp, host/database, source dump checksum,
script/command checksum, pre/post row counts, warnings, verification evidence,
backup location and rollback identifier. Mark 006, 008 and 011 explicitly as
**not needed**, **dry-run only**, or **approved and applied**. Do not infer that
schema presence proves a historical data correction was performed.

## 7. Execution record — 2026-10-04 Asia/Colombo

- The owner confirmed the supplied export was final with no later source
  transactions, and confirmed that the previous Kreethya business data need
  not be retained for normal operations. The old `kreethya_sampath` database
  was nevertheless backed up and **not deleted**. Its backup is
  `/home/kreethya/sampath-cutover-20261003-oJ84ft/kreethya_pre_cutover.sql`
  (SHA-256 `fe544b6299a5b3fdb86412e7113ca9c091c3af0de2a8470938b984eb7b7feaf3`).
  That directory is outside the web root and root-owned/private.
- The source export copied to the server matched its local SHA-256 exactly.
  It restored without SQL errors into isolated
  `kreethya_sampath_stage_20261003`: 51 original tables, 26,538 customers,
  18,335 pawn summaries, 19,966 pawn details, 42,180 pawn transactions,
  7,712 pawn payments, 6,968 repawn summaries, 9,163 redemption summaries
  and 71 forfeiture summaries. Branch receipt counts were 001=10,703 and
  002=7,632. No receipt key was duplicated within `(BC, Receipt_Number)`.
- Manual schema scripts **001, 002, 003, 004, 005, 007 and 009** applied to
  staging in numeric order. Optional customer indexes **011** were also
  applied. Four receipt types (A/B/C/SILVER) each had one active version.
  The upgraded schema had 54 tables. Scripts 010, 012 and 013 were run only
  as read-only audits; their private outputs are in the cutover directory.
- The 008 repair dry run scanned 4,282 repawning receipts and proposed
  corrections for 4,250 receipts / 14,186 rows. Staging was backed up first
  as `stage_before_repawning_repair.sql` (SHA-256
  `d392daa24342ca7bfd9cfbdab8d56cae2dbe88fa95f68a2213a077683d928649`).
  The approved `repawning:repair-math --apply` corrected those 14,186 rows
  in place, generated private before/after JSON and rollback SQL, and ended
  with `REPAWNING_MATH_REPAIR_VERIFIED` (zero further automatic corrections).
  The audit/rollback copies are in the cutover directory with prefix
  `repawning_math_20261003_234150_2ef1e9`.
- **Fourteen ambiguous repawning receipts were excluded**, including seven
  still active. They retain their original source values. The owner explicitly
  chose to go live with these unchanged and review them later. The identifiers
  are 001/47, 001/186, 001/2226, 001/2415, 001/2744, 001/3647, 001/4648,
  001/4919, 001/5298, 001/10427, 002/2442, 002/4272, 002/4313 and
  002/4654. Receipt 002/2442 has a repawn row but no pawn parent.
- Before article-status reconciliation, 9,864 joined pawn-detail rows had
  `IsRedeemed=0` while their parent was redeemed. A separate backup
  `stage_before_article_status.sql` (SHA-256
  `cdddfe202a93eb47a60a38f9f127492fa0216b0d4f1309e15e3bcbe48e4556d0`)
  was taken. Manual script **006** then synchronized statuses on the isolated
  staging database; the verified parent/detail mismatch count is zero.
  **183 orphan pawn-detail rows** from the source have no parent and were not
  modified; only one matches a deleted-pawn summary. Opening-pawn statuses
  had no mismatches.
- Audit 012 found 9 zero-interest repawns and 22 interest-copy discrepancy
  rows; the latter have transaction matches and include reused-key cases.
  Silver audits 010/013 returned 339/1,031 data rows respectively. No posted
  interest or legacy Silver dates were bulk-rewritten.
- All 54 staging tables passed `mysqlcheck`. Receipt history and monochrome
  print rendered for active/redeemed A and SILVER samples in both branches.
  Late-letter, Silver-filtered letter, reminder and forfeit pages rendered
  for both branches against staging.
- A final MariaDB dump of corrected staging data was saved as
  `production_ready.sql` (SHA-256
  `c0051694756377e6b7b1674b81cdbdb4e419ec57eef93843157d215212c4abf8`)
  and restored to `kreethya_sampath_prod_20261004`. Exact row-count plus
  extended table-checksum manifests matched for **all 54 tables**: SHA-256
  `25db161a2a8cbe41b33ee494498042d3f39e08449f261cd15d340bb0aa9468a3`.
- During maintenance mode, the site's `.env` was backed up in the private
  cutover directory, changed to the new database, and Laravel config/views
  were cached as the `kreethya` site user. The app connected to the new
  database and rendered Receipt Search and Silver late letters. The site was
  reopened: `/login` returned HTTP 200 and protected late letters returned
  HTTP 302 to login. The old database remains intact for rollback. Local
  temporary `.env` copy was removed. No Laravel migration or seeder ran.

Follow-up: review the 14 ambiguous repawns and 183 orphan article rows from
the retained audit/source evidence. Do not guess which duplicate repawn was a
real disbursement, and do not fabricate historical letter events or rates.
