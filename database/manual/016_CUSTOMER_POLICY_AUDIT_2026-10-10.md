# Customer policy enforcement audit — 2026-10-10

Read-only audit of the Kreethya production database before customer-policy code deployment. No SQL, migrations, or customer/receipt data corrections were run for this audit.

## Existing data

- Actual `users.role` values: `Admin` (9 users, including the `developer` accounts) and `Cashier` (9 users). Admin can select customer records from any branch; Cashier customer edits remain branch-limited.
- Positive customer limits exist only on six legacy `customers` rows with `BC IS NULL`; branch-specific customer rows currently have no positive limits. Code must not use only a branch row or sum duplicated limits. The effective policy for one normalized NIC is: inactive if any matching row has `Status = 0`; otherwise active; the smallest positive amount/count limit from matching rows; null/zero means no limit.
- One customer with a positive limit is over it on **active** receipts (not just historical redeemed/forfeited receipts):

  | Customer | Existing policy | Active receipts and principal | Current exposure | Difference |
  | --- | --- | --- | --- | --- |
  | NALAWATTHAGE SAMPATH, NIC `732762027V` | Amount Rs. 400,000; count 3; legacy status inactive | BC 001 receipt 10588 / invoice 775184: Rs. 182,000; BC 001 receipt 10771 / invoice 775367: Rs. 42,000; BC 002 receipt 4768 / invoice 769543: Rs. 350,000 | Rs. 574,000 across 3 receipts | Rs. 174,000 above amount limit; count equals limit |

- All other five positive-limit legacy customers have active receipt amounts/counts within their existing limits at audit time.
- 272 legacy inactive NICs also have branch-specific customer rows. Of those, **214 have at least one active receipt**. These are existing loans, not errors in themselves. Enforcing inactive status prevents *new pawning and repawning* until the customer is reactivated; it does not erase receipts or prevent normal payments/redemption. Admin should review whether each legacy inactive status remains intended.
- One active `t_opening_pawn_sums` receipt has no matching `t_pawn_sums` receipt and is included in exposure. Matching opening/main receipt keys are counted only once.

## Implementation and rollout notes

- No new columns are required: `customers.Status`, `Limit_Amount`, and `Limit_Pawn_Count` already exist. For efficient locking/counting, run `016_customer_policy_indexes.sql` manually **once before deploying**. It adds indexes only. Never run Laravel migrations for this project.
- Production NIC columns use case-insensitive `utf8mb4_unicode_ci`; a read-only audit found no NICs with surrounding spaces in `customers`, `t_pawn_sums`, or `t_opening_pawn_sums`. This permits direct indexed NIC comparisons while matching case variants such as `v`/`V`.
- Editing either customer page synchronizes the three policy fields for all customer rows of that normalized NIC. Contact/name changes continue to synchronize active receipt snapshots in the selected customer's branch only. Existing loan amounts and statuses are not changed by customer-policy editing.
- New pawning (including the visible Opening Pawn form) and repawning validate policy server-side inside their save transactions, across all branches. Existing over-limit loans are preserved. A repawn that remains above the amount/count limit is rejected until the limit or exposure changes.
- Live audit results are a point-in-time report, not a replacement for a pre-deployment recheck.

## Verification performed locally

- `php vendor/bin/phpunit tests/Unit`: 67 tests, 252 assertions passed.
- Blade compilation succeeded. Read-only local rendering succeeded for the status page, Master Customer page, and pawn-customer search card.
- A separate existing feature test (`ReceiptPaymentStatusTest`, data set `redeem_invoice`) still fails because it calls `RedeemController::searchInvoice()` with one argument although the method requires two; customer-policy code does not touch that path.
- The production database was only queried for the audit. The index script has **not** been executed, and the policy code has **not** been deployed by this task.
