# Release of Held Late Letters ("Cycle Review") — 2026-10-29

Never run Laravel migrations in this project (`php artisan migrate` and variants are forbidden). Schema and data safety rules were strictly adhered to.

## 1. System Identification & Background

### Core Pawning Lifecycle
The system manages gold pawning loans across multiple branches (`001` - Balangoda, `002` - Rathnapura):
- **Loan Issuance (`TPawnSum` / `TPawnDetails` / `TPawnTrans`)**: Customers pawn gold articles for capital advances under designated receipt types (`A`, `B`, `C`, `SILVER`). Each type specifies interest rates, penalty intervals, and validity terms.
- **Expiry Calculation (`ReceiptPenaltySchedule`)**: Non-Silver loans expire on `Final_date`. Silver loans dynamically determine expiry from transaction cycles and validity days (`validPeriod` / `period3`).
- **Late Letter Workflow (`RedeemLateLettersController` / `ArrearsLetterService`)**:
  - **Letter 1 (Tab 1)**: Due immediately upon loan expiry.
  - **Letter 2 (Tab 2)**: Due `letter_2_days` (default 21 days) after Letter 1 is printed.
  - **Letter 3 (Tab 3)**: Due `letter_3_days` (default 21 days) after Letter 2 is printed.
  - **Forfeit Reminder**: Due `forfeit_reminder_days` (default 21 days) after Letter 3 is printed.
- **Payment & Repawning Cycles (`PawningPartPaymentController` / `RepawningController`)**:
  - When part payment or repawning occurs, a new loan cycle starts with a new `RePawning_date` or payment transaction date, and a new forward `Final_date`.
  - In the modernized lifecycle architecture (`ReceiptLifecycleService::reactivate`), starting a new cycle clears current-cycle letter flags (`is_letter_1..3 = 0`, `letter_1..3_date = null`, `letter_pay_one..three = 0`) so the receipt can begin fresh for its new cycle upon expiry.

## 2. Root Cause of Held Letters ("Cycle review")

In legacy versions of the application (prior to October 2026):
1. When loans had part payments or repawnings, the legacy controller updated transaction and capital dates, but **failed to clear the previous cycle's letter flags and print dates** on `t_pawn_sums`.
2. When the modernized letter engine was deployed, it introduced `hasPriorCycleLetters()` as an integrity guard. If any `letter_X_date` was older than the receipt's current cycle start date (`RePawning_date`, `Pawn_Date`, `Receipt_Date`, or latest payment date), the receipt was flagged:
   - `letter_needs_review = true`
   - Forced into Tab 1 (1st Letter)
   - Marked with red badge **`Cycle review: Old letter dates precede the current payment cycle`**
   - Checkboxes and **[Print]** buttons were disabled to prevent issuing mismatched letters.
3. Over time, 405 active receipts across the database were trapped in this state. 292 of them were expired and stuck on Tab 1 with disabled actions.

## 3. Database Inspection Findings

An exhaustive scan across all active loans in `kreethya_sampath_prod_20261004` identified:
- **Total active receipts in database**: 9,238
- **Receipts with prior-cycle letters**: 405
  - **Branch 001 (Balangoda)**: 289
  - **Branch 002 (Rathnapura)**: 116
- **Expired receipts held in Tab 1 under "Cycle review"**: 292
  - **Branch 001**: 205 (including all 9 held receipts shown on Page 1 in the user's screenshot: 1120, 2074, 806, 1569, 1571, 34, 2726, 720, 894)
  - **Branch 002**: 87
- **Future/unexpired receipts held with prior letter dates**: 113 (84 in 001, 29 in 002)

## 4. Reconciliation & Release Execution

The release was executed via a dedicated, idempotent script matching the project's historical reconciliation patterns (`024` and `027`):
`database/manual/029_release_held_late_letters.php`

### Procedure per receipt:
1. **Archive Historical Letters**: Preserved all existing letter print dates (`letter_1_date`, `letter_2_date`, `letter_3_date`) and postage amounts as JSON snapshots in `receipt_lifecycle_events` (`PART_PAYMENT_ALLOCATION`). This ensures `ReceiptHistoryService` continues to display historical letter dates in customer transaction timelines without data loss.
2. **Reactivate Loan Cycle**: Invoked `ReceiptLifecycleService::reactivate()`, resetting current-cycle columns:
   - `is_letter_1 = 0, letter_1_date = null, letter_pay_one = 0`
   - `is_letter_2 = 0, letter_2_date = null, letter_pay_two = 0`
   - `is_letter_3 = 0, letter_3_date = null, letter_pay_three = 0`
   - `forfeit_queued_at = null`
3. **Execution Results**:
   - Total processed: **405**
   - Succeeded: **405**
   - Failed: **0**

## 5. Verification & Live Status

1. **Cycle Review Count**:
   - Branch 001: **0** held (previously 205)
   - Branch 002: **0** held (previously 87)
2. **Page 1 Verification (`http://127.0.0.1:8000/pawning_late_letters`)**:
   - Receipts 7828, 1120, 2074, 806, 1569, 1571, 34, 2726, 720, 894, etc., now all display active selection checkboxes and the green **[Print]** button.
   - All held notices and disabled states have been eliminated.
