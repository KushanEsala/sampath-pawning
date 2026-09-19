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
}
