# Production cutover exception review — 2026-10-04

This is a **read-only evidence report**, not an instruction to repair records. No database row, schema, rate, or historical event was changed for this review. No Laravel migration was run.

Evidence checked: the retained final production export and cutover/repair audit described in [PRODUCTION_CUTOVER_PLAN_2026-10-03.md](../../PRODUCTION_CUTOVER_PLAN_2026-10-03.md), the restored source staging database `kreethya_sampath_stage_20261003`, the live imported database `kreethya_sampath_prod_20261004`, and the repair's matching rules in `RepawningMathRepairService`. The source staging and live databases both contain the same 183 pawn-detail rows without a matching pawn-summary parent. The cutover repair left all 14 receipts below unchanged by design.

## What the labels mean

- **Ambiguous repawn** means the automatic financial replay cannot establish one unambiguous sequence of source events. For 13 receipts, two repawn summary rows reuse the same **branch + receipt + repawn number + date**; there are also two matching transaction rows. The database does not say whether both cash payouts occurred, one was an accidental duplicate, or a number was reused. For the fourteenth receipt, the parent pawn summary is absent. The repair therefore skipped the *whole receipt*, not merely one row. **Ambiguous does not mean proven overpayment or proven duplicate cash.**
- **Orphan article row** means a `t_pawn_details` row has no `t_pawn_sums` row with the same `(BC, Receipt_Number)`. This is a database relationship, **not proof that an article is physically missing, available for sale, forfeited, or redeemed**. The status on an orphan cannot be safely inferred from a parent that is not there.

## The 14 repawn receipts held for manual review

The two numbers in each “recorded payouts” cell are the `Payable_Total` values on the two repawn summaries, in ascending summary-ID order. Matching `t_pawn_trans.Cr_amount` values agree with those pairs. These are **recorded amounts, not independently verified cash disbursements**. All 13 duplicate keys have two summary rows and two matching repawn transaction rows. `Active` means `IsRedeemed=0, isForfeit=0` in the existing parent; `Redeemed` means `IsRedeemed=1`. These are the source statuses retained at cutover.

| Branch / receipt | Parent status | Reused repawn no. / date | Repawn summary IDs; recorded payouts (Rs.) | Matching transaction IDs | Why held / result |
|---|---|---|---|---|---|
| 001 / 47 | Redeemed | 50 / 2026-01-12 | 44, 45; 5,000 + 2,000 | 826, 832 | Same event key used twice; original values retained. |
| 001 / 186 | Redeemed | 19 / 2026-01-08 | 16, 18; 5,000 + 3,000 | 391, 398 | Same event key used twice; original values retained. |
| 001 / 2226 | Active | 2181 / 2026-04-06 | 2223, 2225; 40,000 + 10,000 | 12924, 12926 | Same event key used twice; original values retained. |
| 001 / 2415 | Active | 8761 / 2026-10-02 | 6931, 6932; 1,900 + 1,900 | 42056, 42058 | Even the recorded payout and interest match, but this alone does not prove which cash events were real; original values retained. |
| 001 / 2744 | Redeemed | 956 / 2026-02-25 | 951, 952; 15,000 + 15,000 | 6721, 6722 | Even the recorded payout and interest match, but this alone does not prove which cash events were real; original values retained. |
| 001 / 3647 | Active | 5633 / 2026-07-13 | 5010, 5011; 30,000 + 20,000 | 29086, 29087 | Same event key used twice; original values retained. |
| 001 / 4648 | Redeemed | 2578 / 2026-04-12 | 2406, 2407; 5,000 + 5,000 | 14168, 14169 | Same payout, different recorded interest (250.00 / 303.10); original values retained. |
| 001 / 4919 | Active | 4979 / 2026-06-25 | 4585, 4586; 1,000 + 9,000 | 25907, 25909 | Same event key used twice; original values retained. |
| 001 / 5298 | Active | 2942 / 2026-04-27 | 2721, 2722; 5,000 + 4,729 | 15948, 15949 | Same event key used twice; original values retained. |
| 001 / 10427 | Redeemed | 8494 / 2026-09-24 | 6725, 6731; 5,000 + 5,000 | 40873, 40893 | Same payout, different recorded interest (252.10 / 305.22); original values retained. |
| 002 / 2442 | **No parent** | Not a duplicate-key case | Seven repawn summaries: 2884, 2936, 3019, 3047, 5620, 5712, 6512 | 16678, 16931, 17335, 17484, 33380, 34030, 39526 | Parent count is zero; replay requires exactly one. See separate orphan finding below. Original values retained. |
| 002 / 4272 | Active | 3979 / 2026-05-29 | 3763, 3764; 2,000 + 4,000 | 21077, 21079 | Same event key used twice; original values retained. |
| 002 / 4313 | Active | 4089 / 2026-06-02 | 3876, 3880; 10,000 + 30,000 | 21604, 21635 | Same event key used twice; original values retained. |
| 002 / 4654 | Redeemed | 4396 / 2026-06-10 | 4188, 4192; 3,000 + 2,000 | 23125, 23145 | Same event key used twice; original values retained. |

