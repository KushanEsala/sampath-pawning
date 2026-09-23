<?php

namespace Tests\Unit;

use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptHistoryService;
use Tests\TestCase;

class ReceiptRedemptionLedgerInterestTest extends TestCase
{
    public function test_legacy_redeem_uses_recorded_summary_interest(): void
    {
        $transaction = (object) [
            'trans_type' => 'REDEEM', 'trans_no' => '02965',
            'dDate' => '2026-04-28', 'Paided_Interest' => null,
        ];
        $summary = (object) [
            'Redeem_Number' => 2965, 'Redeem_Date' => '2026-04-28 14:49:49',
            'Paid_Interest' => 900,
        ];

        $rows = (new ReceiptHistoryService(new ReceiptFinancialCalculator()))
            ->attachRedemptionInterest(collect([$transaction]), collect([$summary]));

        $this->assertEquals(900, $rows->first()->Paided_Interest);
        $this->assertFalse($rows->first()->redeem_interest_conflict);
    }

    public function test_new_transaction_interest_is_not_added_twice(): void
    {
        $transaction = (object) [
            'trans_type' => 'REDEEM', 'trans_no' => 2965,
            'dDate' => '2026-04-28', 'Paided_Interest' => 900,
        ];
        $summary = (object) [
            'Redeem_Number' => 2965, 'Redeem_Date' => '2026-04-28',
            'Paid_Interest' => 900,
        ];

        $rows = (new ReceiptHistoryService(new ReceiptFinancialCalculator()))
            ->attachRedemptionInterest(collect([$transaction]), collect([$summary]));

        $this->assertEquals(900, $rows->first()->Paided_Interest);
        $this->assertFalse($rows->first()->redeem_interest_conflict);
    }

    public function test_ambiguous_summaries_do_not_overwrite_a_transaction(): void
    {
        $transaction = (object) [
            'trans_type' => 'REDEEM', 'trans_no' => 2965,
            'dDate' => '2026-04-28', 'Paided_Interest' => null,
        ];
        $summary = (object) [
            'Redeem_Number' => 2965, 'Redeem_Date' => '2026-04-28',
            'Paid_Interest' => 900,
        ];

        $rows = (new ReceiptHistoryService(new ReceiptFinancialCalculator()))
            ->attachRedemptionInterest(collect([$transaction]), collect([$summary, clone $summary]));

        $this->assertNull($rows->first()->Paided_Interest);
    }
}
