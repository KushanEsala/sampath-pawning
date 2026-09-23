<?php

namespace Tests\Unit;

use App\Models\Recei_Add;
use App\Models\TPawnSum;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptHistoryService;
use App\Services\ReceiptTypeResolver;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class ReceiptHistoryRateCycleTest extends TestCase
{
    public function test_later_edited_master_cannot_be_presented_as_a_verified_old_rate(): void
    {
        $historical = new Recei_Add();
        $historical->forceFill([
            'id' => 17, 'receiptname' => 'A', 'rate1' => 2,
            'rate2' => 2.6, 'rate3' => 3, 'period1' => 10,
            'period2' => 15, 'period3' => 30,
            'effective_from' => '2020-01-01',
            'updated_at' => '2026-09-23 12:00:00',
        ]);
        $resolver = Mockery::mock(ReceiptTypeResolver::class)->makePartial();
        $resolver->shouldReceive('resolveByDate')->once()->andReturn($historical);

        $receipt = new TPawnSum();
        $receipt->forceFill([
            'Receipt_Type' => 'A', 'Pawn_Date' => '2026-09-09',
            'rate1' => 1.8, 'rate2' => 2, 'rate3' => 1,
            'period1' => 10, 'period2' => 15, 'period3' => 30,
        ]);
        $rows = collect([
            (object) ['id' => 1, 'dDate' => '2026-04-05', 'trans_type' => 'PAWN', 'historical_capital' => 14000, 'original_receipt_type' => 'A'],
            (object) ['id' => 2, 'dDate' => '2026-09-09', 'trans_type' => 'REPAWNING', 'historical_capital' => 20000, 'Paided_Interest' => 420],
        ]);

        $pawn = (new ReceiptHistoryService(new ReceiptFinancialCalculator($resolver), $resolver))
            ->enrichWithRemainingAmounts($rows, $receipt)->firstWhere('id', 1);

        $this->assertNull($pawn->rate_1_used);
        $this->assertStringContainsString('unverified', $pawn->rate_configuration);
        $this->assertEquals(420, $pawn->interest_amount);
    }

    public function test_part_payment_keeps_the_cycle_rate_and_latest_repawn_uses_its_snapshot(): void
    {
        Carbon::setTestNow('2026-04-30');
        try {
            $historical = new Recei_Add();
            $historical->forceFill([
                'id' => 17, 'receiptname' => 'A', 'rate1' => 1,
                'rate2' => 1.5, 'rate3' => 2, 'period1' => 10,
                'period2' => 15, 'period3' => 30, 'validPeriod' => 90,
            ]);
            $resolver = Mockery::mock(ReceiptTypeResolver::class)->makePartial();
            $resolver->shouldReceive('resolveByDate')->once()->with('A', Mockery::type(Carbon::class))
                ->andReturn($historical);

            $receipt = new TPawnSum();
            $receipt->forceFill([
                'Receipt_Type' => 'A', 'Pawn_Date' => '2026-03-01',
                'rate1' => 4, 'rate2' => 4.5, 'rate3' => 5,
                'period1' => 10, 'period2' => 15, 'period3' => 30,
                'validPeriod' => 90,
            ]);
            $rows = collect([
                (object) ['id' => 1, 'dDate' => '2026-01-01', 'trans_type' => 'PAWN', 'historical_capital' => 10000, 'original_receipt_type' => 'A'],
                (object) ['id' => 2, 'dDate' => '2026-02-01', 'trans_type' => 'PART_PAYMENT', 'historical_capital' => 8000, 'Paided_Interest' => 321],
                (object) ['id' => 3, 'dDate' => '2026-03-01', 'trans_type' => 'REPAWNING', 'historical_capital' => 12000],
                (object) ['id' => 4, 'dDate' => '2026-04-01', 'trans_type' => 'PART_PAYMENT', 'historical_capital' => 9000],
            ]);

            $history = (new ReceiptHistoryService(new ReceiptFinancialCalculator($resolver), $resolver))
                ->enrichWithRemainingAmounts($rows, $receipt);

            $this->assertEquals(1, $history->firstWhere('id', 1)->rate_1_used);
            $this->assertEquals(321, $history->firstWhere('id', 1)->interest_amount);
            $this->assertSame('Recorded at next transaction', $history->firstWhere('id', 1)->interest_source);
            $this->assertEquals(1, $history->firstWhere('id', 2)->rate_1_used);
            $this->assertSame('Receipt type version #17', $history->firstWhere('id', 2)->rate_configuration);
            $this->assertEquals(4, $history->firstWhere('id', 3)->rate_1_used);
            $this->assertEquals(4, $history->firstWhere('id', 4)->rate_1_used);
            $this->assertSame('Stored receipt rate snapshot', $history->firstWhere('id', 4)->rate_configuration);
        } finally {
            Carbon::setTestNow();
        }
    }
}
