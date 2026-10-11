# Letter/history postage display correction — 2026-10-11

Never run Laravel migrations. This change requires no schema or data update.

The per-letter postage rate comes from the receipt type/version applicable to the receipt's pawn cycle, unless the receipt already has a positive saved letter charge or postage snapshot. The current master receipt-type rate must not overwrite a historical saved amount. `t_pawn_trans.Postage_charge` is a cumulative/legacy transaction value and must not be divided by a letter count to infer a historical per-letter rate.

The shared calculator now supplies the per-letter breakdown and total used by the late-letter report, payment totals, receipt-history fallback rows, and letter printout. The letter printout no longer shows zero in its individual letter columns while showing a positive postage total.

The 629 genuine zero-charge events repaired by `019` remain recorded in the database. The 18 events skipped by that repair remain untouched: 13 have a later pawn/payment cycle and five belong to closed receipts. Review their source evidence before changing historical amounts; do not fabricate letter events or rates. This code change does not rewrite any historical row.

## Deployment and verification

Code commit `c81ce18f` was pushed to `origin/master` and fast-forwarded on Kreethya. No SQL was run for this change. Local syntax checks, view compilation, and 28 focused tests passed. Kreethya view compilation passed. A read-only live check of branch 002 Silver receipt 413 (`t_pawn_sums.id=820`) showed two historical letter charges of Rs. 125 each, Rs. 250 total; the history charge rows also show Rs. 125 for each letter, despite the newer Silver master version specifying Rs. 150. The old receipt's saved amounts and database rows were not changed by this deployment.
