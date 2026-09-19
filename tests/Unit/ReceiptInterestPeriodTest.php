<?php

namespace Tests\Unit;

use App\Models\TPawnSum;
use App\Services\ReceiptInterestPeriod;
use Tests\TestCase;

class ReceiptInterestPeriodTest extends TestCase
{
    public function test_print_day_count_is_inclusive_and_captured_before_payment_resets_period(): void
    {
        $receipt = new TPawnSum(['Receipt_Date'=>'2026-09-01', 'Pawn_Date'=>'2026-09-01']);
        $days = ReceiptInterestPeriod::days($receipt, '2026-09-13');
        $receipt->Pawn_Date = '2026-09-14';
        $this->assertSame(13, $days);
        $this->assertSame(0, ReceiptInterestPeriod::days($receipt, '2026-09-13'));
        $receipt->RePawning_date = '2026-09-05';
        $this->assertSame(9, ReceiptInterestPeriod::days($receipt, '2026-09-13'));
        $this->assertSame(1, ReceiptInterestPeriod::days($receipt, '2026-09-05'));
    }
}
