# Branch 001 receipt 2147: carried interest correction (5 November 2026)

Never run Laravel migrations in this project.

The same receipt number also exists in Branch 002. That record is redeemed and is not part of this correction.

## Evidence

- On 1 September, payment 4075 received Rs. 2,000.00. Its transaction records Rs. 5,446.94 interest due and Rs. 3,446.94 unpaid interest carried. The cash account also records Rs. 2,000.00.
- Payment 4076, also on 1 September, received Rs. 1,000.00 as capital reduction. It incorrectly reset carried interest to zero. Its cash entry confirms Rs. 1,000.00.
- On 3 November, payment 5962 received Rs. 2,000.00. The allocation capitalized Rs. 1,771.28 current-period interest and Rs. 300.00 postage but omitted the Rs. 3,446.94 carried from September.
- Receipt Search consequently quoted Rs. 93,728.37 on 5 November while the uncorrected ledger displayed Rs. 102,592.25. The ledger also charged the first September payment's Rs. 5,446.94 interest a second time because both payments shared a date.

## Correction

`030_reconcile_receipt_2147_carried_interest.php` is dry-run by default. `--apply` is restricted by exact branch, receipt, row IDs, posted cash, amounts, dates, and latest transaction. It updates no cash entries.

- The first September payment's **paid** interest becomes Rs. 2,000.00; an allocation event retains its Rs. 5,446.94 interest due and Rs. 3,446.94 carried.
- The second September payment retains the Rs. 3,446.94 carried amount.
- The November payment capitalizes that carried amount together with its existing Rs. 1,771.28 unpaid current interest and Rs. 300.00 postage. Current capital becomes Rs. 96,217.60.
- The November event keeps Rs. 3,771.28 as the **new interest charge**. Adding carried interest to that field would charge it twice in the ledger. Separate event fields record the Rs. 3,446.94 carry and Rs. 7,218.22 total interest due at payment.
- A nonfinancial reconciliation event preserves the reason and original capital.

The code also matches historical payment summaries by transaction number before considering a date. A date fallback is used only if one summary exists for that date. The part-payment page now asks the cashier to select the new loan period rather than guessing it from unrelated amounts.

## Live execution and verification

Code commit `892c844a` was deployed to Kreethya. The script passed dry run and was then applied to Branch 001 receipt 2147 on 5 November. It made no cash-account changes and ran no migrations.

The read-only live check after correction showed current capital Rs. 96,217.60, accrued interest Rs. 962.18, and redemption service charge Rs. 30.00. Receipt Search redemption total was **Rs. 97,209.78**; ledger balance was **Rs. 97,179.78**. Their Rs. 30.00 difference is the current redemption service charge, which is added to the quote and is not yet a posted ledger movement. Cash entries remained Rs. 2,000.00, Rs. 1,000.00, and Rs. 2,000.00 for payments 4075, 4076, and 5962. The live login page responded HTTP 200 and production Blade compilation succeeded.
