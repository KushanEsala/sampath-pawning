# Arrears, Forfeit Reminder, Receipt Search and Payment Improvements

## Revision — 2026-09-05 (supersedes timing and automatic-forfeit-list movement below)

- All four receipt-type intervals default to 21 days: expiry to first letter, scheduled first to second, scheduled second to third, and actual third-letter issue to Forfeit Reminder. First interval may be 0 for expiry-day issuing.
- Reminder entry waits for its configured interval after the actual third letter. Overdue promises remain visible until manually reviewed and moved using the Forfeit checkbox/action.
- Forfeit Receipt List includes manually queued receipts and historical forfeited receipts. Rows expand/collapse to detailed articles, amounts, stock and history.
- Reminder and Forfeit reports use server-side pagination.
- Shared sidebar navigation now owns submenu toggling on all pages, with absolute asset URLs.
- See database/manual/PENALTY_INTERVALS_README.md and manual SQL 004/005 for setup, date examples and checks.

## 1. Purpose

Latest stock-transfer clarification and implementation notes: see [FORFEIT_STOCK_TRANSFER_UPDATES.md](FORFEIT_STOCK_TRANSFER_UPDATES.md). Stock Number is now confirmed as Invoice Number, replacing the earlier Ticket Number interpretation. The existing Forfeit Article List supports receipt-level selection, sale-stock transfer and audit printing.

This document defines the implementation plan for the requested arrears-letter, forfeit-reminder, receipt-history, customer-contact and payment-calculation improvements.

This document is both the approved implementation plan and the requirement traceability record. The application changes described here have now been implemented. The three new-table SQL scripts were created under `database/manual`, but no SQL script, database migration, or supplied database dump was executed by Codex.

## Implementation status

- Implemented: centralized receipt financial calculation and lifecycle/history services.
- Implemented: scheduled, idempotent first/second/third arrears-letter issuing and printing.
- Implemented: Forfeit Reminder promises and the separate broken-promise final-forfeit worklist.
- Implemented: Receipt Search, newest-first complete history, and printable history.
- Implemented: current customer contact lookup plus transactional active-snapshot synchronization and audit events.
- Implemented: telephone on new pawn receipt, expiry on Redeem/Part Payment, charge rows, Invoice Number as Stock Number (Ticket Number separate), and sidebar entries.
- Deployment prerequisite: manually review and run the numbered SQL scripts before using the new letter, promise, and lifecycle write operations.

## 2. Mandatory Database Rule

- Never run Laravel database migrations in this project.
- Never run `php artisan migrate` or any variation of it.
- The existing MySQL database and its data must be preserved.
- Every new MySQL table must have its own separately reviewed `CREATE TABLE` SQL script and its own rollback SQL script.
- Existing-table alterations must also be kept in separate, clearly named manual SQL scripts.
- A full database backup and a staging restore must be prepared before any manual SQL is executed.
- Manual SQL must only be executed after explicit approval.

## 3. Confirmed Terminology and Decisions

### 3.1 Stock Number

Latest clarification: Stock Number means `Invoice_Number`, not `Ticket_Number`. Display Invoice Number beside Receipt Number in the Forfeit Receipt List and in stock-number fields in search/history/transfer prints. Ticket Number remains a separate identifier and is available in expanded details.

### 3.2 Arrears report table

The word "table" in the arrears requirements means the visible report/worklist in the application. It does not mean a MySQL table.

No existing database columns must be removed. The visible arrears report must include the customer address and receipt date/time.

### 3.3 Receipt-type charges

Interest rates, valid days, service charge and postage charge already come from the existing Receipt Type setup at `/master_receipt`, backed by `recei__adds`. No second receipt-type or financial-charge setup will be created.

When a new pawn is created, the operator selects a Receipt Type. That selected type's rates, valid period, service charge and postage charge govern that pawn. The applicable values must be snapshotted into the pawn/transaction/letter event so later edits to `/master_receipt` do not change old receipts. The current pawn creation snapshot process must be completed to include postage where it is currently omitted.

Service charge and applicable letter/postage charges are treated as part of the interest/arrears amount payable by the customer and must appear in the calculation breakdown and payment history.

### 3.4 Payment and reactivation

For the initial implementation, the customer must pay the full currently required arrears amount before a receipt is removed from the arrears or Forfeit Reminder list and made active again. In this workflow, **full arrears amount** means all accrued interest plus the receipt-type service charge and all applicable letter/postage charges. It does not mean repayment of the outstanding pawn principal.

Reactivation must not introduce a new or custom expiry rule. The system must reuse its current default month-based expiry calculation and update `Final_date` through that existing mechanism. The next arrears cycle is evaluated from that default calculated date.