**Result:** 7 active, 6 redeemed, and 1 without a parent. The approved repawn math repair made no correction to these 14 receipts. The presence of a matching transaction row is *internal database evidence* of a posting, not independent confirmation that cash was handed to the customer. The accounting effect, if any, remains unresolved. The seven active receipts should be checked first because their current balance can still affect payments and redemption.

## The 183 orphan article rows

| Group | Exact row identifiers | What the source/live data shows | Meaning / unresolved point |
|---|---|---|---|
| 182 legacy rows, branch 001 | **Every `t_pawn_details.id` from 10806 through 10987 inclusive** (182 rows, 182 distinct receipt numbers, 625892–733776 with gaps) | All have `Bill_System_bill_type='OLD_SYSTEM'`, `Date='2026-05-15'`, `created_at='2024-12-31 18:30:00'`, `IsRedeemed=0`, `isForfeit=0`. Types: A=152, B=27, C=3. There are **zero** matching rows in `t_pawn_sums` (including `old_Receipt_Number`), `t_opening_pawn_sums`, `t_opening_pawn_details`, `t_pawn_trans`, `t_redeem_sums`, or `items` using the same branch and receipt number. No `t_pawn_sums` row anywhere is marked `OLD_SYSTEM` in this import. | The final production export carried an article-only legacy batch with no current pawn-summary/transaction counterpart. Its original import process and physical custody are not established by these tables. The `0` status flags are not independently validated. Do **not** manufacture parent receipts or release/forfeit these articles from this evidence alone. |
| 1 Silver row, branch 002 / receipt 2442 | `t_pawn_details.id=6947` | Article is a Silver Chain dated 2026-03-23, with `Receipt_Type`, `IsRedeemed`, and `isForfeit` all `NULL`. `t_delete_pawn_sums.id=1` records deletion of the parent receipt on 2026-09-15, with a note about a 6% versus 1.98% repawning interest concern. Its pawn, part-payment, and seven repawn transaction records remain; seven repawn summaries remain. | This parent was recorded as deleted while related history and the article row survived. The deletion note explains the operator's stated concern but does not establish the correct historical rate or desired replacement transaction. It cannot be safely repaired by simply changing the article flags or recreating a summary. |

**Result:** all 183 rows were present in the restored source and remain untouched in the imported live database. The cutover status-sync script updated only details that had a real parent; it did not assign status to orphans. The 182 legacy rows and receipt 002/2442 are materially different cases and should not be handled with one bulk update.

## What is needed before any correction

1. For each duplicate repawn key, compare signed/printed repawn receipts, daily cashbook and cashier closing sheets, bank/cash disbursement evidence, and any application/server audit logs. Establish **how many real disbursements occurred** and the correct interest/charge breakdown for each. Do not select the first, last, larger, or smaller database row merely from its ID or value.
2. For the 182 legacy rows, obtain the old-system source register/import mapping and a physical inventory reconciliation. Determine whether their six-digit numbers are standalone legacy receipts, references to another parent numbering system, or stale article-only imports. No matching current-system parent can be inferred from the present database.
3. For 002/2442, review the deleted-receipt record and the original Silver contract/rate evidence with the operator before deciding whether it should stay deleted, be re-entered, or receive any financial correction. Do not infer a historical Silver rate from the deletion note alone.
4. Only after individual decisions are documented should a separate, reviewed, backed-up data-correction script be prepared and tested on a copy. This report does not authorize one. Never use Laravel migrations for this project.

### Reproduce the orphan scope (read-only)

```sql
SELECT d.id, d.BC, d.Receipt_Number, d.Receipt_Type,
       d.Bill_System_bill_type, d.Date, d.IsRedeemed, d.isForfeit
FROM t_pawn_details AS d
WHERE NOT EXISTS (
    SELECT 1 FROM t_pawn_sums AS s
    WHERE BINARY s.BC = BINARY d.BC
      AND s.Receipt_Number = d.Receipt_Number
)
ORDER BY d.BC, d.id;
```

The query should return 183 rows on the retained source/live snapshot: IDs 10806–10987 and ID 6947. It reads data only.
