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
   - Updated `App\Models\User::isAdmin()` to be case-insensitive to safely match both 'Admin' and 'admin'.

3. **Verification**:
   - Automated test suite `ReceiptNumberVisibilityTest` with 9 assertions covering Admin vs Cashier behavior across searches, forfeit lists, and reports.
   - Full test suite passed (137 passed, 0 failed).
