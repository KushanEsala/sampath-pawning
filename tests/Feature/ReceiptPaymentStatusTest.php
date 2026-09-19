<?php

namespace Tests\Feature;

use App\Http\Controllers\OldsystemRedeemController;
use App\Http\Controllers\PawnController;
use App\Http\Controllers\PawningPartPaymentController;
use App\Http\Controllers\RedeemController;
use App\Models\TPawnSum;
use App\Models\User;
use App\Services\ReceiptArticleStatus;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptHistoryService;
use App\Services\ReceiptLifecycleService;
use Illuminate\Database\MySqlConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

// Intercept every query/transaction: these tests never use the project database.
class ReceiptPaymentStatusTest extends TestCase
{
    private function connection(): MySqlConnection
    {
        $connection = Mockery::mock(MySqlConnection::class)->makePartial();
        $connection->__construct(null, 'fixture', '', []);
        config(['database.default'=>'payment_status_fixture', 'database.connections.payment_status_fixture'=>['driver'=>'payment_status_fixture']]);
        DB::extend('payment_status_fixture', fn () => $connection);
        $user = new User();
        $user->forceFill(['id'=>1, 'username'=>'cashier', 'BC'=>'001']);
        $this->actingAs($user);
        return $connection;
    }

    /** @dataProvider searches */
    public function test_every_payment_lookup_filters_both_closed_statuses(string $operation, string $field, string $table): void
    {
        $connection = $this->connection();
        $connection->shouldReceive('select')->once()->andReturnUsing(function ($sql, $bindings) use ($field, $table) {
            $this->assertStringContainsString('from `'.$table.'`', $sql);
            $this->assertStringContainsString('`'.$field.'` = ?', $sql);
            $this->assertStringContainsString('`IsRedeemed` = ?', $sql);
            $this->assertStringContainsString('`isForfeit` = ?', $sql);
            $this->assertContains('001', $bindings);
            $this->assertSame(2, count(array_filter($bindings, fn ($value) => $value === 0)));
            return [];
        });
        $request = Request::create('/', 'GET', ['search_receipt_no'=>100, 'search_invoice_no'=>300]);
        $calculator = Mockery::mock(ReceiptFinancialCalculator::class);
        $response = match ($operation) {
            'redeem_receipt' => (new RedeemController())->search($request, $calculator),
            'redeem_ticket' => (new RedeemController())->searchTicket($request, $calculator),
            'redeem_invoice' => (new RedeemController())->searchInvoice($request),
            'part_receipt' => (new PawningPartPaymentController())->PartpaymentSearch(Request::create('/', 'GET', ['search_receipt_no'=>100]), $calculator),
            'part_ticket' => (new PawningPartPaymentController())->PartpaymentTicketSearch(Request::create('/', 'GET', ['search_receipt_no'=>200]), $calculator),
            'part_invoice' => (new PawningPartPaymentController())->PartpaymentInvoiceSearch($request, $calculator),
        };
        $this->assertSame('not_found', $response->getData(true)['status']);
    }

    public function searches(): array
    {
        return [
            ['redeem_receipt','Receipt_Number','t_pawn_sums'], ['redeem_ticket','Ticket_Number','t_pawn_sums'],
            ['redeem_invoice','Invoice_Number','t_opening_pawn_sums'], ['part_receipt','Receipt_Number','t_pawn_sums'],
            ['part_ticket','Ticket_Number','t_pawn_sums'], ['part_invoice','Invoice_Number','t_pawn_sums'],
        ];
    }

    /** @dataProvider companions */
    public function test_payment_article_and_history_requests_do_not_return_closed_receipts(string $operation): void
    {
        $connection = $this->connection();
        $connection->shouldReceive('select')->once()->andReturnUsing(function ($sql) {
            $this->assertStringContainsString('`IsRedeemed` = ?', $sql);
            $this->assertStringContainsString('`isForfeit` = ?', $sql);
            return [(object) ['exists'=>0]];
        });
        $request = Request::create('/', 'GET', ['search_receipt_no'=>100, 'payment_workflow'=>1]);
        $history = Mockery::mock(ReceiptHistoryService::class);
        $response = match ($operation) {
            'articles' => (new PawnController())->getArticleDetails($request),
            'redeem_history' => (new PawnController())->getCustomerDetails($request, $history),
            'part_history' => (new PawningPartPaymentController())->PartpaymentHistory($request, $history),
            'old_articles' => (new OldsystemRedeemController())->getArticleDetailsold($request),
        };
        $this->assertSame(['status'=>'not_found','data'=>[]], $response->getData(true));
    }

