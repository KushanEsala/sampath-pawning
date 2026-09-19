<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\TPawnSum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

// In-memory fixtures only: never connects to or modifies the project database.
class ForfeitReportRenderingTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_payment_prints_show_day_count_beside_interest_without_changing_amounts(): void
    {
        $pawn = ['Customer_Name'=>'Example','Customer_Address'=>'Main Road','Customer_Phone'=>'0771234567',
            'Customer_NIC'=>'123V','Receipt_Number'=>100,'Invoice_Number'=>300,'Final_date'=>'2026-10-01','Receipt_Date'=>'2026-09-01'];
        $payment = array_merge(array_fill_keys(['current_pawn_amount','Postage_Charges','Paid_Interest','paid_cap_amount','Payable_Total','Discount','Stamp_Fee','Document_Charges','Original_Pawn_Amount'],100),
            ['Receipt_Number'=>100,'Redeem_Date'=>'2026-09-13']);
        $data = ['pawnSumData'=>collect([$pawn]),'pawnDetailsData'=>collect(),'redeemdata'=>collect([$payment]),
            'companyData'=>collect(),'branchDetails'=>collect(),'interestDays'=>13];
        foreach (['partpaymentReceiptPrint','redeemReceiptPrint'] as $template) {
            $html = view($template, $data)->render();
            $this->assertStringContainsString('(13 days)', $html);
            $this->assertStringContainsString('100.00', $html);
        }
    }

    public function test_only_missed_promise_dates_highlight_reminder_rows(): void
    {
        Carbon::setTestNow('2026-09-13');
        $records = collect(['2026-09-12','2026-09-13','2026-09-14'])->map(function ($date, $index) {
            $receipt = new TPawnSum(['Receipt_Number'=>100+$index]);
            $receipt->forceFill([
                'id'=>$index+1, 'financial_breakdown'=>array_fill_keys(['principal','interest','service_charge','letter_charge','arrears_total','redemption_total'], 0),
                'current_promise'=>new \App\Models\ForfeitReminderPromise(['promise_date'=>$date, 'status'=>'PENDING']),
                'can_queue_forfeit'=>false, 'reminder_due_date'=>'2026-09-01',
                'current_customer_name'=>'Example', 'current_customer_phone'=>'',
            ]);
            return $receipt;
        });
        $receipts = new LengthAwarePaginator($records, 3, 25, 1, ['path'=>'/forfeit-reminders']);
        $html = view('forfeitReminderList', compact('receipts'))->render();
        $this->assertSame(1, substr_count($html, 'class="receipt-summary-row reminder-overdue"'));
        $this->assertSame(1, substr_count($html, '>Promise overdue</span>'));
    }

    public function test_article_list_selects_receipts_and_displays_invoice_as_stock_number(): void
    {
        $receipt = new TPawnSum(['Receipt_Number'=>100,'Invoice_Number'=>300,'Ticket_Number'=>200]);
        $receipt->forceFill(['id'=>1,'current_customer_name'=>'Example', 'available_articles'=>collect()]);
        $recipts = new LengthAwarePaginator([$receipt], 1, 25, 1, ['path'=>'/forfeit_article_receipt']);
        $html = view('forfeit_article_receipt', compact('recipts'))->render();
        $this->assertStringContainsString('name="selected_receipts[]" value="1"', $html);
        $this->assertStringContainsString('<td>300</td>', $html);
        $this->assertStringContainsString('Forfeit selected &amp; print', $html);
        $this->assertStringContainsString('data-article-details="articles-1"', $html);
        $this->assertStringContainsString('sidebar-navigation.js', $html);
    }

    public function test_transfer_print_contains_frozen_finances_dates_and_operator(): void
    {
        $event = new \App\Models\ReceiptLifecycleEvent([
            'receipt_number'=>100,'created_by'=>'original','event_date'=>'2026-09-13 10:00:00',
            'event_data'=>json_encode(['stock_number'=>300,'ticket_number'=>200,'forfeited_date'=>'2026-09-01',
                'capital_outstanding'=>500,'interest_outstanding'=>50,'capital_paid'=>100,'interest_paid'=>20,
                'operator'=>['name'=>'Original Operator'], 'articles'=>[]]),
        ]);
        $html = view('forfeitArticleTransferPrint', ['events'=>collect([$event])])->render();
        $this->assertStringContainsString('Ticket Number', $html);
        $this->assertStringContainsString('Stock Number (Invoice Number)', $html);
        $this->assertStringContainsString('200', $html);
        $this->assertStringContainsString('300', $html);
        $this->assertStringContainsString('500.00', $html);
        $this->assertStringContainsString('50.00', $html);
    }
    protected function setUp(): void
    {
        parent::setUp();
        $user = new User();
        $user->forceFill(['id'=>1, 'role'=>'Admin', 'BC'=>'001', 'Branch'=>'Test', 'username'=>'Test operator']);
        $this->actingAs($user);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
    }

    public function test_empty_reminder_page_has_shared_navigation_and_pagination(): void
    {
        $receipts = new LengthAwarePaginator([], 0, 25, 1, ['path'=>'/forfeit-reminders']);
        $html = view('forfeitReminderList', compact('receipts'))->render();
        $this->assertStringContainsString('sidebar-navigation.js', $html);
        $this->assertSame(1, substr_count($html, 'id="sidebar-menu"'));
        $this->assertSame(1, substr_count($html, 'sidebar-navigation.js'));
        $this->assertStringContainsString('Rows per page', $html);
        $this->assertStringContainsString('No receipts are currently', $html);
    }

    public function test_forfeited_receipt_renders_ticket_articles_and_expandable_history(): void
    {
        $receipt = new TPawnSum(['Receipt_Number'=>100, 'Ticket_Number'=>200, 'Invoice_Number'=>300, 'Customer_Name'=>'Example', 'isForfeit'=>1]);
        $receipt->setAttribute('receipt_history', [
            'customer'=>null,
            'financial'=>array_fill_keys(['principal','interest','service_charge','letter_charge','arrears_total','redemption_total'], 0),
            'details'=>collect(), 'timeline'=>collect(),
        ]);
        $receipt->setAttribute('forfeit_record', null);
        $receipt->setAttribute('stock_items', collect());
        $recipts = new LengthAwarePaginator([$receipt], 30, 25, 1, ['path'=>'/forfeitReceipt_List']);
        $status = 'all';
        $html = view('forfeitReceipt_List', compact('recipts', 'status'))->render();
        $this->assertStringContainsString('Receipt / Stock', $html);
        $this->assertStringContainsString('Stock: 300', $html);
        $this->assertStringContainsString('Ticket: 200', $html);
        $this->assertStringContainsString('Forfeited', $html);
        $this->assertStringContainsString('data-row-details="forfeit-details-', $html);
        $this->assertStringContainsString('class="detail-row" hidden', $html);
        $this->assertStringContainsString('Collapse all', $html);
        $this->assertStringContainsString('page=2', $html);
        $this->assertStringNotContainsString('Process Forfeit</a>', $html);
    }

    public function test_due_reminder_has_manual_review_checkbox_and_promise_form(): void
    {
        $receipt = new TPawnSum(['Receipt_Number'=>100, 'Final_date'=>'2026-01-01', 'letter_3_date'=>'2026-03-05']);
        $receipt->forceFill([
            'id'=>1, 'financial_breakdown'=>array_fill_keys(['principal','interest','service_charge','letter_charge','arrears_total','redemption_total'], 0),
            'current_promise'=>null, 'can_queue_forfeit'=>true, 'reminder_due_date'=>'2026-03-26',
            'current_customer_name'=>'Example', 'current_customer_phone'=>'',
        ]);
        $receipts = new LengthAwarePaginator([$receipt], 1, 25, 1, ['path'=>'/forfeit-reminders']);
        $html = view('forfeitReminderList', compact('receipts'))->render();
        $this->assertStringContainsString('data-row-details="reminder-details-', $html);
        $this->assertStringContainsString('name="confirm_forfeit" value="1" required', $html);
        $this->assertStringContainsString('Move to Forfeit List', $html);
        $this->assertStringContainsString('Save Promise', $html);
        $this->assertStringContainsString('2026-03-26', $html);
    }

    public function test_pawn_customer_panel_shows_the_telephone_as_a_visible_field(): void
    {
        $customer = new Customer();
        $customer->forceFill([
            'id'=>1, 'First_name'=>'Kamal', 'Last_name'=>'Perera',
            'Address_1'=>'Main Street', 'Contact_1'=>'0771234567',
        ]);
        $customer_get = collect([$customer]);
        $pending_count = 2;
        $redeemed_count = 3;
        $pendingPawnTotal = 15000;
        $Limit_Amount = 50000;
        $Limit_Pawn_Count = 5;

        $html = view('pawning_search_customer', compact(
            'customer_get', 'pending_count', 'redeemed_count', 'pendingPawnTotal',
            'Limit_Amount', 'Limit_Pawn_Count'
        ))->render();

        $this->assertStringContainsString('Telephone Number', $html);
        $this->assertStringContainsString('id="customer_contact_1"', $html);
        $this->assertStringContainsString('value="0771234567"', $html);
        $this->assertStringNotContainsString('type="hidden" name="customer_contact_1"', $html);
    }

    public function test_late_letter_table_removes_address_and_formats_every_date_without_time(): void
    {
        $makeReceipt = function (int $id, bool $first, bool $second): TPawnSum {
            $receipt = new TPawnSum([
                'Receipt_Number'=>99 + $id, 'Customer_NIC'=>'123V', 'Customer_Name'=>'Example',
                'Customer_Address'=>'ADDRESS MUST NOT APPEAR', 'Customer_Phone'=>'0771234567',
                'Receipt_Type'=>'A', 'Receipt_Date'=>'2026-01-02 08:45:12',
                'Final_date'=>'2026-02-02 09:15:20', 'Amount'=>1000,
                'is_letter_1'=>$first, 'is_letter_2'=>$second, 'is_letter_3'=>false,
                'letter_1_date'=>$first ? '2026-02-23 10:11:12' : null,
                'letter_2_date'=>$second ? '2026-03-16 12:13:14' : null,
            ]);
            $receipt->forceFill([
                'id'=>$id,
                'next_letter_due_date'=>Carbon::parse('2026-04-06 11:12:13'),
                'financial_breakdown'=>array_fill_keys(['interest','service_charge','letter_charge','arrears_total'], 0),
            ]);
            return $receipt;
        };
        $recipts = collect([
            $makeReceipt(1, false, false),
            $makeReceipt(2, true, false),
            $makeReceipt(3, true, true),
        ]);
        $receiptType = collect();
        $companyData = collect();

        $html = view('redeem_late_letter', compact('recipts', 'receiptType', 'companyData'))->render();

        $this->assertStringNotContainsString('ADDRESS MUST NOT APPEAR', $html);
        $this->assertStringNotContainsString('<th>Address</th>', $html);
        $this->assertStringNotContainsString('08:45:12', $html);
        $this->assertStringNotContainsString('11:12:13', $html);
        $this->assertStringNotContainsString('10:11:12', $html);
        $this->assertStringNotContainsString('12:13:14', $html);
        $this->assertStringContainsString('data-late-letter-details="letter-1-details-1', $html);
        $this->assertStringContainsString('data-late-letter-details="letter-2-details-2', $html);
        $this->assertStringContainsString('data-late-letter-details="letter-3-details-3', $html);
        $this->assertStringContainsString('class="late-letter-detail-row" hidden', $html);
        $this->assertStringContainsString('Letter / postage', $html);
        $this->assertStringContainsString('2026-01-02', $html);
        $this->assertStringContainsString('2026-02-02', $html);
        $this->assertStringContainsString('2026-02-23', $html);
        $this->assertStringContainsString('2026-03-16', $html);
        $this->assertStringContainsString('2026-04-06', $html);
    }
}
