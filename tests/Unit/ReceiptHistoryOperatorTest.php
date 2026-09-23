<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptHistoryService;
use Tests\TestCase;

class ReceiptHistoryOperatorTest extends TestCase
{
    public function test_event_displays_recorded_operator_and_profile_not_the_viewer(): void
    {
        $service = new ReceiptHistoryService(new ReceiptFinancialCalculator());
        $operator = new User();
        $operator->forceFill(['username'=>'original', 'name'=>'Original Operator', 'role'=>'Cashier', 'Branch'=>'Main', 'BC'=>'001']);
        $property = new \ReflectionProperty($service, 'operatorProfiles');
        $property->setAccessible(true);
        $property->setValue($service, ['original|001'=>$operator]);
        $method = new \ReflectionMethod($service, 'event');
        $method->setAccessible(true);
        $event = $method->invoke($service, '2026-09-01', 'PART PAYMENT', 50, [], 1, 'original', '001');
        $this->assertSame('original · Original Operator · Cashier · Main · Branch 001', $event['details']['Operator']);
        $missing = $method->invoke($service, '2026-09-01', 'LETTER 1 CHARGE', 125);
        $this->assertSame('Not recorded', $missing['details']['Operator']);
        $deleted = $method->invoke($service, '2026-09-01', 'REDEEM', 50, [], 1, 'deleted', '001');
        $this->assertSame('deleted · Branch 001', $deleted['details']['Operator']);
    }

    public function test_append_charge_rows_and_timeline_resolve_letter_charges(): void
    {
        $calc = new ReceiptFinancialCalculator();
        $service = new ReceiptHistoryService($calc);

        $receipt = new \App\Models\TPawnSum([
            'Receipt_Number' => 'TEST999',
            'BC' => '001',
            'letter_1_date' => '2026-05-07',
            'letter_2_date' => '2026-07-22',
            'letter_pay_one' => 0.00,
            'letter_pay_two' => 0.00,
            'Postage_charge' => 200.00,
            'is_letter_1' => 1,
            'is_letter_2' => 1,
        ]);

        $rows = $service->appendChargeRows(collect(), $receipt);
        $this->assertCount(2, $rows);

        $letter2 = $rows->firstWhere('trans_type', 'LETTER 2 CHARGE');
        $this->assertNotNull($letter2);
        $this->assertSame(200.0, $letter2->trans_amount);
        $this->assertSame('2nd Letter', $letter2->letter_sent);

        $letter1 = $rows->firstWhere('trans_type', 'LETTER 1 CHARGE');
        $this->assertNotNull($letter1);
        $this->assertSame(200.0, $letter1->trans_amount);
        $this->assertSame('1st Letter', $letter1->letter_sent);
    }

    public function test_remaining_history_prefers_the_event_capital_over_the_latest_receipt_balance(): void
    {
        $service = new ReceiptHistoryService(new ReceiptFinancialCalculator());
        $rows = collect([
            (object) [
                'id' => 1, 'dDate' => '2026-01-01', 'trans_type' => 'PAWN',
                'trans_pawn_amount' => 10000, 'rate1' => 1, 'rate2' => 1,
                'rate3' => 1, 'period1' => 10, 'period2' => 15,
            ],
            (object) [
                'id' => 2, 'dDate' => '2026-01-10', 'trans_type' => 'REPAWNING',
                'historical_capital' => 20000, 'RePawning_amount' => 99999,
                'rate1' => 1, 'rate2' => 1, 'rate3' => 1,
                'period1' => 10, 'period2' => 15,
            ],
            (object) [
                'id' => 3, 'dDate' => '2026-01-20', 'trans_type' => 'PART_PAYMENT',
                'historical_capital' => 15000, 'Paided_Captional' => 5000,
                'rate1' => 1, 'rate2' => 1, 'rate3' => 1,
                'period1' => 10, 'period2' => 15,
            ],
        ]);

        $history = $service->enrichWithRemainingAmounts($rows);
        $repawn = $history->firstWhere('trans_type', 'REPAWNING');
        $partPayment = $history->firstWhere('trans_type', 'PART_PAYMENT');

        $this->assertSame(20000.0, $repawn->remaining_capital);
        $this->assertSame(15000.0, $partPayment->remaining_capital);
        $this->assertNotSame(99999.0, $repawn->remaining_capital);
    }
}
