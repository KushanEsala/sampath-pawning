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
}