If the customer also pays the complete outstanding pawn principal, that is a full redemption and closes the pawn rather than reactivating it.

### 3.5 After the third letter

A receipt is not forfeited after the third letter. After the third letter is issued and payment has not been received, it enters the new **Forfeit Reminder List**. Actual forfeiture requires a later, explicit user action.

## 4. Confirmed End-to-End Lifecycle

```text
ACTIVE RECEIPT
    |
    | Expiry date reached
    v
1ST LETTER DUE
    |
    | 1st letter issued; receipt-type postage charge recorded once
    v
1ST LETTER SENT
    |
    | 21 days from the receipt expiry date
    v
2ND LETTER DUE
    |
    | 2nd letter issued; receipt-type postage charge recorded once
    v
2ND LETTER SENT
    |
    | 21 days from the scheduled 2nd-letter date
    v
3RD LETTER DUE
    |
    | 3rd letter issued; receipt-type postage charge recorded once
    v
FORFEIT REMINDER LIST
    |\
    | \ Full required arrears payment received
    |  \-------------------------------> ACTIVE, USING DEFAULT MONTH-BASED EXPIRY
    |
    | Promise date entered or extended before due date
    v
WAITING FOR PROMISED PAYMENT
    |\
    | \ Full required arrears payment received
    |  \-------------------------------> ACTIVE, USING DEFAULT MONTH-BASED EXPIRY
    |
    | Promise broken / no payment
    v
FORFEIT CANDIDATE LIST
    |
    | Authorized user selects checkbox and confirms forfeiture
    v
FORFEITED
```

## 5. Functional Requirements

### 5.1 Arrears Letter Worklist

The existing Redeem Late Letters operation will become a clear three-stage arrears worklist.

Required report columns:

- Selection checkbox
- Receipt Number
- Customer NIC
- Customer Name
- Customer Address
- Customer Telephone
- Receipt Type
- Receipt Date/Time (`Receipt_Date` plus the recorded creation time where available)
- Expiry Date
- Previous Letter Date, where applicable
- Current Letter Due Date
- Outstanding pawn amount
- Calculated interest
- Service charge
- Accumulated letter/postage charge
- Total arrears amount
- Print/Issue action

An explicit receipt search field and Search button must be provided. The required search key is Receipt Number.

Eligibility rules:

- First letter: due on the receipt expiry date.
- Second letter: due 21 calendar days after the receipt expiry date.
- Third letter: due 21 calendar days after the scheduled second-letter date, which is normally 42 calendar days after expiry.
- Printing a letter late does not move the scheduled due dates for later letters.
- Redeemed, forfeited or successfully reactivated receipts must not appear.
- Every query must be restricted to the authenticated user's branch.
- Dates must use the application timezone, Asia/Colombo.

### 5.2 Letter issuing and printing

- Displaying a print page must not itself silently mutate data through a GET request.
- A POST action must validate that the receipt is eligible for the requested letter.
- Issuing must record the letter date, letter number, charge and user exactly once.
- A unique database rule must prevent a duplicate charge for the same receipt, arrears cycle and letter number.
- Single and bulk letter issuing must use the same backend service and validation.
- Reprinting must reuse the original letter event and charge without adding a new charge.
- The printed letter must show principal/outstanding amount, interest, service charge, letter/postage charges and the total payable amount.

### 5.3 Forfeit Reminder List

This is a new workflow stage and a new sidebar operation. It is not the existing final forfeit operation.

Required columns:

- Receipt Number
- Customer Name
- Telephone Number
- Expiry Date
- Outstanding amount
- Total interest payable
- Service charge
- Total letter/postage charges
- Total amount required
- Promise remark text box
- Last promised-payment date calendar field
- Promise status
- Green View History button
- Save/Update action

Rules:

- A receipt enters this list after its third letter has been issued and payment remains outstanding.
- A user can enter the customer's payment promise and promised-payment date.
- Management can change or extend the promised date only before the current promised date arrives.
- The previous remark/date must remain in history when a promise is changed.
- Successful receipt of the full required arrears amount—accrued interest, service charge and all letter/postage charges—removes the receipt from this list.
- After successful payment, the receipt becomes active and its `Final_date` is recalculated by the existing default month-based expiry mechanism. No separate custom expiry rule is added.
- Payment of the outstanding principal as well is handled as redemption and closes the receipt instead of reactivating it.
- No payment by the promised date changes the receipt to a forfeit candidate; it still is not automatically forfeited.

### 5.4 Forfeit Candidate and final Forfeit List

