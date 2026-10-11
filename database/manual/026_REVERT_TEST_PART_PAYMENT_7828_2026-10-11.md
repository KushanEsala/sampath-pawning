# Receipt 001/7828: Rs. 10 test part-payment reversal

On 2026-10-11, the user asked to revert their Rs. 10 test part payment on Balangoda (branch `001`) receipt `7828`. No Laravel migrations were run.

## Evidence and scope

- The only payment for this receipt was payment number `5255`, entered at `2026-10-11 23:09:42` local time. It recorded Rs. 10 paid interest and no paid capital.
- The part-payment code capitalized Rs. 30,620.72 unpaid interest, changing capital from Rs. 136,191.00 to Rs. 166,811.72 and the receipt's dates.
- The receipt had no later pawn transaction, payment, lifecycle event, letter event, or forfeit promise. It remained active and had no printed letters.
- Pre-payment values came from `/home/kreethya/db-backups/letter_postage_print_time_pre_20261011_225736_f0e1e9.sql.gz`, created before this test payment. The row in that backup had already received the latest receipt-type terms. No historical rate was calculated or invented.

## Applied on Kreethya

`026_revert_test_part_payment_7828.php` was run in dry-run mode and then with `--apply` as the `kreethya` user. The script guarded the exact receipt, payment values, related row IDs, and absence of later activity. It saved a private copy of every affected current row at:

`/home/kreethya/public_html/sampath/storage/app/manual-reversals/receipt_001_7828_before_20261011_232152.json`

In one database transaction, it restored receipt `t_pawn_sums.id=13322` capital, dates, payment fields, and `updated_at` to the pre-payment values; restored `t_pawn_details.id=14368` timestamp; and removed only these test records:

| Table | Exact test record |
| --- | --- |
| `t_pawn_payments` | Branch `001`, receipt `7828`, payment `5255`, created `2026-10-11 23:09:42` |
| `t_pawn_trans` | `id=43402` |
| `t_account_trans` | `id=43840` |
| `receipt_lifecycle_events` | `id=1957` |

The generated shared branch PDF was still the one written at the test-payment time. Its SHA-256 was `6dfbe8ed44cddf32c8793ab431b84f69374255c61c9f4479f97cd4f8fbf04602`. It was moved, not destroyed, from `public/assets/pdf/Redeem_receipt001.pdf` to the private 0600 file:

`/home/kreethya/public_html/sampath/storage/app/manual-reversals/receipt_001_7828_test_part_payment_20261011.pdf`

Downloaded or printed copies outside the server cannot be recalled. The next legitimate branch 001 part payment will regenerate the shared PDF path.

## Independent verification

The receipt now has `Pawn_Amount=RePawning_amount=136191.00`, `Pawn_Date=2025-10-20`, `Final_date=2025-12-20`, and `Valid_Period=2`. Payment count for branch `001` receipt `7828` is zero, and the test transaction, cash-account entry, and allocation event IDs are absent. Article `updated_at` is again `2026-06-28 06:21:40`. The script is idempotent and will refuse to change the receipt if a later transaction appears.
