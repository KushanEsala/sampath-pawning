<?php

namespace Tests\Unit;

use App\Models\TPawnSum;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptInterestPeriod;
use Tests\TestCase;

class RedeemSameDayPartPaymentTest extends TestCase
{
    /**
     * When a customer makes a part payment today (e.g. 2026-10-01),
     * Pawn_Date / RePawning_date is set to tomorrow (2026-10-02).
     * On the same day (2026-10-01), days must be 0 and interest must be 0.00.
     * Only tomorrow (2026-10-02) does the loan enter Day 1 and incur defined interest.
     */
    public function test_same_day_as_part_payment_has_zero_interest_and_tomorrow_incurs_defined_interest(): void
    {
        $paymentDate = '2026-10-01';
        $tomorrow = '2026-10-02';

        $receipt = new TPawnSum([
            'Receipt_Number' => 'P001',
            'Receipt_Type' => 'A',
            'Pawn_Amount' => 50000.0,
            'Pawn_Date' => $tomorrow,
            'RePawning_date' => $tomorrow,
            'period1' => 7,
            'period2' => 14,
            'period3' => 30,
            'validPeriod' => 90,
            'rate1' => 1.5,
            'rate2' => 2.0,
            'rate3' => 2.5,
            'service_charge' => 0.0,
            'interest_Paid' => 0.0,
            'BalanceInterest' => 0.0,
        ]);

        $calculator = new ReceiptFinancialCalculator();

        // 1. On payment date (today): 0 days elapsed, 0 interest owed
        $sameDayDays = ReceiptInterestPeriod::days($receipt, $paymentDate);
        $sameDayFinancial = $calculator->calculate($receipt, $paymentDate);

        $this->assertSame(0, $sameDayDays, 'Days on same day as part payment must be 0');
        $this->assertSame(0.0, $sameDayFinancial['interest'], 'Interest on same day as part payment must be 0.00');
        $this->assertSame(0.0, $sameDayFinancial['gross_interest'], 'Gross interest on same day must be 0.00');
        $this->assertSame(50000.0, $sameDayFinancial['redemption_total'], 'Redemption total on same day is principal only');

        // 2. On tomorrow (Day 1 of new cycle): 1 day elapsed, period 1 interest owed (1.5% of 50000 = 750)
        $tomorrowDays = ReceiptInterestPeriod::days($receipt, $tomorrow);
        $tomorrowFinancial = $calculator->calculate($receipt, $tomorrow);

        $this->assertSame(1, $tomorrowDays, 'Days on tomorrow must be 1');
        $expectedPeriod1Interest = (50000.0 / 100) * 1.5; // 750.00
        $this->assertSame($expectedPeriod1Interest, $tomorrowFinancial['interest'], 'Interest on tomorrow must be period 1 defined interest');
        $this->assertSame(50750.0, $tomorrowFinancial['redemption_total']);

        // 3. On day 7: still in period 1 (1.5%)
        $day7Date = '2026-10-08';
        $day7Financial = $calculator->calculate($receipt, $day7Date);
        $this->assertSame(7, $day7Financial['days']);
        $this->assertSame(750.0, $day7Financial['interest']);

        // 4. On day 8: moves to period 2 (2.0% of 50000 = 1000)
        $day8Date = '2026-10-09';
        $day8Financial = $calculator->calculate($receipt, $day8Date);
        $this->assertSame(8, $day8Financial['days']);
        $this->assertSame(1000.0, $day8Financial['interest']);
    }

    /**
     * If customer carried unpaid balance interest from part payment,
     * same day redeem shows only that carried interest, not a new cycle interest.
     */
    public function test_same_day_redeem_preserves_carried_balance_interest_without_new_interest(): void
    {
        $paymentDate = '2026-10-01';
        $tomorrow = '2026-10-02';

        $receipt = new TPawnSum([
            'Receipt_Number' => 'P002',
            'Receipt_Type' => 'A',
            'Pawn_Amount' => 40000.0,
            'Pawn_Date' => $tomorrow,
            'RePawning_date' => $tomorrow,
            'period1' => 7,
            'period2' => 14,
            'period3' => 30,
            'validPeriod' => 90,
            'rate1' => 1.5,
            'rate2' => 2.0,
            'rate3' => 2.5,
            'service_charge' => 0.0,
            'interest_Paid' => 0.0,
            'BalanceInterest' => 125.50, // Carried unpaid interest
        ]);

        $calculator = new ReceiptFinancialCalculator();

        // On same day: days = 0, interest = carried balance interest (125.50)
        $financial = $calculator->calculate($receipt, $paymentDate);
        $this->assertSame(0, $financial['days']);
        $this->assertSame(125.50, $financial['carried_interest']);
        $this->assertSame(125.50, $financial['interest'], 'Same day interest must equal carried balance interest');
        $this->assertSame(40125.50, $financial['redemption_total']);

        // On tomorrow: days = 1, interest = gross interest (600) + carried balance interest (125.50) = 725.50
        $tomorrowFinancial = $calculator->calculate($receipt, $tomorrow);
        $this->assertSame(1, $tomorrowFinancial['days']);
        $this->assertSame(725.50, $tomorrowFinancial['interest']);
    }

    /**
     * For SILVER receipts, same day as part payment also carries 0 new interest,
     * and tomorrow starts the new cycle.
     */
    public function test_silver_receipt_same_day_has_zero_interest_and_tomorrow_has_defined_interest(): void
    {
        $paymentDate = '2026-10-01';
        $tomorrow = '2026-10-02';

        $receipt = new TPawnSum([
            'Receipt_Number' => 'SILVER001',
            'Receipt_Type' => 'SILVER',
            'Pawn_Amount' => 30000.0,
            'Pawn_Date' => $tomorrow,
            'RePawning_date' => $tomorrow,
            'period1' => 30,
            'period2' => 60,
            'period3' => 30,
            'validPeriod' => 90,
            'rate1' => 2.0,
            'rate2' => 2.0,
            'rate3' => 2.0,
            'service_charge' => 0.0,
            'interest_Paid' => 0.0,
            'BalanceInterest' => 0.0,
        ]);

        $calculator = new ReceiptFinancialCalculator();

        // Same day
        $sameDayFinancial = $calculator->calculate($receipt, $paymentDate);
        $this->assertSame(0, $sameDayFinancial['days']);
        $this->assertSame(0.0, $sameDayFinancial['interest']);

        // Tomorrow (Day 1)
        $tomorrowFinancial = $calculator->calculate($receipt, $tomorrow);
        $this->assertSame(1, $tomorrowFinancial['days']);
        $this->assertSame(600.0, $tomorrowFinancial['interest'], 'Silver receipt gets month 1 flat rate on tomorrow');
    }
}