- Broken or unpaid promises must appear in a controlled Forfeit Candidate list.
- Each row must have a checkbox for selection.
- Checking a row alone must not forfeit it.
- A separate Forfeit Selected action must display a confirmation showing the exact receipts/articles.
- Only authorized management users may confirm the operation.
- The existing final forfeit transaction must run inside one database transaction.
- It must prevent duplicate `t_forfeit_sums`, article, item and item-movement entries.
- The existing `/forfeitReceipt_List` report displays Stock Number (`Invoice_Number`) beside Receipt Number; Ticket Number stays separate in expanded details.
- A green View button must show the entire receipt history.

### 5.5 Receipt Search

A new top-level sidebar operation named **Receipt Search** must be created.

Search inputs:

- Receipt Number
- Branch selector for authorized cross-branch management users, if required

Search result:

- Original pawn summary
- Customer's current contact details
- Receipt Number, Stock Number (Invoice Number) and separate Ticket Number
- Article details
- Receipt date and expiry dates
- Original principal and current outstanding principal
- Pawn, repawn, part-payment and redeem transactions
- Interest breakdowns
- Service-charge rows
- Individual letter-charge rows
- All letter dates
- Customer feedback/comments
- Forfeit Reminder promises and changes
- Final forfeiture and stock-item information, if applicable
- Current lifecycle status

The complete history must be ordered newest first and must have a print-friendly view or PDF output.

### 5.6 Customer address and telephone updates

The `customers` table will be the canonical source for current customer address and telephone details.

Preferred implementation:

- Every current operational screen and report that displays a customer address or telephone should join/read the latest values from `customers` using the customer's stable identity.
- New transactions may retain snapshots needed for historical/audit purposes.
- Updating the customer must immediately affect every screen that displays current contact information.
- Active pawn summary and opening-pawn snapshot fields should also be synchronized transactionally for compatibility with existing code and print templates.
- Historical financial transaction values must not be rewritten. Where an old receipt/history screen is intended to show current contact details, it should obtain those contact fields from `customers`; the old snapshot remains available for audit.
- Old and new contact values must be recorded in an audit history.
- All update queries must be branch-scoped and executed in one database transaction.

This hybrid approach avoids risky mass updates across thousands of historical rows while ensuring the current address and telephone are displayed throughout the operational system.

### 5.7 New pawning receipt

- Add the customer's telephone number to the new pawn receipt printout.
- Use the current customer telephone at generation time.
- Preserve the generated transaction snapshot for audit/reprint consistency where required.

### 5.8 Redeem and part-payment screens

- Display the current expiry date on the Redeem screen.
- Display the current expiry date on the Part Payment screen.
- Use the same backend receipt calculation service on both screens.
- Do not independently recalculate financial values in multiple Blade JavaScript blocks.

### 5.9 Payment history

- Sort payment history newest first.
- Use transaction timestamp/transaction ID as a tie-breaker when multiple records share a date.
- Add an individual Service Charge row.
- Add an individual Letter Charge row for every issued letter.
- Show the letter number and issued date on letter-charge rows.
- Show payment, interest, capital, service, postage and remaining balance as a consistent ledger.
- Use the same history component in Redeem, Part Payment, Forfeit Reminder, Forfeit and Receipt Search.

## 6. Central Financial Calculation Service

All affected workflows must use one server-side calculator. Blade/JavaScript may display the calculation but must not be the authoritative source.

Inputs:

- Receipt type and its snapshotted configuration
- Outstanding principal
- Pawn/repawn date
- Calculation date
- Rate 1, Rate 2 and Rate 3 percentages and day ranges
- Valid days
- Previously paid interest
- Carried interest balance
- Receipt-type service charge
- Letter/postage events
- Discounts where permitted

Output breakdown:

```text
Calculated interest
+ Previous unpaid interest
+ Receipt-type service charge (once for the current settlement/arrears cycle)
+ 1st-letter postage charge, if issued
+ 2nd-letter postage charge, if issued
+ 3rd-letter postage charge, if issued
- Allowed arrears discount, if permitted
= Full arrears / total interest payable

Outstanding pawn principal
+ Full arrears / total interest payable
= Full redemption total
```

The service and letter/postage values are included in the interest/arrears amount shown as payable. They must remain separate line items for history and audit purposes. Service charge is included once, while each issued letter contributes one receipt-type postage charge.

The implementation must remove unexplained hard-coded values, including the current fixed `+ 25` in the letter total, unless that value is explicitly sourced from receipt-type setup.

## 7. Manual Database Change Design

