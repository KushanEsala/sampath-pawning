# Part-payment interest capitalization — 2026-10-07

This release changes **new** part payments only. It does not recalculate or rewrite existing production payments. No Laravel migration, SQL schema change, seeder, or SQL data update is required.

For all receipt types, a payment below the accrued interest is allocated to interest first. Unpaid interest is added to capital for the next cycle. Example: old capital Rs. 5,000.00 + interest due Rs. 200.00 − cash interest paid Rs. 150.00 = new capital Rs. 5,050.00. Unpaid postage or other non-interest charges are not added to capital. The next cycle starts after the payment date and does not also carry this capitalized interest in `BalanceInterest`.

The payment writes its actual cash amount and paid-interest amount to the existing payment and transaction tables. When interest is capitalized or a discount is given, an explicit `PART_PAYMENT_ALLOCATION` event is stored in the existing `receipt_lifecycle_events` table with the transaction ID and the exact allocation. Receipt Search, payment-page ledgers, and history print use that event to show the full interest charge, actual cash credit, amount added to capital, and reconciled running balance. Old transactions without this event continue using their recorded paid-interest values; no historical allocation is invented.

The existing requirement for **full arrears payment to reactivate a receipt with a printed letter** remains separate from this calculation. If that policy changes, it must be designed alongside letter-stage/expiry handling before accepting smaller payments on lettered receipts.

## Deployment checks

1. Deploy the code. No SQL file needs to be applied.
2. On a non-lettered test receipt with capital Rs. 5,000 and interest Rs. 200, take a Rs. 150 part payment. Confirm new capital Rs. 5,050, no carried interest, and no same-day interest charged again.
3. Confirm Receipt Search, each payment-page history tab, history print, and the part-payment print show the Rs. 200 interest charge, Rs. 150 customer payment, Rs. 50 interest added to capital, and Rs. 5,050 balance.
4. Verify an existing old part payment still uses its original recorded figures.
