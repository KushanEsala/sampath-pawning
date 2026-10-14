<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockCheckReportTabsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('t_pawn_sums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('BC');
            $table->integer('Receipt_Number');
            $table->string('Invoice_Number');
            $table->date('Receipt_Date');
            $table->decimal('Amount', 12, 2);
            $table->decimal('Interest', 12, 2)->default(0);
            $table->boolean('IsRedeemed')->default(false);
            $table->boolean('isForfeit')->default(false);
            $table->boolean('checkbox_stock')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('t_opening_pawn_sums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('BC');
            $table->integer('Receipt_Number');
            $table->string('Invoice_Number');
            $table->date('Receipt_Date');
            $table->decimal('Amount', 12, 2);
            $table->decimal('Interest', 12, 2)->default(0);
            $table->boolean('IsRedeemed')->default(false);
            $table->boolean('isForfeit')->default(false);
        });

        $this->actingAs((new User())->forceFill([
            'id' => 1, 'username' => 'stock-admin', 'role' => 'Admin', 'BC' => '001',
        ]));
    }

    private function seedStock(): void
    {
        DB::table('t_pawn_sums')->insert([
            ['id' => 1, 'BC' => '001', 'Receipt_Number' => 101, 'Invoice_Number' => 'STK-PENDING',
                'Receipt_Date' => '2026-10-10', 'Amount' => 1000, 'checkbox_stock' => 0,
                'IsRedeemed' => 0],
            ['id' => 2, 'BC' => '001', 'Receipt_Number' => 102, 'Invoice_Number' => 'STK-CHECKED',
                'Receipt_Date' => '2026-10-11', 'Amount' => 2000, 'checkbox_stock' => 1,
                'IsRedeemed' => 0],
            ['id' => 3, 'BC' => '002', 'Receipt_Number' => 103, 'Invoice_Number' => 'OTHER-BRANCH',
                'Receipt_Date' => '2026-10-11', 'Amount' => 3000, 'checkbox_stock' => 1,
                'IsRedeemed' => 0],
            ['id' => 4, 'BC' => '001', 'Receipt_Number' => 105, 'Invoice_Number' => 'REDEEMED-STOCK',
                'Receipt_Date' => '2026-10-11', 'Amount' => 4000, 'checkbox_stock' => 1,
                'IsRedeemed' => 1],
        ]);
        DB::table('t_opening_pawn_sums')->insert([
            'id' => 1, 'BC' => '001', 'Receipt_Number' => 104,
            'Invoice_Number' => 'OPENING-STOCK', 'Receipt_Date' => '2026-10-10', 'Amount' => 500,
        ]);
    }

    public function test_tabs_show_separate_stock_and_keep_date_filters(): void
    {
        $this->seedStock();
        $dates = '?from_date=2026-10-01&to_date=2026-10-14';

        $this->get('/StockCheckreport'.$dates)
            ->assertOk()->assertSee('STK-PENDING')->assertSee('OPENING-STOCK')
            ->assertDontSee('STK-CHECKED')->assertDontSee('OTHER-BRANCH')
            ->assertSee('1,500.00')->assertSee('name="view" value="pending"', false)
            ->assertSee('from_date=2026-10-01', false);

        $this->get('/StockCheckreport'.$dates.'&view=checked')
            ->assertOk()->assertSee('STK-CHECKED')
            ->assertDontSee('STK-PENDING')->assertDontSee('OPENING-STOCK')
            ->assertSee('2,000.00')->assertSee('name="view" value="checked"', false)
            ->assertSee('Reset All Checked in Branch');

        $this->get('/StockCheckreport?view=checked')
            ->assertOk()->assertSee('STK-CHECKED')->assertSee('Showing all checked records');
    }

    public function test_check_uncheck_and_reset_move_rows_between_tabs_without_touching_other_branches(): void
    {
        $this->seedStock();
        $dates = '?from_date=2026-10-01&to_date=2026-10-14';

        $this->post('/update-checkbox', ['record_id' => 1, 'checked' => 1])->assertOk();
        $this->get('/StockCheckreport'.$dates)->assertDontSee('STK-PENDING');
        $this->get('/StockCheckreport'.$dates.'&view=checked')->assertSee('STK-PENDING');

        $this->post('/update-checkbox', ['record_id' => 1, 'checked' => 0])->assertOk();
        $this->get('/StockCheckreport'.$dates)->assertSee('STK-PENDING');
        $this->get('/StockCheckreport'.$dates.'&view=checked')->assertDontSee('STK-PENDING');

        $this->post('/reset-checkbox-stock')->assertOk();
        $this->assertSame(0, (int) DB::table('t_pawn_sums')->where('id', 2)->value('checkbox_stock'));
        $this->assertSame(1, (int) DB::table('t_pawn_sums')->where('id', 3)->value('checkbox_stock'));
        $this->assertSame(1, (int) DB::table('t_pawn_sums')->where('id', 4)->value('checkbox_stock'));
    }
}