The exact SQL will be finalized after verifying it against a staging restore of the supplied SQL dump. Letter timing rules (expiry, +21 days, +42 days) will be implemented in backend logic; no duplicate receipt-type setup table is required.

Each table below must be delivered in an individual SQL file, for example `001_create_arrears_letter_events.sql`, with a matching individual rollback file. Codex will not execute these scripts; the user will run approved scripts manually.

### 7.1 `arrears_letter_events`

Suggested fields:

- `id`
- `pawn_sum_id`
- `BC`
- Receipt Number snapshot
- Arrears cycle number
- Letter number
- Due date
- Issued date/time
- Postage charge snapshot
- Service-charge snapshot if charged at that event
- Issued by
- Reprint count or last reprinted timestamp
- Created/updated timestamps

Unique key:

- Pawn summary + arrears cycle + letter number

### 7.2 `forfeit_reminder_promises`

Suggested fields:

- `id`
- `pawn_sum_id`
- `BC`
- Arrears cycle number
- Promise date
- Remark
- Status: pending, extended, kept, broken, cancelled
- Reference to the previous promise when extended
- Created/updated user and timestamps

### 7.3 `receipt_lifecycle_events`

Suggested event types:

- Receipt activated
- Letter 1/2/3 issued
- Promise entered
- Promise extended
- Full arrears payment received
- Receipt reactivated
- Receipt redeemed
- Moved to forfeit candidate
- Receipt forfeited
- Customer contact updated

### 7.4 Compatibility fields

The current `is_letter_1`, `is_letter_2`, `is_letter_3`, letter dates and letter-payment fields may be maintained during the transition for compatibility. New workflow decisions must use the event/status service as the authoritative source.

If status projection fields are added to `t_pawn_sums`, suggested values are:

- `ACTIVE`
- `LETTER_1_DUE`
- `LETTER_1_SENT`
- `LETTER_2_DUE`
- `LETTER_2_SENT`
- `LETTER_3_DUE`
- `FORFEIT_REMINDER`
- `FORFEIT_CANDIDATE`
- `REDEEMED`
- `FORFEITED`

## 8. Application Components

Recommended backend components:

- `ReceiptFinancialCalculator`
- `ReceiptLifecycleService`
- `ArrearsLetterService`
- `ForfeitReminderService`
- `ReceiptHistoryService`
- Form Request classes for every write operation
- Policies/middleware for branch and management authorization

Recommended controllers/pages:

- Refactored Arrears Letter controller and report
- New Forfeit Reminder controller and page
- Refactored Forfeit Candidate/final Forfeit controller and page
- New Receipt Search controller and page
- Shared printable Receipt History template

## 9. Security and Data Integrity

- Move all operational routes under authentication middleware.
- Scope every receipt query by branch.
- Identify pawn records internally by `t_pawn_sums.id`; do not assume Receipt Number is globally unique.
- Validate all status transitions on the server.
- Use POST/PUT/PATCH for state changes and GET only for reading/printing existing data.
- Use database transactions for payment, reactivation, promise changes and forfeiture.
- Add idempotency/unique constraints for letters and final forfeiture.
- Do not trust totals submitted from the browser; recalculate them on the server.
- Record acting user, branch, date/time and before/after state for important changes.

## 10. Implementation Sequence

### Phase 1 — safety and baseline

1. Take code and database backups.
2. Restore the supplied database to an isolated staging database.
3. Document representative receipts for active, late, paid, redeemed and forfeited states.
4. Record existing calculation outputs for regression comparison.

### Phase 2 — calculation and history foundation

1. Implement the central receipt calculator.
2. Implement a normalized receipt-history read service.
3. Correct the model identity/branch-scoping strategy.
4. Add automated tests for monetary and date-boundary rules.

### Phase 3 — manual schema package

1. Prepare one forward `CREATE TABLE` SQL file for each new table.
2. Prepare one matching rollback SQL file for each new table.
3. Keep every existing-table `ALTER TABLE` operation in its own manual SQL file.
4. Prepare a separate legacy-data classification/backfill SQL file where required.
5. Prepare read-only verification queries.
6. Review every script individually; Codex must not execute them, and the user will run approved scripts manually.

### Phase 4 — arrears letters

1. Rebuild eligibility queries.
2. Add Receipt/Stock search controls.
3. Update the report columns.
4. Replace GET mutations with idempotent issue actions.
5. Update single/bulk printing and charge breakdowns.

### Phase 5 — Forfeit Reminder and promises

1. Add the new sidebar operation.
2. Implement promise entry and controlled extension.
3. Add payment/reactivation handling.
4. Implement broken-promise transition to Forfeit Candidate.

### Phase 6 — final forfeiture

