# Local customer activation — 2026-10-10

Target: local MySQL database `smartom_sampath` used by the Laravel checkout at `E:\freelance\Sampath\sampath`. No Laravel migration was run.

## Manual SQL run

- Executed all three `CREATE INDEX` statements from `016_customer_policy_indexes.sql`. Verified all three indexes exist.
- Before the data update, backed up all 2,938 rows that would change to `storage/app/customer_status_backup_2026-10-10_local_pre_activation.json` (SHA-256 `a4b12b995a2bdf37412d31be90eba7d3b8afe7425066003ed01a03da59740030`). This backup is not committed.
- Executed the `UPDATE customers` statement from `017_activate_customers_with_active_receipts.sql` in a transaction. It changed only `Status` to `1` for 2,938 customer rows, covering 2,460 distinct NICs. Previous statuses: 2,760 null and 178 zero.

## Verification and exception

- Customer rows still inactive/unset despite a matching active receipt: **0**.
- All four locally existing positive amount/count limit records retained their values.
- Two active `t_pawn_sums` receipts have **no customer row matching their stored NIC** and therefore could not be activated: BC 001 receipt 7828, stored NIC `791813`; BC 002 receipt 2479, stored NIC `766413447V`. No customer records were invented or corrected by this activation. Their identities should be reviewed before any separate customer-enrichment script is run.
