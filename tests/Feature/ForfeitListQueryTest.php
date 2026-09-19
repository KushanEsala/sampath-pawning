<?php

namespace Tests\Feature;

use App\Http\Controllers\ForfeitReceiptController;
use App\Models\User;
use App\Services\ReceiptHistoryService;
use Illuminate\Database\MySqlConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ForfeitListQueryTest extends TestCase
{
    /** @dataProvider statusFilters */
    public function test_status_filter_can_change_between_all_pending_and_forfeited(string $status, array $expectedBindings): void
    {
        $connection = Mockery::mock(MySqlConnection::class)->makePartial();
        $connection->__construct(null, 'fixture', '', []);
        $pawnListRead = false;
        $connection->shouldReceive('select')->andReturnUsing(function ($sql, $bindings) use (&$pawnListRead, $expectedBindings) {
            if (str_contains($sql, 'from `t_pawn_sums`')) {
                $pawnListRead = true;
                $this->assertSame($expectedBindings, $bindings);
                return [(object) ['aggregate' => 0]];
            }
            $this->fail('Unexpected query: '.$sql);
        });
        config(['database.default' => 'forfeit_fixture', 'database.connections.forfeit_fixture' => ['driver' => 'forfeit_fixture']]);
        DB::extend('forfeit_fixture', fn () => $connection);
        Schema::shouldReceive('hasColumn')->once()->with('t_pawn_sums', 'forfeit_queued_at')->andReturn(true);

        $user = new User();
        $user->forceFill(['id' => 1, 'BC' => '001']);
        $this->actingAs($user);
        $history = Mockery::mock(ReceiptHistoryService::class);

        $request = Request::create('/forfeitReceipt_List', 'GET', ['status' => $status]);
        $view = (new ForfeitReceiptController())->ForfeitReceiptList($request, $history);

        $this->assertTrue($pawnListRead);
        $this->assertSame($status, $view->getData()['status']);
        $this->assertCount(0, $view->getData()['recipts']);
    }

    public function statusFilters(): array
    {
        return [
            'all' => ['all', ['001', 1, 0, 0]],
            'pending' => ['pending', ['001', 1, 0, 0, 0]],
            'forfeited' => ['forfeited', ['001', 1, 0, 0, 1]],
        ];
    }

    public function test_populated_list_orders_forfeits_by_existing_forfeit_number(): void
    {
        // Intercept every SELECT: no connection to the user's database is made.
        $connection = Mockery::mock(MySqlConnection::class)->makePartial();
        $connection->__construct(null, 'fixture', '', []);
        $forfeitRead = false;
        $connection->shouldReceive('select')->andReturnUsing(function ($sql, $bindings) use (&$forfeitRead) {
            if (str_contains($sql, 'from `t_pawn_sums`')) {
                if (str_contains($sql, 'count(*)')) return [(object) ['aggregate' => 1]];
                return [(object) ['id' => 1, 'Receipt_Number' => 8476, 'BC' => '001', 'isForfeit' => 1]];
            }
            if (str_contains($sql, 'from `t_forfeit_sums`')) {
                $forfeitRead = true;
                $this->assertStringContainsString('order by `Forfeit_Number` desc', $sql);
                $this->assertStringNotContainsString('`id`', $sql);
                $this->assertSame(['001', 8476], $bindings);
                return [(object) ['Receipt_Number' => 8476, 'BC' => '001', 'Forfeit_Number' => 12]];
            }
            if (str_contains($sql, 'from `items`')) return [];
            $this->fail('Unexpected query: '.$sql);
        });
        config(['database.default' => 'forfeit_fixture', 'database.connections.forfeit_fixture' => ['driver' => 'forfeit_fixture']]);
        DB::extend('forfeit_fixture', fn () => $connection);
        Schema::shouldReceive('hasColumn')->once()->with('t_pawn_sums', 'forfeit_queued_at')->andReturn(true);

        $user = new User();
        $user->forceFill(['id' => 1, 'BC' => '001']);
        $this->actingAs($user);
        $history = Mockery::mock(ReceiptHistoryService::class);
        $history->shouldReceive('build')->once()->andReturn([]);

        $view = (new ForfeitReceiptController())->ForfeitReceiptList(Request::create('/forfeitReceipt_List'), $history);

        $this->assertTrue($forfeitRead);
        $this->assertSame(12, $view->getData()['recipts']->first()->forfeit_record->Forfeit_Number);
    }
}
