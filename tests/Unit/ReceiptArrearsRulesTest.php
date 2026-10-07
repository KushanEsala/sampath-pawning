<?php

namespace Tests\Unit;

use App\Models\TPawnSum;
use App\Services\ArrearsLetterService;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptLifecycleService;
use Carbon\Carbon;
use Tests\TestCase;

class ReceiptArrearsRulesTest extends TestCase
{
    public function test_letter_due_dates_are_anchored_to_expiry(): void
    {
        $receipt = new TPawnSum(['Final_date' => '2026-01-10']);
        $service = new ArrearsLetterService(new ReceiptFinancialCalculator(), new ReceiptLifecycleService());

        $this->assertSame('2026-01-31', $service->dueDate($receipt, 1)->toDateString());
        $this->assertSame('2026-02-21', $service->dueDate($receipt, 2)->toDateString());
        $this->assertSame('2026-03-14', $service->dueDate($receipt, 3)->toDateString());
    }

    public function test_late_first_letter_does_not_reset_next_interval(): void
    {
        $receipt = new TPawnSum([
            'Final_date' => '2026-01-01', 'letter_1_days' => 0,
            'letter_2_days' => 21, 'letter_3_days' => 21,
            'is_letter_1' => 1, 'letter_1_date' => '2026-02-10',
        ]);
        $service = new ArrearsLetterService(new ReceiptFinancialCalculator(), new ReceiptLifecycleService());
        $this->assertSame('2026-01-22', $service->dueDate($receipt, 2)->toDateString());
        $this->assertTrue($service->isEligible($receipt, 2, Carbon::parse('2026-02-10')));
    }

    public function test_expired_receipt_cannot_print_before_its_saved_first_interval(): void
    {
        $receipt = new TPawnSum(['Final_date'=>'2026-10-01', 'letter_1_days'=>14]);
        $service = new ArrearsLetterService(new ReceiptFinancialCalculator(), new ReceiptLifecycleService());

        $this->assertFalse($service->isEligible($receipt, 1, Carbon::parse('2026-10-07')));
        $this->assertTrue($service->isEligible($receipt, 1, Carbon::parse('2026-10-15')));
    }

    public function test_service_and_letter_charges_are_included_in_arrears(): void
    {
        Carbon::setTestNow('2026-01-10');
        $receipt = new TPawnSum([
            'Receipt_Type' => 'A', 'receiptname' => 'A',
            'Pawn_Amount' => 10000, 'Pawn_Date' => '2026-01-01',
            'RePawning_date' => '2026-01-01', 'period1' => 10,
            'period2' => 15, 'validPeriod' => 90, 'rate1' => 1,
            'rate2' => 2, 'rate3' => 2.5, 'service_charge' => 60,
            'letter_pay_one' => 125, 'letter_pay_two' => 125,
            'letter_pay_three' => 0, 'interest_Paid' => 0,
            'BalanceInterest' => 0,
        ]);

        $result = (new ReceiptFinancialCalculator())->calculate($receipt);

        $this->assertSame(100.0, $result['interest']);
        $this->assertSame(60.0, $result['service_charge']);
        $this->assertSame(250.0, $result['letter_charge']);
        $this->assertSame(410.0, $result['arrears_total']);
        $this->assertSame(10410.0, $result['redemption_total']);
        Carbon::setTestNow();
    }

    public function test_same_day_after_paid_interest_has_no_new_interest_even_if_type_has_service_charge(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Type' => 'A', 'receiptname' => 'A',
            'Pawn_Amount' => 10000, 'Pawn_Date' => '2026-09-11',
            'RePawning_date' => '2026-09-11', 'period1' => 10,
            'period2' => 15, 'period3' => 30, 'validPeriod' => 90,
            'rate1' => 1, 'rate2' => 2, 'rate3' => 2.5,
            'service_charge' => 60, 'interest_Paid' => 0,
            'BalanceInterest' => 0,
        ]);
        $financial = (new ReceiptFinancialCalculator())->calculate($receipt, '2026-09-10');
        $this->assertSame(0, $financial['days']);
        $this->assertSame(0.0, $financial['interest']);
        $this->assertSame(60.0, $financial['service_charge']);
    }
}
