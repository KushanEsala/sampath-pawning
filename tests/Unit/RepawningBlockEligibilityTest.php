<?php

namespace Tests\Unit;

use App\Models\TPawnSum;
use App\Models\User;
use App\Http\Controllers\RepawningController;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptPaymentEligibility;
use App\Services\ReceiptTypeResolver;
use App\Services\RepawningCalculator;
use Illuminate\Database\MySqlConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class RepawningBlockEligibilityTest extends TestCase
{
    public function test_blocked_receipt_cannot_be_repawned(): void
    {
        $receipt = new TPawnSum(['is_blocked' => 1]);

        $this->expectException(ValidationException::class);
        ReceiptPaymentEligibility::assertRepawningAllowed($receipt);
    }

    public function test_unblocked_receipt_can_be_repawned(): void
    {
        $receipt = new TPawnSum(['is_blocked' => 0]);

        ReceiptPaymentEligibility::assertRepawningAllowed($receipt);
        $this->assertTrue(true);
    }

    /** @dataProvider lookupMethods */
    public function test_each_repawning_lookup_rejects_a_blocked_receipt(string $method, string $field): void
    {
        $connection = Mockery::mock(MySqlConnection::class)->makePartial();
        $connection->__construct(null, 'repawning_block_fixture', '', []);
        config([
            'database.default' => 'repawning_block_fixture',
            'database.connections.repawning_block_fixture' => ['driver' => 'repawning_block_fixture'],
        ]);
        DB::extend('repawning_block_fixture', fn () => $connection);
        $connection->shouldReceive('select')->once()->andReturnUsing(function ($sql) use ($field) {
            $this->assertStringContainsString('`'.$field.'` = ?', $sql);
            $this->assertStringContainsString('`IsRedeemed` = ?', $sql);
            $this->assertStringContainsString('`isForfeit` = ?', $sql);
            return [(object) ['id' => 1, 'BC' => '001', 'Receipt_Number' => 100, 'is_blocked' => 1]];
        });

        $user = new User();
        $user->forceFill(['id' => 1, 'username' => 'cashier', 'BC' => '001']);
        $this->actingAs($user);
        $request = Request::create('/', 'GET', ['search_receipt_no' => 100, 'search_invoice_no' => 100]);
        $response = (new RepawningController())->{$method}(
            $request,
            new ReceiptFinancialCalculator(),
            new ReceiptTypeResolver(),
            new RepawningCalculator()
        );

        $this->assertSame('blocked', $response->getData(true)['status']);
    }

    public function lookupMethods(): array
    {
        return [
            ['search', 'Receipt_Number'],
            ['searchTicket', 'Ticket_Number'],
            ['searchInvoice', 'Invoice_Number'],
        ];
    }
}
