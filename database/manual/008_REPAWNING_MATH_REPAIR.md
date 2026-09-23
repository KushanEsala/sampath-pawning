# 008 — Legacy repawning math repair

This is a Laravel service/command data repair, not a migration and not a schema
change. It updates incorrect values already stored in existing rows; it does not
insert balance-adjustment transactions or delete financial history.
It replays the **recorded** `Paid_Interest`, service, stamp, postal and discount
amounts. It does not reconstruct or rewrite those posted amounts using today's
receipt-type rates. If a posted amount itself is suspect, review the read-only
`012_repawning_interest_review.sql` output, original type snapshots and older
backups before any separate correction. Re-running 008 will not fix a wrong
`Paid_Interest` value.

## Why it exists

The old repawning/part-payment flow sometimes carried the original pawn
principal into the running balance a second time. Later rows inherited that
inflated amount. For example, receipt 001/4520 stored Rs. 29,336.16 while
chronological replay of its recorded principal payments, repawn cash, interest
and service charges produces Rs. 15,336.16.

The database also contains reused repawning keys. Because those rows can
represent either an accidental double submit or two real cash disbursements,
the command refuses to guess and lists those receipts for manual review.

## Commands

Dry run one receipt:

```text
php artisan repawning:repair-math --branch=001 --receipt=4520
```

Apply one reviewed receipt:

```text
php artisan repawning:repair-math --branch=001 --receipt=4520 --apply
```

Dry run all branches:

```text
php artisan repawning:repair-math
```

Apply all unambiguous receipts after reviewing the dry run:

```text
php artisan repawning:repair-math --apply
```

Apply mode writes a private JSON before/after audit and generated rollback SQL
under `storage/app/private/repawning-repair/` before opening update
transactions. Each receipt is locked and repaired atomically. If any inspected
value changed after the dry run, that receipt transaction stops instead of
overwriting concurrent work.

## Fields corrected in place

- `t_pawn_trans.Pawn_Amount` opening principal for part payment/repawning.
- `t_pawn_trans.trans_amount` pre-repawn principal plus applicable recorded
  interest/service/stamp/postal charges less discount.
- `t_pawn_payments` opening and remaining principal snapshots.
- `t_repawning_sums.Payable_Pawn_Amount` and `Redeem_total`.
- Active `t_pawn_sums.Pawn_Amount`, `RePawning_amount`, latest repawn cash and
  carried-interest fields.

No Laravel migration is used. No new table or column is required.

## Execution record — 2026-09-23

The local `smartom_sampath` database was repaired only after the user explicitly
requested the data correction.

- Receipt 001/4520 was applied first: 13 rows corrected and verified.
- Full audit: 3,846 receipts with repawning history.
- Full apply: 3,815 additional receipts and 11,941 rows corrected.
- Final dry-run verification: zero remaining automatic row updates.
- Twelve ambiguous receipts were deliberately left unchanged: 001/47, 001/186,
  001/2226, 001/2744, 001/3647, 001/4648, 001/4919, 001/5298, 001/9810,
  002/4272, 002/4313 and 002/4654.
- Private audit/rollback pairs were written under
  `storage/app/private/repawning-repair/` with identifiers
  `20260923_012514_ad01ec` and `20260923_012650_cdc235`.

No migration was run and no adjustment transaction was inserted.

## Prevention for new repawning records

The repawning screen now obtains its next number from `t_repawning_sums` rather
than the unrelated redemption table. Saving locks the active receipt and
rejects an already-used branch repawning number. The browser also disables the
submit button after a valid submission starts. These checks prevent the
double-submit/reused-number pattern without requiring a new database index.
