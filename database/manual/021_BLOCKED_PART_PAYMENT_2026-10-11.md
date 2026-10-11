# Blocked receipts cannot receive part payments — 2026-10-11

No schema or data correction is required. Do not run Laravel migrations.

Before this change, redemption and repawning checked `t_pawn_sums.is_blocked`, but part-payment search and submission did not. A cashier could load and save a part payment for a blocked receipt.

Receipt, ticket, and invoice part-payment lookups now return a `blocked` status and show the administrator-unblock message instead of a payment form. Submission independently checks the flag after locking the branch-scoped, open receipt and rolls back before writing anything if it is blocked. General receipt history remains viewable; this change only prevents a payment transaction.

Verification: `ReceiptPaymentStatusTest` covers all three blocked lookups and blocked submission with no inserts/updates. `RepawningBlockEligibilityTest` confirms the pre-existing repawn protection remains. No existing receipt or customer row was modified by this release.
