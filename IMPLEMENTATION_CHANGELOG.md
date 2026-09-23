# Sampath implementation change register

This file records the functional changes made for the current project upgrade.
Database schema/data changes are never applied through Laravel migrations. The
authoritative manual SQL register is `database/manual/README.md`.
Production rollout, staging rehearsal, data-repair gates, and verification are
documented in `PRODUCTION_DATABASE_UPGRADE_RUNBOOK.md`.

## 2026-09-24 — Redemption negative balance resolution, cash rounding and silver contract flow

- Pinpointed and resolved the root causes of negative balances on redeemed receipts:
  1. Historical redemptions in `t_pawn_trans` lacked stored `Paided_Interest` values (NULL), causing the ledger to omit the interest debit while crediting full payment.
  2. Sri Lankan cash rounding (where cashiers collect rounded whole rupees, e.g. Rs. 10,466.00 for dues of Rs. 10,465.27) left fractional-cent negative balances (e.g. -0.73 on ticket 4545).
- Implemented multi-tier fallback in `ReceiptHistoryService` to resolve paid interest:
  `t_pawn_trans.Paided_Interest` -> matched `TRedeemSum.Paid_Interest` -> `TPawnSum.Redeem_interest` -> single `TRedeemSum` -> dynamic calculation snapshot.
- Added `attachPaymentAndRepawnInterest` to retrieve missing historical interest for `PART_PAYMENT` from `TPawnPayment.Paid_Interest` and `REPAWNING` from `TRepawningSum.Paid_Interest`.
- Added automatic `Cash rounding` adjustment debit row in `formatLedgerRows` for rounding differences (< Rs. 5.00) and accounted for redemption discounts, ensuring redeemed receipts always settle to exactly Rs. 0.00.
- Updated `RedeemController` and `OldsystemRedeemController` to persist `Paided_Interest` on all future redemptions.
- Updated Silver receipts to allow standard customer-selected loan terms (1–12 months) in `PawnController`, `PawningPartPaymentController`, and `RepawningController`, removing the hardcoded 30-day term restriction while preserving the 30-day initial interest calculation in `SilverInterest`.
- Unified Type "D" interest calculation rules across `ReceiptFinancialCalculator`, `ReceiptHistoryService`, and views.
- All 70 automated tests pass. Zero database migrations run.

## 2026-09-24 — Repawning rate-version audit and historical display

- Verified the 008 repair is based on posted event interest/charges and corrects
  principal; it does not reprice old interest. A read-only dry run of receipt
  001/4520 found zero remaining principal corrections.
- History now carries the rate context from pawn to part payments, changes it
  only at repawning, and uses the receipt's stored snapshot for the latest
  cycle. Closed periods show recorded paid interest, not a calculation made
  from a later master row. If a master row was edited after an event, the old
  rate is explicitly marked unverified rather than displayed as certain.
- Repawning print now uses the updated receipt's saved type snapshot instead
  of an arbitrary first master row.
- Added read-only manual script 012 to review zero-interest repawns and type
  version history and compare transaction/summary interest. The local
  read-only scan found 7 zero-interest repawns out of 6,148; these are review
  candidates, **not** automatic corrections. Receipt 001/4520's latest
  repawn shows Rs. 0.00 interest in both saved records, so 008 cannot infer a
  different amount from principal replay alone.
- No database values or schema were changed and no migration was run.

## 2026-09-23 — Customer directory and receipt-type change history

- Customer directory now queries one branch-scoped page at a time, with
  server-side name/NIC/phone/code search, status filter, compact expandable
  details, and a delayed next-code lookup only when adding a customer.
- Removed client-side DataTables over the full customer set and duplicate
  jQuery loads. Added optional manual customer indexes in script 011; **not run**.
- Receipt Type history now opens in a modal and shows each stored version's
  actual values and save time. Editing a type always inserts a new version,
  including same-day edits, instead of overwriting the prior row.
- Existing receipts retain their stored rate/charge snapshot in live payment
  calculations until repawning starts a new cycle; that repawn selects the
  effective type version and stores a new snapshot. If an older receipt lacks
  a snapshot, resolution falls back to the dated master version.
- Earlier edits that overwrote a single row cannot be reconstructed from the
  present database alone; the production runbook requires prior backup/audit
  evidence before any historical backfill. No live database values were changed
  in this pass, and no Laravel migration was run.

## 2026-09-23 — Receipt financial history and payment calculations

### Receipt Search details and monochrome print

- Fixed the per-row View details toggle in Receipt Search. The shared ledger
  handler now uses browser DOM APIs, so it initializes on the search page even
  when jQuery is absent. The same handler supports dynamically loaded payment
  history rows.
- The history print renders every row's details and operator fully expanded.
  Its stylesheet uses plain black text, white backgrounds and table borders for
  black-and-white printing.
- This is a view/script change. No database values or schema were changed.

- Replaced the Receipt Search financial history with a ledger suited to this
  pawn system: Date, Description, DR, CR and running Balance.
- Applied the same ledger to the history tabs on Part Payment, Redeem and
  Repawning, using one backend calculation path so pages cannot disagree.
- Pawn and repawn advances are debits; customer payments are credits; service,
  postal/letter and accrued-interest charges are explicit debit rows.
- Preserved the actual recorded payment amount. Where inconsistent legacy
  snapshots cannot reconcile, the ledger shows a labelled historical balance
  adjustment instead of changing or hiding the recorded payment.
- Balances are calculated chronologically and displayed newest first. Signed
  balances are retained so a genuine customer credit/overpayment remains
  visible.