1. Add the candidate checkbox workflow.
2. Add confirmation and authorization.
3. Make existing forfeiture and stock creation idempotent.
4. Add full-history View action and Stock Number (Invoice Number), keeping Ticket Number separate.

### Phase 7 — Receipt Search and print

1. Add Receipt Search to the sidebar.
2. Aggregate all receipt-related data into one timeline.
3. Sort newest first.
4. Add printable full history.

### Phase 8 — customer and remaining UI changes

1. Centralize current customer contact retrieval.
2. Synchronize active compatibility snapshots on customer update.
3. Add customer telephone to the new pawn receipt.
4. Add expiry dates to Redeem and Part Payment.
5. Use the shared payment-history component everywhere.

## 11. Acceptance Tests

### Letter dates

- A receipt appears for the first letter on its expiry date.
- It does not appear for the second letter before 21 days from the receipt expiry date.
- It appears for the second letter on exactly expiry date + 21 calendar days.
- It appears for the third letter on exactly the scheduled second-letter date + 21 calendar days (normally expiry date + 42 days).
- Late printing/reprinting does not shift these scheduled eligibility dates.
- Reprinting never duplicates a letter charge.

### Payments and reactivation

- An insufficient payment does not reactivate the receipt.
- Payment of the full required arrears amount (interest + service + applicable letter/postage charges) removes it from the arrears/reminder list.
- Reactivation uses the existing default month-based mechanism to recalculate `Final_date`.
- That default calculated date is used for the next arrears cycle; no new/custom expiry rule is introduced.
- Payment including the full outstanding principal is a redemption and closes the receipt instead of reactivating it.

### Forfeit Reminder

- Third-letter completion moves the unpaid receipt to Forfeit Reminder, not final Forfeit.
- Promise remarks and dates are saved.
- Promise dates can be extended before the due date.
- Previous promises remain visible in history.
- Broken promises move to Forfeit Candidate.
- Nothing is automatically forfeited.

### Final forfeiture

- Checkbox selection requires separate confirmation.
- Unauthorized users cannot forfeit.
- Repeating the request cannot create duplicate stock/items.
- The existing `/forfeitReceipt_List` displays Invoice Number as Stock Number beside Receipt Number.
- Green View displays complete history.

### Calculation and history

- Rates, service charge and postage come from the applicable receipt-type snapshot.
- Service and each letter charge appear as separate history rows.
- All screens and prints show identical totals.
- History is newest first.
- No unexplained hard-coded charges remain.

### Customer updates

- Updating address/telephone changes every current operational display.
- Active receipt compatibility snapshots are synchronized.
- Old and new contact values are auditable.
- No unrelated financial history is modified.

### Branch isolation

- A user cannot search, view, update, print or forfeit another branch's receipt.
- Receipt Number collisions between branches do not return the wrong record.

## 12. Requirement Traceability

| Original requirement | Planned implementation |
|---|---|
| 1 | Arrears worklist with explicit receipt search |
| 2 | Report retains and displays address and receipt date/time; no DB columns removed |
| 3 | 1st letter on expiry, 2nd at expiry + 21 days, and 3rd at the scheduled 2nd-letter date + 21 days |
| 4 | Letter shows postal charge plus arrears breakdown and total |
| 5 | Full required payment removes receipt from active arrears lists |
| 6 | New Forfeit Reminder List with contact, amounts, remarks and promise date |
| 7 | Full arrears payment (interest + service + letter/postage charges) reactivates the receipt and reuses the existing default month-based `Final_date` calculation; no custom expiry rule |
| 8 | Broken promises move to checkbox-controlled Forfeit Candidate list |
| 9 | Green View button shows the complete receipt history |
| 10 | Promise date can be extended before it arrives, with audit history |
| 11 | Display `Invoice_Number` as Stock Number beside Receipt Number in the existing `/forfeitReceipt_List` report; Ticket Number is separate |
| 12 | New Receipt Search operation with complete lifecycle history |
| 13 | Customer telephone displayed on the new pawning receipt |
| 14 | Customer master becomes canonical; active snapshots synchronized |
| 15 | Complete receipt history has print/PDF output |
| 16 | Expiry date displayed in Redeem and Part Payment |
| 17 | Separate Service Charge and Letter Charge history rows |
| 18 | Receipt-type service/postage included consistently in arrears payable calculation |
| 19 | All payment history displayed newest first |

## 13. Out of Scope Until Explicitly Authorized

- Executing any SQL against the current database
- Importing the supplied dump
- Running any Laravel migration command
- Changing existing production data
- Automatically forfeiting receipts
- Removing existing database columns
