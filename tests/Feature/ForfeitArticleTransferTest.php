<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ForfeitArticleTransferService;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

// All reads and writes are intercepted. No PDO or real database is used.
class ForfeitArticleTransferTest extends TestCase
{
    private function fixture(bool $stale = false, bool $foreign = false): array
    {
        $writes = new \ArrayObject();
        $connection = Mockery::mock(MySqlConnection::class)->makePartial();
        $connection->__construct(null, 'fixture', '', []);
        $connection->shouldReceive('transaction')->andReturnUsing(fn ($callback) => $callback());
        $connection->shouldReceive('select')->andReturnUsing(function ($sql, $bindings) use ($stale, $foreign) {
            if (str_contains($sql, 'GET_LOCK')) return [(object) ['acquired'=>1]];
            if (str_contains($sql, 'RELEASE_LOCK')) return [(object) ['released'=>1]];
            if (str_contains($sql, 'from `t_pawn_sums`')) {
                $this->assertStringContainsString('for update', $sql);
                $this->assertSame([1, '001', 1, 0], $bindings);
                return $foreign ? [] : [(object) ['id'=>1, 'Receipt_Number'=>100, 'Invoice_Number'=>300, 'Ticket_Number'=>200, 'BC'=>'001', 'isForfeit'=>1, 'IsRedeemed'=>0, 'Customer_Name'=>'Example', 'Customer_NIC'=>'123V', 'Pawn_Amount'=>500]];
            }
            if (str_contains($sql, 'from `get_item_data`')) return [(object) ['Item_code'=>'10000']];
            if (str_contains($sql, 'from `items`')) {
                $this->assertSame(['001', 100, 0, 0], $bindings);
                return $stale ? [] : [(object) ['id'=>7, 'Receipt_Number'=>100, 'BC'=>'001', 'IntoItem'=>0, 'category'=>'Gold', 'Item_description'=>'Ring', 'Brand'=>'Good', 'Make'=>'22', 'purchasePrice'=>500, 'saleprice'=>600, 'Weight'=>2, 'Total_Weight'=>2, 'QTY'=>1]];
            }
            if (str_contains($sql, 'from `t_forfeit_sums`')) {
                $this->assertStringContainsString('order by `Forfeit_Number` desc', $sql);
                return [(object) ['Forfeit_Number'=>9, 'Forfeit_Date'=>'2026-09-01', 'Payable_Pawn_Amount'=>500, 'Payable_Interest'=>50, 'Paid_Interest'=>20]];
            }
            if (str_contains($sql, 'from `customers`')) return [];
            if (str_contains($sql, 'from `t_pawn_trans`')) return [(object) ['aggregate'=>100]];
            $this->fail('Unexpected SELECT: '.$sql);
        });
        $processor = Mockery::mock(MySqlProcessor::class)->makePartial();
        $processor->shouldReceive('processInsertGetId')->andReturnUsing(function ($query, $sql, $bindings) use ($writes) {
            $writes->append(['sql'=>$sql, 'bindings'=>$bindings]);
            return count($writes) + 10;
        });
        $connection->setPostProcessor($processor);
        $connection->shouldReceive('update')->andReturnUsing(function ($sql, $bindings) use ($writes) {
            $writes->append(['sql'=>$sql, 'bindings'=>$bindings]);
            $this->assertStringContainsString('`IntoItem` = ?', $sql);
            $this->assertSame(1, $bindings[0]);
            return 1;
        });
        config(['database.default'=>'transfer_fixture', 'database.connections.transfer_fixture'=>['driver'=>'transfer_fixture']]);
        DB::extend('transfer_fixture', fn () => $connection);
        Schema::shouldReceive('hasTable')->with('receipt_lifecycle_events')->andReturn(true);
        $operator = new User();
        $operator->forceFill(['id'=>1, 'username'=>'cashier', 'name'=>'Cashier Name', 'role'=>'Cashier', 'BC'=>'001', 'Branch'=>'Main']);
        return [$operator, $writes];
    }

    public function test_receipt_transfer_moves_articles_and_saves_invoice_and_operator_snapshot(): void
    {
        [$operator, $writes] = $this->fixture();
        $events = (new ForfeitArticleTransferService())->transfer([1], $operator);
        $this->assertCount(1, $events);
        $this->assertCount(3, $writes); // stock insert, source flag, lifecycle audit
        $this->assertStringContainsString('insert into `get_item_data`', $writes[0]['sql']);
        $this->assertContains('10001', $writes[0]['bindings']);
        $this->assertStringContainsString('insert into `receipt_lifecycle_events`', $writes[2]['sql']);
        $json = collect($writes[2]['bindings'])->first(fn ($value) => is_string($value) && str_starts_with($value, '{'));
        $snapshot = json_decode($json, true);
        $this->assertSame(300, $snapshot['stock_number']);
        $this->assertSame('cashier', $snapshot['operator']['username']);
        $this->assertSame(500, $snapshot['capital_outstanding']);
        $this->assertSame(100, $snapshot['capital_paid']);
    }

    public function test_already_transferred_receipt_cannot_create_duplicate_stock(): void
    {
        [$operator, $writes] = $this->fixture(true);
        try { (new ForfeitArticleTransferService())->transfer([1], $operator); $this->fail('Expected rejection'); }
        catch (ValidationException $exception) { $this->assertCount(0, $writes); }
    }

    public function test_other_branch_or_non_forfeited_selection_is_rejected_without_writes(): void
    {
        [$operator, $writes] = $this->fixture(false, true);
        try { (new ForfeitArticleTransferService())->transfer([1], $operator); $this->fail('Expected rejection'); }
        catch (ValidationException $exception) { $this->assertCount(0, $writes); }
    }
}