- Added the recorded login/operator details to ledger rows where legacy data
  contains an operator. Missing historical operators remain `Not recorded`.
- Updated printable receipt history to use the same ledger and totals.
- Corrected historical rate/principal resolution, current receipt-type
  selection, part-payment allocation and repawning amount calculations.
- No database schema or data change is required for this update. No SQL script
  or migration was executed.

### Legacy repawning data repair

- Added the dry-run-first `repawning:repair-math` command and
  `RepawningMathRepairService` to replay existing pawn, part-payment and
  repawning events chronologically.
- The repair corrects existing row values in place and never creates artificial
  adjustment transactions. Apply mode generates private before/after JSON and
  rollback SQL before changing data.
- Ambiguous reused repawning keys are excluded and reported for manual review.
- Usage and changed-field details are recorded in
  `database/manual/008_REPAWNING_MATH_REPAIR.md`.
- Execution on 2026-09-23 corrected 3,816 receipts / 11,954 existing rows in
  total (the known receipt first, then the remaining safe set). A final dry run
  found zero remaining automatic corrections. Twelve ambiguous receipts were
  left unchanged for manual review; recovery files are recorded in the 008
  document.
- New repawning submissions now use the repawning table's own number sequence,
  reject a reused branch repawning number under the receipt lock, and disable
  the submit button while the request is being saved.
- Ledger history now uses the repaired stored interest/principal amounts for
  closed periods and calculates interest only for the current open period. It
  no longer creates display-only historical adjustment rows.
- Ledger main rows show only the useful summary (interest, paid capital, paid
  interest, repawn/payment/charge amount). Full receipt/operator details are
  collapsed under a per-row View details control; printed history is expanded.

## 2026-09 — Forfeit stock transfer and payment eligibility

- Corrected Stock Number to mean `Invoice_Number`; Ticket Number remains a
  separate field.
- Added receipt-level selection to the Forfeit Article List. The confirmed
  operation moves every eligible article of each selected forfeited receipt to
  sale stock and produces a detailed audit print.
- Added transaction locks, stale/duplicate validation and lifecycle-event
  history for transfers.
- Excluded redeemed and forfeited receipts from Redeem, Part Payment and
  Repawning search/submission flows while retaining them in historical reports.
- Synchronized new article/parent status handling. Legacy status repair is
  optional and documented as manual script/helper 006.
- Added total interest-day counts beside Interest on redeem and part-payment
  printouts.

## 2026-09 — Arrears letters and Forfeit Reminder workflow

- Added the searchable arrears letter report without removing existing working
  columns; receipt date/time and customer contact details are report fields.
- Added first, second and third letter scheduling from receipt-type intervals,
  with 21-day defaults and receipt-level snapshots.
- Added receipt-type postal/service charge handling and explicit charge history.
- Added the Forfeit Reminder List after the third-letter waiting period. It
  supports promise remarks, editable future promise dates, overdue highlighting
  and pagination.
- Kept forfeiture manual: an unpaid reminder receipt is selected and moved to
  the Forfeit List; it is not automatically forfeited after letter three.
- Added detailed expandable Forfeit Receipt rows, filters, history view and
  Invoice Number display.
- Added Receipt Search with complete printable receipt history.
- Added telephone display on new pawning and centralized current customer
  contact lookup so customer-table updates are reflected throughout views.
- Added the new functions under their relevant existing sidebar menus.
- Manual database scripts 001–005 provide the tables/columns required by these
  features. They were created but not executed by Codex.

## 2026-09-23 — Silver receipt flow and receipt blocking

- Fixed Receipt Type history navigation to `/master_receipt?history=1` and included active SILVER configurations even when their legacy `validPeriod` is zero. Silver is excluded from ordinary gold amount-band lookups.
- Centralized Silver interest: one configured period's charge through day 30, then daily proration using the receipt type's period and rate. Applied the same calculation to server-side payment totals, receipt history, part-payment/redeem/repawn previews, forfeit preview and bulk notice print.
- Corrected the Silver history interest-cycle start after part payment and the new pawn's stored interest rate. Silver extension previews now offer the configured one-period term instead of gold amount-band terms.
- New Silver pawn, part-payment and repawn expiry dates now use the receipt type's configured valid days, falling back to its 30-day period. Existing stored expiry dates were **not rewritten**; review legacy Silver rows before any data repair.
- Added an admin-only Block Receipts page under Pawning. Blocking is receipt-specific and audited for pawn receipts. Redemption searches and both current/legacy redemption store paths reject blocked receipts; part payments and the late-letter/reminder/forfeit paths remain eligible.
- Added manual schema script `database/manual/009_receipt_block_status.sql` and matching rollback for pawn/opening-pawn status. Neither script was executed by Codex. Apply script 009 manually before using blocking.
- Added read-only `database/manual/010_silver_expiry_audit.sql` to identify legacy Silver expiry discrepancies without rewriting historical contracts.
- The attempted read-only live database query did not return and was interrupted; no live Silver rows were inspected or changed in this update.
- Ran isolated Silver interest and block-eligibility tests, syntax checks and Blade compilation. No migrations were run.

## Deployment rule

Before deploying against an older database:

1. Take and verify a full database backup.
2. Compare the target schema with `database/manual/README.md` using its
   read-only checks.
3. Run only missing scripts, in numeric order, through the approved manual
   database process.
4. Never run `php artisan migrate` in this project.
5. Verify letter timing, one active receipt, one closed receipt and one legacy
   multi-payment receipt in staging before production use.