    public function companions(): array { return [['articles'],['redeem_history'],['part_history'],['old_articles']]; }

    public function test_general_article_history_remains_available_outside_payment_workflow(): void
    {
        $connection = $this->connection();
        $connection->shouldReceive('select')->once()->andReturnUsing(function ($sql, $bindings) {
            $this->assertStringContainsString('from `t_pawn_details`', $sql);
            $this->assertSame([100,'001'], $bindings);
            return [(object) ['Receipt_Number'=>100,'IsRedeemed'=>1,'Articles'=>'Ring']];
        });
        $response = (new PawnController())->getArticleDetails(Request::create('/', 'GET', ['search_receipt_no'=>100]));
        $this->assertSame('success', $response->getData(true)['status']);
    }

    public function test_opening_receipt_article_status_is_scoped_to_its_own_table_and_branch(): void
    {
        $connection = $this->connection();
        Schema::shouldReceive('hasColumn')->once()->with('t_opening_pawn_details', 'isForfeit')->andReturn(true);
        $connection->shouldReceive('update')->once()->andReturnUsing(function ($sql, $bindings) {
            $this->assertStringContainsString('update `t_opening_pawn_details`', $sql);
            $this->assertSame([1,0], array_slice($bindings, 0, 2));
            $this->assertSame(['001',100], array_slice($bindings, -2));
            return 1;
        });
        $receipt = new \App\Models\TOpeningPawnSum();
        $receipt->forceFill(['Receipt_Number'=>100, 'BC'=>'001', 'IsRedeemed'=>1, 'isForfeit'=>0]);
        ReceiptArticleStatus::sync($receipt, 'Opening_Pawn');
    }

    /** @dataProvider submissions */
    public function test_closed_or_foreign_receipt_submission_rolls_back_before_any_payment_is_saved(string $operation, string $type): void
    {
        $connection = $this->connection();
        $connection->shouldReceive('beginTransaction')->once();
        $connection->shouldReceive('rollBack')->once();
        $connection->shouldReceive('select')->once()->andReturnUsing(function ($sql) {
            $this->assertStringContainsString('for update', $sql);
            $this->assertStringContainsString('`IsRedeemed` = ?', $sql);
            $this->assertStringContainsString('`isForfeit` = ?', $sql);
            return [];
        });
        $connection->shouldNotReceive('insert');
        $connection->shouldNotReceive('update');
        $request = Request::create('/', 'POST', ['receipt_number'=>100, 'pawn_receipt_type'=>$type]);
        $calculator = Mockery::mock(ReceiptFinancialCalculator::class);
        $lifecycle = Mockery::mock(ReceiptLifecycleService::class);
        $response = match ($operation) {
            'redeem' => (new RedeemController())->store($request, $lifecycle, $calculator),
            'part' => (new PawningPartPaymentController())->AddPartPayment($request, $calculator, $lifecycle),
            'old' => (new OldsystemRedeemController())->StoreOld($request),
        };
        $this->assertTrue($response->getSession()->has('errors'));
        $this->assertStringContainsString('Receipt unavailable', $response->getSession()->get('error'));
    }

    public function submissions(): array { return [['redeem','Pawn'],['redeem','Opening_Pawn'],['part','Pawn'],['old','Pawn']]; }

    /** @dataProvider articleStatuses */
    public function test_article_status_tracks_parent_without_using_missing_optional_column(int $redeemed, int $forfeit, bool $hasColumn): void
    {
        $connection = $this->connection();
        Schema::shouldReceive('hasColumn')->once()->with('t_pawn_details', 'isForfeit')->andReturn($hasColumn);
        $connection->shouldReceive('update')->once()->andReturnUsing(function ($sql, $bindings) use ($redeemed, $forfeit, $hasColumn) {
            $this->assertStringContainsString('update `t_pawn_details`', $sql);
            $this->assertSame($hasColumn, str_contains($sql, '`isForfeit` = ?'));
            $this->assertSame($hasColumn ? [$redeemed,$forfeit] : [$redeemed], array_slice($bindings, 0, $hasColumn ? 2 : 1));
            $this->assertSame(['001',100], array_slice($bindings, -2));
            return 1;
        });
        $receipt = new TPawnSum(['Receipt_Number'=>100, 'BC'=>'001', 'IsRedeemed'=>$redeemed, 'isForfeit'=>$forfeit]);
        ReceiptArticleStatus::sync($receipt);
    }

    public function articleStatuses(): array { return [[1,0,true],[0,1,true],[0,0,true],[0,1,false]]; }
}
