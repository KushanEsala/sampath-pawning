<?php

namespace Tests\Unit;

use App\Models\TPawnSum;
use App\Services\ReceiptTypeResolver;
use Tests\TestCase;

class ReceiptTypeSnapshotTest extends TestCase
{
    public function test_existing_receipt_keeps_its_issue_time_rates_for_current_cycle(): void
    {
        $receipt = new TPawnSum();
        $receipt->forceFill([
            'Receipt_Type' => 'A',
            'Pawn_Date' => '2025-01-01',
            'RePawning_date' => '2026-01-01',
            'rate1' => 1.25,
            'period1' => 10,
            'rate2' => 1.50,
            'period2' => 15,
            'rate3' => 2.00,
            'period3' => 30,
            'service_charge' => 30,
        ]);

        $configuration = (new ReceiptTypeResolver())->resolveForCurrentCycle($receipt);

        $this->assertEquals(1.25, (float) $configuration->rate1);
        $this->assertEquals(2.00, (float) $configuration->rate3);
        $this->assertEquals(30, (float) $configuration->service_charge);
        $this->assertEquals('2025-01-01', $configuration->effective_from->toDateString());
    }
}
