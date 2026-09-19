# September 2026 updates

No Laravel migrations or database SQL were executed. Stock-transfer changes reuse the existing schema, including `receipt_lifecycle_events` from `database/manual/003_create_receipt_lifecycle_events.sql`. The subsequent closed-receipt protection includes optional manual article-status repair SQL, described below.

1. Stock Number is Invoice Number. Ticket Number remains separate. Forfeit Receipt List, Receipt Search, receipt-history print and the new stock-transfer report use the corrected mapping.
2. Receipt history identifies the operator recorded on each transaction, service/letter charge, promise, remark, forfeiture and lifecycle event. Name/role/branch are resolved from that recorded account where available. Missing historical operators are labelled Not recorded, never replaced with the current viewer. New stock transfers preserve the operator profile in their audit snapshot.
3. Forfeit Reminder List highlights missed promises (promise date before today) with a pale red row and Promise overdue badge. Promises due today/future and receipts without a promise are not highlighted.
4. Forfeit Article List is paginated by receipt, with expandable article details. Select receipts and click Forfeit selected & print to move all available articles into sale stock. Source items are marked IntoItem=1, not deleted. Receipt and payment history remain unchanged. Branch/status checks and transactional locks protect duplicate/stale submissions. The entire batch rolls back if a selection is invalid. Selection is limited to the displayed page and resets after navigation.
5. The stock-transfer print is persisted through lifecycle audit data and can be reprinted from Receipt Search history. It contains receipt/stock/ticket numbers, customer, original forfeiture date, actual selected/transfer date, operator, all transferred articles and financial figures recorded at forfeiture. Outstanding capital/interest and previous payments are labelled separately; transferring stock does not record a payment. Missing legacy figures show Not recorded.
6. Part-payment and redeem printouts show inclusive elapsed days beside Interest. Normal receipts use the same period calculated for interest, captured before part payment changes the period start. Opening receipt redemption uses its stored period dates. No interest rates, totals or expiry rules were changed for this display update.

Verification: focused tests use in-memory models and intercepted MySQL queries/writes, not the project database. Live browser/database verification is separate. Browsers may block the automatic print tab; the completion message provides a manual Print selected receipts link.

## Closed-receipt payment protection

Redeemed and forfeited receipts are excluded from receipt/ticket/invoice payment lookups and from payment-screen article/history requests. Part-payment ticket/invoice lookups now load its own active-pawn form rather than the redemption form. Normal/opening/old-system redemption and normal part-payment submissions recheck branch and both status flags under a transaction lock before saving any payment. Unsupported receipt types are rejected.

The payment-screen guard clears previous receipt/article/history panels when search input changes or no active receipt is found, ignores responses to old search input and disables submission until an active result has loaded. Closed receipts remain accessible through Receipt Search and general historical reports.

Article status is synchronized with its parent for new pawning, redemption, partial payment and forfeiture. Part-paid articles remain active; redeemed articles are marked redeemed; forfeited articles remain forfeited even after sale-stock transfer. This does not reset inventory IntoItem/SaleIsItem flags or delete history.

Optional manual repair: `database/manual/006_sync_pawn_article_status.sql` adds `t_pawn_details.isForfeit` only if missing and synchronizes existing normal/opening article flags with their branch-specific parents. Back up first and run it yourself if required. The application also works without that optional column by using parent receipt status for payment eligibility. No repair SQL or Laravel migration was executed.
