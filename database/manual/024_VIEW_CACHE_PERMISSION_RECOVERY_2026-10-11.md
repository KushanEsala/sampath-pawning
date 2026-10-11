# Kreethya view-cache permission recovery — 2026-10-11

No SQL, seeder, database data update, or Laravel migration was run.

## Incident and cause

After code commit `ca74c9b9` was deployed, production requests to redemption, repawning, and receipt-search AJAX returned HTTP 500. The 2026-10-11 Laravel log repeatedly recorded `file_put_contents(.../storage/framework/views/*.php): Permission denied`. The previous deployment ran `php artisan view:cache` as `root`, creating 137 compiled Blade files owned by `root:root` with mode `644`. The web application could not rewrite a compiled file after the source view changed. The payment-search guard translated that AJAX failure into “Receipt search failed. Please search again.” This was not a receipt lookup or database failure.

## Recovery performed on Kreethya

- Confirmed `/home/kreethya/public_html/sampath/storage/framework/views` resolves to the intended application directory and is owned by `kreethya:kreethya`.
- Changed ownership only for root-owned regular files directly inside that compiled-view directory to `kreethya:kreethya`.
- Rebuilt Blade views as the application account: `cd /home/kreethya/public_html/sampath && runuser -u kreethya -- php artisan view:cache`.
- Confirmed all 137 compiled view files are now `kreethya:kreethya` with mode `644`; no root-owned regular files remain under `storage/framework` or `bootstrap/cache`.

For future deployments, run all cache-generation Artisan commands as `kreethya`, not `root`. Never run Laravel migrations in this project.

## Read-only production verification

- Authenticated Laravel GET checks, executed as the application account, returned HTTP 200 for 50 active sidebar routes under a Cashier account and 51 under an Admin account. This included user management, company/branch setup, all Master and Accounts entries, pawning, part payment, redemption, repawning, letters, Receipt Search, forfeit pages, vouchers, sales, and reports. The Admin-only blocked-receipts page returned 200 for Admin.
- A Branch 001 active receipt returned HTTP 200 from the part-payment, redemption, and repawning receipt-search AJAX endpoints. Its stock number was absent from each Cashier response. Receipt Search and history print returned HTTP 200 and did not display that stock number when queried by receipt number. A search using the exact customer-supplied stock number found the receipt and displayed that supplied number.
- A Branch 002 Cashier also received HTTP 200 on the part-payment, redemption, repawning, and Receipt Search pages and on an active-receipt part-payment lookup.
- Payment save code still derives Ticket/Stock identifiers from the locked receipt record; redacting them in the cashier's page does not discard them from saved transactions.

The checks were read-only GETs. They did not submit a payment, redeem or repawn a receipt, print a letter, or modify customer/receipt data. Separate legacy non-fatal PHP warnings were observed in the Petty Cash page and Part Payment History report; neither page returned a 500 during this audit.
