# Part payment below letter arrears — 2026-10-11

Never run Laravel migrations. This release changes new part payments only; it requires no SQL schema or data update and does not rewrite historical payments. It supersedes the full-arrears-to-reactivate rule and the separate unpaid-postage carry described in earlier notes.

## Rule for every receipt type and branch

A positive part payment below accrued interest plus printed-letter postage is accepted. Cash pays due interest first, then postage/other due charges, then capital. Any unpaid interest **and** unpaid postage/charges are added to the new capital exactly once. `BalanceInterest` is zero for this new cycle so the same charges cannot be added again. The payment starts the usual new date/expiry cycle. When letters existed, their current-cycle flags and charges are cleared after the allocation, so the receipt returns to active status and can enter first-letter flow again only after its new expiry. The historic letter events and their charges remain in history.

For example, on 2026-10-11 Rathnapura `BC=002` receipt 5542 had capital Rs. 35,308.00, accrued interest Rs. 7,502.95, and two letter charges totalling Rs. 250.00. A **hypothetical** Rs. 150.00 part payment allocates Rs. 150.00 to interest, adds Rs. 7,352.95 unpaid interest and Rs. 250.00 unpaid postage to capital, and results in new capital Rs. 42,910.95. No actual payment was posted during this verification. Balangoda `BC=001` also has receipt number 5542; all payment queries remain branch-scoped.

The payment allocation lifecycle event now records `capitalized_interest` and `capitalized_charges` separately, tied to the transaction ID. Before clearing current letter flags, it also snapshots the already-saved print dates and postage amounts. Receipt Search, payment-page history, and history print can therefore continue showing a legacy printed letter with no `arrears_letter_events` row, using its actual saved date and amount; matching event-backed letters are not duplicated. No backdated letter event is created. The part-payment print shows the charge portion added to capital. The browser preview uses the same formula as the server.

Verification: run focused calculator, ledger, arrears, and letter-schedule tests; compile Blade views; confirm the live code version and a read-only calculation for branch 002 receipt 5542. Do not make a test payment on production data.

## Deployment record

Code commit `84b070c1` was pushed to `origin/master` and fast-forwarded on Kreethya. No SQL, seeder, or Laravel migration was run. Local focused tests: **45 passed**; PHP syntax checks and Blade view compilation passed. Kreethya view compilation passed. A read-only live invocation of the deployed calculator for the receipt-5542 example returned charges due Rs. 7,752.95, hypothetical cash Rs. 150.00, new capital Rs. 42,910.95, interest capitalized Rs. 7,352.95, postage capitalized Rs. 250.00, and carried charges zero. The actual production receipt still had capital Rs. 35,308.00 and both letter flags set, confirming no test payment or data correction was made during deployment.
