<?php

namespace Tests\Unit;

use App\Models\Recei_Add;
use App\Models\TPawnSum;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptHistoryService;
use App\Services\SilverInterest;
use Carbon\Carbon;
use Tests\TestCase;

class SilverInterestTest extends TestCase
{
    public function test_first_thirty_days_have_one_period_charge_then_prorate_daily(): void
    {
        $this->assertEqualsWithDelta(600.00, SilverInterest::amount(10000, 6, 1), 0.001);
        $this->assertEqualsWithDelta(600.00, SilverInterest::amount(10000, 6, 30), 0.001);
        $this->assertEqualsWithDelta(620.00, SilverInterest::amount(10000, 6, 31), 0.001);
        $this->assertEqualsWithDelta(900.00, SilverInterest::amount(10000, 6, 45), 0.001);
        $this->assertEqualsWithDelta(0.00, SilverInterest::amount(10000, 6, 0), 0.001);
    }

    public function test_silver_validity_uses_master_days_or_thirty_day_period(): void
    {
        $type = new Recei_Add();
        $type->forceFill(['validPeriod' => 0, 'period3' => 30]);
        $this->assertSame(30, SilverInterest::validDays($type));
        $type->period3 = 0;
        $this->assertSame(30, SilverInterest::validDays($type));
        $type->validPeriod = 45;
        $this->assertSame(45, SilverInterest::validDays($type));
    }

    public function test_receipt_financial_calculation_uses_silver_daily_interest_and_paid_interest(): void
    {
        $receipt = new TPawnSum();
        $receipt->forceFill([
            'Receipt_Type' => 'SILVER', 'receiptname' => 'SILVER',
            'Pawn_Amount' => 10000, 'Pawn_Date' => '2026-01-01',
            'RePawning_date' => '2026-01-01', 'period1' => 10,
            'period2' => 15, 'period3' => 30, 'validPeriod' => 0,
            'rate1' => 6, 'rate2' => 6, 'rate3' => 6,
            'service_charge' => 0, 'interest_Paid' => 100, 'BalanceInterest' => 0,
        ]);

        $financial = (new ReceiptFinancialCalculator())->calculate($receipt, '2026-01-31');
        $this->assertSame(31, $financial['days']);
        $this->assertEqualsWithDelta(620.00, $financial['gross_interest'], 0.001);
        $this->assertEqualsWithDelta(520.00, $financial['interest'], 0.001);
    }

    public function test_silver_payment_history_starts_the_new_period_after_the_payment_day(): void
    {
        Carbon::setTestNow('2026-01-31');
        try {
            $row = (object) [
                'id' => 1, 'trans_type' => 'PART_PAYMENT', 'dDate' => '2026-01-01',
                'historical_capital' => 10000, 'receiptname' => 'SILVER',
                'rate1' => 6, 'rate2' => 6, 'rate3' => 6,
                'period1' => 10, 'period2' => 15, 'period3' => 30,
            ];
            $enriched = (new ReceiptHistoryService(new ReceiptFinancialCalculator()))
                ->enrichWithRemainingAmounts(collect([$row]));

            $this->assertSame(30, $enriched->first()->days_count);
            $this->assertEqualsWithDelta(600.00, $enriched->first()->interest_amount, 0.001);
        } finally {
            Carbon::setTestNow();
        }
    }
}
