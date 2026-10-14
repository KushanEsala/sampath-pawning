<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\TPawnSum;
use App\Models\User;
use App\Services\ReceiptHistoryService;
use App\Services\ReceiptNumberVisibility;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ReceiptNumberVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
    }

    private function makeUser(string $role): User
    {
        $user = new User();
        $user->forceFill([
            'id' => 1,
            'username' => 'testuser',
            'role' => $role,
            'BC' => '001',
            'Branch' => 'Test Branch',
        ]);
        return $user;
    }

    public function test_admin_always_sees_ticket_and_stock_numbers(): void
    {
        $this->actingAs($this->makeUser('Admin'));
        $receipt = (object) ['Ticket_Number' => 'TK123', 'Invoice_Number' => 'STK456'];

        $this->assertTrue(ReceiptNumberVisibility::ticket($receipt, Request::create('/')));
        $this->assertTrue(ReceiptNumberVisibility::stock($receipt, Request::create('/')));
    }

    public function test_cashier_hidden_by_default_and_on_receipt_and_nic_searches(): void
    {
        $this->actingAs($this->makeUser('Cashier'));
        $receipt = (object) ['Ticket_Number' => 'TK123', 'Invoice_Number' => 'STK456'];

        // Default request without search
        $this->assertFalse(ReceiptNumberVisibility::ticket($receipt, Request::create('/')));
        $this->assertFalse(ReceiptNumberVisibility::stock($receipt, Request::create('/')));

        // Receipt search
        $receiptReq = Request::create('/', 'GET', ['search_type' => 'receipt', 'search_query' => '100']);
        $this->assertFalse(ReceiptNumberVisibility::ticket($receipt, $receiptReq));
        $this->assertFalse(ReceiptNumberVisibility::stock($receipt, $receiptReq));

        // NIC search
        $nicReq = Request::create('/', 'GET', ['search_type' => 'nic', 'search_query' => '123456789V']);
        $this->assertFalse(ReceiptNumberVisibility::ticket($receipt, $nicReq));
        $this->assertFalse(ReceiptNumberVisibility::stock($receipt, $nicReq));
    }

    public function test_cashier_sees_ticket_number_only_when_customer_supplies_it(): void
    {
        $this->actingAs($this->makeUser('Cashier'));
        $receipt = (object) ['Ticket_Number' => 'TK123', 'Invoice_Number' => 'STK456'];

        $ticketReq = Request::create('/', 'GET', ['search_type' => 'ticket', 'search_query' => 'TK123']);
        $this->assertTrue(ReceiptNumberVisibility::ticket($receipt, $ticketReq));
        $this->assertFalse(ReceiptNumberVisibility::stock($receipt, $ticketReq));

        $directFieldReq = Request::create('/', 'GET', ['search_ticket_no' => 'TK123']);
        $this->assertTrue(ReceiptNumberVisibility::ticket($receipt, $directFieldReq));
        $this->assertFalse(ReceiptNumberVisibility::stock($receipt, $directFieldReq));
    }

    public function test_cashier_sees_stock_number_only_when_customer_supplies_it(): void
    {
        $this->actingAs($this->makeUser('Cashier'));
        $receipt = (object) ['Ticket_Number' => 'TK123', 'Invoice_Number' => 'STK456'];

        $stockReq = Request::create('/', 'GET', ['search_type' => 'stock', 'search_query' => 'STK456']);
        $this->assertFalse(ReceiptNumberVisibility::ticket($receipt, $stockReq));
        $this->assertTrue(ReceiptNumberVisibility::stock($receipt, $stockReq));

        $directFieldReq = Request::create('/', 'GET', ['search_invoice_no' => 'STK456']);
        $this->assertFalse(ReceiptNumberVisibility::ticket($receipt, $directFieldReq));
        $this->assertTrue(ReceiptNumberVisibility::stock($receipt, $directFieldReq));
    }

    public function test_receipt_search_nic_list_hides_ticket_and_stock_from_cashier(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Number' => 100,
            'Ticket_Number' => 'TK999',
            'Invoice_Number' => 'INV888',
            'Amount' => 5000,
            'Customer_NIC' => '123456789V',
        ]);
        $tickets = collect([$receipt]);
        $searchType = 'nic';
        $searchQuery = '123456789V';
        $customer = null;
        $history = null;

        // Cashier view
        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('receiptSearch', compact('tickets', 'searchType', 'searchQuery', 'customer', 'history'))->render();
        $this->assertStringNotContainsString('Ticket No', $cashierHtml);
        $this->assertStringNotContainsString('Stock No (Invoice)', $cashierHtml);
        $this->assertStringNotContainsString('TK999', $cashierHtml);
        $this->assertStringNotContainsString('INV888', $cashierHtml);

        // Admin view
        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('receiptSearch', compact('tickets', 'searchType', 'searchQuery', 'customer', 'history'))->render();
        $this->assertStringContainsString('Ticket No', $adminHtml);
        $this->assertStringContainsString('Stock No (Invoice)', $adminHtml);
        $this->assertStringContainsString('TK999', $adminHtml);
        $this->assertStringContainsString('INV888', $adminHtml);
    }

    public function test_date_range_pawning_report_shows_only_stock_number_to_cashier(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Number' => 100,
            'Invoice_Number' => 'INV100',
            'Ticket_Number' => 'TKT100',
            'Receipt_Type' => 'A',
            'Receipt_Date' => '2026-10-11',
            'Pawn_Weight' => 1,
            'Total_Weight' => 1,
            'Amount' => 5000,
        ]);
        $data = [
            'recipts' => collect([$receipt]),
            'pawnWeight' => '1.00',
            'totalWeight' => '1.00',
            'totalAmount' => '5,000.00',
        ];

        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('search_results', $data)->render();
        $this->assertStringContainsString('INV100', $cashierHtml);
        $this->assertStringNotContainsString('<th>Receipt Number</th>', $cashierHtml);
        $this->assertStringNotContainsString('<td>100</td>', $cashierHtml);
        $this->assertStringNotContainsString('TKT100', $cashierHtml);

        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('search_results', $data)->render();
        $this->assertStringContainsString('INV100', $adminHtml);
        $this->assertStringContainsString('<td>100</td>', $adminHtml);
        $this->assertStringContainsString('TKT100', $adminHtml);
    }

    public function test_stock_number_search_reveals_only_the_exact_supplied_stock_number(): void
    {
        $results = collect([(object) [
            'Receipt_Number' => 100,
            'Invoice_Number' => 'INV100',
            'Customer_Name' => 'Test',
            'Receipt_Date' => '2026-10-11',
            'Pawn_Weight' => 1,
            'Total_Weight' => 1,
            'Amount' => 5000,
            'Interest' => 100,
            'IsRedeemed' => 0,
            'Final_date' => '2026-11-11',
        ]]);
        $data = [
            'results' => $results,
            'results_Details' => collect(),
            'results_transtion' => collect(),
            'Receipt_Type' => collect(),
        ];
        $this->actingAs($this->makeUser('Cashier'));
        app()->instance('request', Request::create('/stock-search'));
        $defaultHtml = view('stocknumbersearch', $data)->render();
        $this->assertStringNotContainsString('Stock: INV100', $defaultHtml);

        app()->instance('request', Request::create('/stock-search', 'POST', ['Stock_Number' => 'INV100']));
        $exactHtml = view('stocknumbersearch', $data)->render();
        $this->assertStringContainsString('Stock: INV100', $exactHtml);
    }

    public function test_forfeit_list_hides_stock_and_ticket_from_cashier(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Number' => 100,
            'Ticket_Number' => 'TK777',
            'Invoice_Number' => 'INV666',
            'Customer_Name' => 'Customer Name',
            'isForfeit' => 0,
        ]);
        $receipt->setAttribute('receipt_history', [
            'customer' => null,
            'financial' => array_fill_keys(['principal','interest','service_charge','letter_charge','arrears_total','redemption_total'], 0),
            'details' => collect(),
            'timeline' => collect(),
        ]);
        $receipt->setAttribute('forfeit_record', null);
        $receipt->setAttribute('stock_items', collect());
        $recipts = new LengthAwarePaginator([$receipt], 1, 25, 1, ['path' => '/forfeitReceipt_List']);
        $status = 'all';

        // Cashier view
        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('forfeitReceipt_List', compact('recipts', 'status'))->render();
        $this->assertStringNotContainsString('Stock: INV666', $cashierHtml);
        $this->assertStringNotContainsString('Stock (Invoice): INV666', $cashierHtml);
        $this->assertStringNotContainsString('Ticket: TK777', $cashierHtml);

        app()->instance('request', Request::create('/forfeitReceipt_List', 'GET', ['search_type' => 'stock', 'search_query' => 'INV666']));
        $stockHtml = view('forfeitReceipt_List', compact('recipts', 'status'))->render();
        $this->assertStringContainsString('Stock: INV666', $stockHtml);
        $this->assertStringNotContainsString('Ticket: TK777', $stockHtml);

        // Admin view
        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('forfeitReceipt_List', compact('recipts', 'status'))->render();
        $this->assertStringContainsString('Stock: INV666', $adminHtml);
        $this->assertStringContainsString('Stock (Invoice): INV666', $adminHtml);
        $this->assertStringContainsString('Ticket: TK777', $adminHtml);
    }

    public function test_forfeit_article_receipt_hides_stock_and_ticket_from_cashier(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Number' => 100,
            'Invoice_Number' => 'INV555',
            'Ticket_Number' => 'TK444',
            'Customer_NIC' => '123456789V',
        ]);
        $receipt->forceFill(['id' => 1, 'current_customer_name' => 'Test', 'available_articles' => collect()]);
        $recipts = new LengthAwarePaginator([$receipt], 1, 25, 1, ['path' => '/forfeit_article_receipt']);

        // Cashier view
        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('forfeit_article_receipt', compact('recipts'))->render();
        $this->assertStringNotContainsString('INV555', $cashierHtml);
        $this->assertStringNotContainsString('TK444', $cashierHtml);

        app()->instance('request', Request::create('/forfeit_article_receipt', 'GET', ['search_type' => 'ticket', 'search_query' => 'TK444']));
        $ticketHtml = view('forfeit_article_receipt', compact('recipts'))->render();
        $this->assertStringContainsString('Ticket Number: TK444', $ticketHtml);
        $this->assertStringNotContainsString('INV555', $ticketHtml);

        // Admin view
        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('forfeit_article_receipt', compact('recipts'))->render();
        $this->assertStringContainsString('INV555', $adminHtml);
        $this->assertStringContainsString('TK444', $adminHtml);
    }

    public function test_part_payment_report_shows_only_stock_number_to_cashier(): void
    {
        $row = (object) [
            'Invoice_Number' => 'INV333',
            'Ticket_Number' => 'TK333',
            'Receipt_Number' => 100,
            'Customer_NIC' => '123456789V',
            'Customer_Name' => 'Test Customer',
            'Redeem_Date' => '2026-10-11',
            'Payable_Pawn_Amount' => 5000,
            'Paid_Interest' => 100,
            'Payable_Total' => 5100,
            'Discount' => 0,
            'OC' => 'cashier',
        ];
        $partPayments = collect([$row]);

        // Cashier view
        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('PartpaymentHistortyReport', compact('partPayments'))->render();
        $this->assertStringContainsString('<th class="text-center">Stock No</th>', $cashierHtml);
        $this->assertStringContainsString('INV333', $cashierHtml);
        $this->assertStringNotContainsString('<th class="text-center">Receipt No</th>', $cashierHtml);
        $this->assertStringNotContainsString('<td class="text-center">100</td>', $cashierHtml);
        $this->assertStringNotContainsString('TK333', $cashierHtml);

        // Even a ticket search must not reveal ticket numbers in cashier report rows.
        app()->instance('request', Request::create('/part-payment-history', 'GET', ['search_type' => 'ticket', 'search_query' => 'TK333']));
        $ticketHtml = view('PartpaymentHistortyReport', compact('partPayments'))->render();
        $this->assertStringContainsString('INV333', $ticketHtml);
        $this->assertStringNotContainsString('<td class="text-center">TK333</td>', $ticketHtml);

        // Admin view
        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('PartpaymentHistortyReport', compact('partPayments'))->render();
        $this->assertStringContainsString('INV333', $adminHtml);
        $this->assertStringContainsString('<td class="text-center">100</td>', $adminHtml);
        $this->assertStringContainsString('<td class="text-center">TK333</td>', $adminHtml);
    }

    public function test_repawning_report_shows_only_stock_number_to_cashier(): void
    {
        $row = (object) [
            'Invoice_Number' => 'INV222',
            'Ticket_Number' => 'TK222',
            'Receipt_Number' => 100,
            'Pawn_Date' => '2026-09-01',
            'Customer_NIC' => '123456789V',
            'Customer_Name' => 'Test Customer',
            'Customer_Phone' => '0771234567',
            'Redeem_Date' => '2026-10-11',
            'Original_Pawn_Amount' => 5000,
            'Payable_Total' => 5100,
        ];
        $repawnings = collect([$row]);

        // Cashier view
        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('repawningHistortyReport', compact('repawnings'))->render();
        $this->assertStringContainsString('<th class="ps-3">Stock No</th>', $cashierHtml);
        $this->assertStringContainsString('INV222', $cashierHtml);
        $this->assertStringNotContainsString('<th>Receipt No</th>', $cashierHtml);
        $this->assertStringNotContainsString('<td>100</td>', $cashierHtml);
        $this->assertStringNotContainsString('TK222', $cashierHtml);

        app()->instance('request', Request::create('/repawning-history', 'GET', ['search_type' => 'ticket', 'search_query' => 'TK222']));
        $ticketHtml = view('repawningHistortyReport', compact('repawnings'))->render();
        $this->assertStringContainsString('INV222', $ticketHtml);
        $this->assertStringNotContainsString('<td>TK222</td>', $ticketHtml);

        // Admin view
        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('repawningHistortyReport', compact('repawnings'))->render();
        $this->assertStringContainsString('INV222', $adminHtml);
        $this->assertStringContainsString('<td>100</td>', $adminHtml);
        $this->assertStringContainsString('<td>TK222</td>', $adminHtml);
    }

    public function test_redeem_report_shows_only_stock_number_to_cashier(): void
    {
        $row = (object) [
            'Invoice_Number' => 'STK777',
            'Ticket_Number' => 'TKT777',
            'Receipt_Number' => 500,
            'Redeem_Date' => '2026-10-11',
            'Redeem_Number' => 12,
            'Total_Weight' => 10.5,
            'Pawn_Weight' => 9.5,
            'Original_Pawn_Amount' => 50000,
            'Payable_Pawn_Amount' => 50000,
            'Paid_Interest' => 1500,
            'Payable_Interest' => 1500,
            'Stamp_Fee' => 25,
            'Document_Charges' => 0,
            'Advance_Balance' => 0,
            'Discount' => 0,
            'Payable_Total' => 51525,
            'OC' => 'cashier',
            'BC' => '001',
        ];
        $redeem = collect([$row]);
        $data = [
            'redeem' => $redeem,
            'recipts' => $redeem,
            'totalAmount' => '51,525.00',
            'totalWeight' => '10.50',
            'pawnWeight' => '9.50',
            'Interest' => '1,500.00',
        ];

        // Cashier view
        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('redeem_report', $data)->render();
        $this->assertStringContainsString('<th>Stock No</th>', $cashierHtml);
        $this->assertStringContainsString('STK777', $cashierHtml);
        $this->assertStringNotContainsString('<th>Receipt No</th>', $cashierHtml);
        $this->assertStringNotContainsString('<th>Redeem No</th>', $cashierHtml);
        $this->assertStringNotContainsString('<td>500</td>', $cashierHtml);
        $this->assertStringNotContainsString('TKT777', $cashierHtml);

        // Admin view
        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('redeem_report', $data)->render();
        $this->assertStringContainsString('<th>Stock No</th>', $adminHtml);
        $this->assertStringContainsString('<th>Receipt No</th>', $adminHtml);
        $this->assertStringContainsString('<th>Redeem No</th>', $adminHtml);
        $this->assertStringContainsString('<th>Ticket No</th>', $adminHtml);
        $this->assertStringContainsString('STK777', $adminHtml);
        $this->assertStringContainsString('<td>500</td>', $adminHtml);
        $this->assertStringContainsString('TKT777', $adminHtml);
    }

    public function test_stock_check_report_shows_only_stock_number_to_cashier(): void
    {
        $receipt = new TPawnSum();
        $receipt->forceFill([
            'id' => 42,
            'Receipt_Number' => 500,
            'Invoice_Number' => 'STK500',
            'Ticket_Number' => 'TKT500',
            'checkbox_stock' => 0,
            'Customer_NIC' => '123456789V',
            'Customer_Name' => 'Test Customer',
            'Customer_Address' => 'Test Address',
            'Customer_Phone' => '0771234567',
            'Receipt_Type' => 'A',
            'Receipt_Date' => '2026-10-11',
            'Amount' => 5000,
        ]);
        $recipts = collect([$receipt]);
        $data = ['recipts' => $recipts, 'pawnCount' => 1, 'amount' => '5,000.00'];

        $this->actingAs($this->makeUser('Cashier'));
        $cashierHtml = view('StockCheckreport', $data)->render();
        $this->assertStringContainsString('STK500', $cashierHtml);
        $this->assertStringNotContainsString('data-receipt=', $cashierHtml);
        $this->assertStringNotContainsString('data-customer=', $cashierHtml);
        $this->assertStringContainsString('data-record-id="42"', $cashierHtml);
        $this->assertStringNotContainsString('TKT500', $cashierHtml);

        $this->actingAs($this->makeUser('Admin'));
        $adminHtml = view('StockCheckreport', $data)->render();
        $this->assertStringContainsString('STK500', $adminHtml);
        $this->assertStringContainsString('data-receipt="500"', $adminHtml);
        $this->assertStringContainsString('Receipt</span><span class="number">500', $adminHtml);
        $this->assertStringContainsString('TKT500', $adminHtml);
        $this->assertStringContainsString('data-date="2026-10-11 00:00:00"', $adminHtml);
        $this->assertStringContainsString('Time</span><span class="number">00:00:00', $adminHtml);
    }
}
