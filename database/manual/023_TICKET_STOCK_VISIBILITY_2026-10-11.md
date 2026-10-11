# Restrict Ticket and Stock Number Visibility to Admin — 2026-10-11

No schema or database data correction is required. Do not run Laravel migrations.

## Summary of Changes
1. **Pawning Part Payment 500 Recovery**:
   - Diagnosed 500 error on `https://sampath.kreethya.com/pawningPartPayment`.
   - Cleared compiled view cache and application cache.
   - Restored proper permissions and ownership on `storage/logs` so Monolog and PHP-FPM can append to daily log files without crashing.
   - Cleared and reloaded PHP-FPM master process opcode cache.
   - Fixed missing variable default `$customerActive` in `resources/views/pawning_search_customer.blade.php`.
   - Verified that all users across all branches (Balangoda, Rathnapura, Admin, Cashier) load `/pawningPartPayment` with HTTP 200 OK.

2. **Ticket and Stock Number Visibility Policy**:
   - Created `App\Services\ReceiptNumberVisibility`:
     - Checks whether authenticated user is Admin (`auth()->user()->isAdmin()`).
     - Admin users can view Ticket Number and Stock Number (Invoice Number) on all existing receipt pages and reports.
     - Non-admin users (Cashiers) cannot view Ticket Number or Stock Number on existing-receipt browsing pages unless the customer explicitly provided that identifier in the search (e.g. searching specifically by ticket reveals that ticket; searching by stock reveals that stock; searching by Receipt Number or NIC hides both).
     - New pawn receipts (`pawnReceipt.blade.php`) remain exempt as cashiers generate them upon receipt creation.
   - Applied visibility restrictions to:
     - `resources/views/receiptSearch.blade.php`: hides Ticket No and Stock No from NIC search table for non-admins; in full receipt details, displays Ticket or Stock number only if user is Admin or if that specific number was provided by the customer.
     - `resources/views/receiptHistoryPrint.blade.php`: prints Ticket or Stock number only if user is Admin or if customer searched by that number.
     - `app/Services/ReceiptHistoryService.php`: includes Stock # in ledger descriptions only when user is Admin or when Stock number was provided.
     - `resources/views/forfeitReceipt_List.blade.php`: hides Stock and Ticket numbers in forfeit details and table rows from non-admin users.
     - `resources/views/forfeit_article_receipt.blade.php`: hides Stock and Ticket numbers from non-admin users.
     - `resources/views/PartpaymentHistortyReport.blade.php`: hides Invoice (Stock) No from non-admin users.
     - `resources/views/repawningHistortyReport.blade.php`: hides Invoice (Stock) No from non-admin users.
     - `resources/views/redeem_report.blade.php`: shows Stock No (Invoice Number) for Admin users only.
   - Updated `App\Models\User::isAdmin()` to be case-insensitive to safely match both 'Admin' and 'admin'.
   - Existing receipt payment screens now leave Ticket and Stock search fields blank unless that exact identifier was used for lookup. Cashiers can enter a customer-provided Stock/Invoice Number to search; the number is no longer automatically exposed after a Receipt Number lookup. AJAX detail fragments omit both identifiers unless separately authorized by the lookup.
   - Redemption saves Ticket and Stock identifiers from the locked database receipt, not client-side search fields. Repawning no longer requires those search fields in the request; it already writes identifiers from the locked receipt. Redeem prints hide Stock Number from non-admin users.

3. **Verification**:
   - Automated test suite `ReceiptNumberVisibilityTest` with 10 assertions covering Admin vs Cashier behavior across searches, forfeit lists, and reports (including redeem report).
   - Full test suite passed (138 passed, 0 failed).

## Follow-up check on the part-payment 500

The latest live source includes commit `fe8f9442`, which safely formats a missing previous payment number as `0000`. On 2026-10-11, the live controller and compiled part-payment page rendered successfully using an existing authenticated account. The current daily Laravel log showed no part-payment exception after that commit; later entries were from CLI diagnostic commands. No database or schema changes were made for this follow-up.

## All history reports and receipt searches follow-up

- Date-range receipt, stock, opening-pawn and article reports now display Receipt Number to cashiers instead of exposing Invoice/Stock Number under misleading Ticket or Receipt headings. Admin retains Stock Number.
- Stock Number Search displays the matching Stock Number because it was supplied explicitly, with the true Receipt Number identified separately.
- Part Payment and Repawning history reports now accept an exact Receipt, Ticket or Stock lookup. Date-only reports hide Ticket/Stock from cashiers; exact lookup repeats only the matching customer-supplied identifier. Admin sees both in those reports. On-screen exports/prints use the same conditional table.
- Forfeit Receipt List and Forfeit Article List support the same exact lookup and carry visibility into the receipt-history print link. Forfeit article transfer print hides Ticket/Stock for non-admin users, since selecting a receipt does not prove the customer supplied either number.
- Old-system redemption lookup fragments no longer auto-reveal Ticket/Stock after a receipt lookup. Their save uses identifiers from the locked receipt rather than the search fields. The regular forfeit-processing lookup follows the same rule.
- Receipt-search history, payment-history modal, and history print carry the exact customer-supplied ticket or stock search context; ledger rows show only that matching identifier to cashiers. Unsearched history and receipt-number/NIC searches show neither identifier. Admin still sees both.
- Existing redemption report and redemption print paths were audited and already restrict Stock Number to Admin. The blocked-receipts management page is Admin-only. New pawn receipt prints remain exempt as requested.
- Verification: 142 local tests passed; Blade view compilation and PHP syntax checks passed. No schema or data changes were made.
- No schema or data changes are required, and no Laravel migrations are to be run.
