# Customer activation and limit-policy deployment — 2026-10-10

Target: Kreethya production (`/home/kreethya/public_html/sampath`). No Laravel migration was run.

## SQL and data changes actually run

1. Before changing data, saved 2,981 complete affected `customers` rows to `storage/app/customer_status_backup_2026-10-10_pre_activation.json` on the server, mode `0600`. SHA-256: `f2eacd08685f746cace987f536f90ac51ea4cc91a3da769418f7078e0e71caf1`. This is outside the public web root and was not committed.
2. Executed the three `CREATE INDEX` statements in `016_customer_policy_indexes.sql` once on the production database. All three were created.
3. Executed the `UPDATE customers` statement in `017_activate_customers_with_active_receipts.sql` in a database transaction. It changed only `Status` to `1` for customer rows whose NIC matched an active, non-redeemed, non-forfeited receipt in either pawn table. It changed 2,981 customer rows covering 2,498 distinct NICs. Prior statuses among those rows: 2,767 null and 214 zero. No loan, payment, or limit values were changed.

## Verification

- The SQL verification query returned **0** customer rows still inactive/unset while having an active receipt.
- Active receipts with no matching customer row: **0** in `t_pawn_sums`, **0** in `t_opening_pawn_sums`.
- All six pre-existing positive amount/count limit records retained the same limit values.
- Of those six limited customers, five have active receipts. One customer (NIC `732762027V`) has three active receipts across BC 001 and 002, with Rs. 574,000 active capital against the existing Rs. 400,000 amount limit, an excess of Rs. 174,000. Existing receipts were preserved; new pawn/repawn policy checks enforce the limit.
- The detailed, private CSV report is `storage/app/reports/customer_limit_overlap_2026-10-10.csv` in the local workspace. It contains all six limited customers, before/after status, existing limits, current cross-branch exposure, and receipt references. It is not committed because it contains customer information.
- Code commit `1e14fd8c` was pushed to `origin/master` and fast-forwarded onto the server. Server view cache compiled successfully. Local unit tests: 67 passed. Production's Composer installation has no `artisan test` command, so production unit tests could not be run there.

The status backup is the recovery source for this specific activation. Do not rerun the UPDATE to reconstruct prior inactive statuses; it is deliberately idempotent only in the forward direction.
