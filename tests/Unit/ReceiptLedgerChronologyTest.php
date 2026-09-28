<?php

namespace Tests\Unit;

use App\Models\TPawnSum;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptHistoryService;
use Tests\TestCase;

class ReceiptLedgerChronologyTest extends TestCase
{
    public function test_ledger_runs_oldest_to_newest_with_current_balance_at_the_end(): void
    {
        $service = new ReceiptHistoryService(new ReceiptFinancialCalculator());
        $receipt = new TPawnSum(['Receipt_Number' => 'TEST', 'Invoice_Number' => 'STOCK', 'BC' => '001']);
        $rows = collect([
            (object) [
                'id' => 2, 'dDate' => '2026-02-01', 'trans_type' => 'REDEEM',
                'OC' => null, 'Paided_Interest' => 0, 'Discount' => 0,
                'payable_total' => 100, 'Dr_amount' => 100, 'Extend_Date' => null,
            ],
            (object) [
                'id' => 1, 'dDate' => '2026-01-01', 'trans_type' => 'PAWN',
                'OC' => null, 'remaining_capital' => 100, 'trans_pawn_amount' => 100,
                'Cr_amount' => 100, 'trans_amount' => 100, 'Extend_Date' => null,
            ],
        ]);

        $format = new \ReflectionMethod($service, 'formatLedgerRows');
        $ledger = $format->invoke($service, $rows, $receipt);

        $this->assertSame(['PAWN', 'REDEEM'], $ledger->pluck('type')->all());
        $this->assertEquals(100, $ledger->first()['balance']);
        $this->assertEquals(0, $ledger->last()['balance']);
    }

    public function test_non_financial_activity_follows_same_day_transaction_balance(): void
    {
        $service = new ReceiptHistoryService(new ReceiptFinancialCalculator());
        $financial = collect([
            ['date' => '2026-01-01', 'dr' => 100.0, 'cr' => 0.0, 'balance' => 100.0],
            ['date' => '2026-02-01', 'dr' => 0.0, 'cr' => 40.0, 'balance' => 60.0],
        ]);
        $timeline = collect([[
            'date' => '2026-02-01', 'type' => 'BLOCKED', 'details' => ['Operator' => 'admin'],
        ]]);

        $merge = new \ReflectionMethod($service, 'mergeNonFinancialActivity');
        $ledger = $merge->invoke($service, $financial, $timeline);

        $this->assertSame(['2026-01-01', '2026-02-01', '2026-02-01'], $ledger->pluck('date')->all());
        $this->assertEquals(60, $ledger->last()['balance']);
        $this->assertSame('BLOCKED', $ledger->last()['type']);
    }

    public function test_print_history_uses_the_same_order_and_expands_all_details(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Number' => 4549,
            'Invoice_Number' => 769145,
            'Receipt_Date' => '2026-04-05',
            'Final_date' => '2027-04-05',
        ]);
        $history = [
            'receipt' => $receipt,
            'customer' => null,
            'financial' => [
                'principal' => 0, 'interest' => 0, 'service_charge' => 0,
                'letter_charge' => 0, 'arrears_total' => 0, 'redemption_total' => 0,
            ],
            'ledger' => collect([
                ['date' => '2026-04-05', 'description' => 'Pawn receipt issued',
                    'summary' => 'Capital: Rs. 40,000.00', 'details' => ['Capital: Rs. 40,000.00'],
                    'dr' => 40000, 'cr' => 0, 'balance' => 40000, 'operator' => 'Cashier'],
                ['date' => '2026-04-28', 'description' => 'Receipt redeemed',
                    'summary' => 'Customer paid: Rs. 40,000.00', 'details' => ['Paid interest: Rs. 0.00'],
                    'dr' => 0, 'cr' => 40000, 'balance' => 0, 'operator' => 'Cashier'],
            ]),
        ];

        $html = view('receiptHistoryPrint', compact('history'))->render();

        $this->assertStringContainsString('<th>Capital</th>', $html);
        $this->assertStringContainsString('Oldest First', $html);
        $this->assertTrue(strpos($html, 'Pawn receipt issued') < strpos($html, 'Receipt redeemed'));
        $this->assertStringNotContainsString('ledger-detail-row d-none', $html);
        $this->assertStringContainsString('Ledger totals / Current balance', $html);
    }
}
