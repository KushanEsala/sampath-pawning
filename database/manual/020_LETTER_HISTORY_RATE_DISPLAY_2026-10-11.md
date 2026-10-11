# Letter/history postage display correction — 2026-10-11

Never run Laravel migrations. This change requires no schema or data update.

The per-letter postage rate comes from the receipt type/version applicable to the receipt's pawn cycle, unless the receipt already has a positive saved letter charge or postage snapshot. The current master receipt-type rate must not overwrite a historical saved amount. `t_pawn_trans.Postage_charge` is a cumulative/legacy transaction value and must not be divided by a letter count to infer a historical per-letter rate.

The shared calculator now supplies the per-letter breakdown and total used by the late-letter report, payment totals, receipt-history fallback rows, and letter printout. The letter printout no longer shows zero in its individual letter columns while showing a positive postage total.

The 629 genuine zero-charge events repaired by `019` remain recorded in the database. The 18 events skipped by that repair remain untouched: 13 have a later pawn/payment cycle and five belong to closed receipts. Review their source evidence before changing historical amounts; do not fabricate letter events or rates. This code change does not rewrite any historical row.

Deployment verification: run focused tests and compile views; on Kreethya, compare old Silver receipts with zero saved charge against their historical type version, and verify new receipt charges against the current version. Record the deployment commit and checks below after execution.
