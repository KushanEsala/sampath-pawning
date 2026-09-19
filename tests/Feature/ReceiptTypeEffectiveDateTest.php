<?php

namespace Tests\Feature;

use App\Http\Controllers\ReceiptController;
use App\Models\Recei_Add;
use App\Models\TPawnSum;
use App\Models\User;
use App\Services\ReceiptFinancialCalculator;
use App\Services\ReceiptTypeResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReceiptTypeEffectiveDateTest extends TestCase
{
    private ReceiptTypeResolver $resolver;
    private ReceiptFinancialCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ReceiptTypeResolver();
        $this->calculator = new ReceiptFinancialCalculator($this->resolver);

        $user = new User();
        $user->forceFill(['id' => 1, 'username' => 'admin', 'BC' => '001']);
        $this->actingAs($user);
    }

    public function test_resolver_prioritizes_receipt_snapshot_guaranteeing_zero_historical_change(): void
    {
        $receipt = new TPawnSum();
        $receipt->forceFill([
            'Receipt_Number' => 'P1001',
            'Receipt_Type' => 'A',
            'receiptname' => 'A',
            'Pawn_Amount' => 100000,
            'Pawn_Date' => '2022-01-01',
            'Final_date' => '2023-01-01',
            'rate1' => 1.25,
            'period1' => 15,
            'rate2' => 2.00,
            'period2' => 20,
            'rate3' => 2.50,
            'period3' => 30,
            'validPeriod' => 365,
            'service_charge' => 150.00,
            'Postage_charge' => 50.00,
            's_charge_less' => 100.00,
            's_charge_greater' => 1.50,
            'letter_1_days' => 21,
            'letter_2_days' => 21,
            'letter_3_days' => 21,
            'forfeit_reminder_days' => 21,
        ]);

        $resolved = $this->resolver->resolveForReceipt($receipt);

        $this->assertSame('A', $resolved->receiptname);
        $this->assertEquals(1.25, (float) $resolved->rate1);
        $this->assertEquals(2.00, (float) $resolved->rate2);
        $this->assertEquals(2.50, (float) $resolved->rate3);
        $this->assertEquals(15, (int) $resolved->period1);
        $this->assertEquals(20, (int) $resolved->period2);
        $this->assertEquals(30, (int) $resolved->period3);
        $this->assertEquals(150.00, (float) $resolved->service_charge);

        // Calculator also uses the snapshot
        $calc30 = $this->calculator->calculate($receipt, '2022-01-30'); // 30 days: Jan 1 to Jan 30
        $this->assertEquals(2500.00, $calc30['interest']);

        // 31 days: Jan 1 to Jan 31 includes 1 day prorated beyond 30 days
        $calc31 = $this->calculator->calculate($receipt, '2022-01-31');
        $this->assertEquals(2583.33, $calc31['interest']);
    }

    public function test_resolver_resolves_correct_version_by_date(): void
    {
        // Clean up any test records
        Recei_Add::where('receiptname', 'TEST_TYPE')->delete();

        // Create Version 1: 2023-01-01 to 2023-12-31 (rate3 = 2.5)
        $v1 = Recei_Add::create([
            'receiptname' => 'TEST_TYPE',
            'effective_from' => '2023-01-01',
            'effective_to' => '2023-12-31',
            'is_active' => 0,
            'rate1' => 1.0,
            'period1' => 15,
            'rate2' => 2.0,
            'period2' => 20,
            'rate3' => 2.5,
            'period3' => 30,
            'validPeriod' => 365,
            'service_charge' => 100,
            'documentCharges' => 0,
            'stampduty' => 0,
            'pawn_amount' => 50000,
            'Postage_charge' => 50,
            's_charge_less' => 50,
            's_charge_greater' => 1,
            'letter_1_days' => 21,
            'letter_2_days' => 21,
            'letter_3_days' => 21,
            'forfeit_reminder_days' => 21,
        ]);

        // Create Version 2: 2024-01-01 onwards (rate3 = 3.5)
        $v2 = Recei_Add::create([
            'receiptname' => 'TEST_TYPE',
            'effective_from' => '2024-01-01',
            'effective_to' => null,
            'is_active' => 1,
            'rate1' => 1.5,
            'period1' => 15,
            'rate2' => 2.5,
            'period2' => 20,
            'rate3' => 3.5,
            'period3' => 30,
            'validPeriod' => 365,
            'service_charge' => 200,
            'documentCharges' => 0,
            'stampduty' => 0,
            'pawn_amount' => 50000,
            'Postage_charge' => 75,
            's_charge_less' => 75,
            's_charge_greater' => 2,
            'letter_1_days' => 21,
            'letter_2_days' => 21,
            'letter_3_days' => 21,
            'forfeit_reminder_days' => 21,
        ]);

        // Look up by 2023 date
        $resolved2023 = $this->resolver->resolveByDate('TEST_TYPE', '2023-06-15');
        $this->assertNotNull($resolved2023);
        $this->assertEquals(2.5, (float) $resolved2023->rate3);
        $this->assertEquals(100.0, (float) $resolved2023->service_charge);

        // Look up by 2024 date
        $resolved2024 = $this->resolver->resolveByDate('TEST_TYPE', '2024-06-15');
        $this->assertNotNull($resolved2024);
        $this->assertEquals(3.5, (float) $resolved2024->rate3);
        $this->assertEquals(200.0, (float) $resolved2024->service_charge);

        // Fallback for receipt with missing snapshot
        $oldPawnWithoutSnapshot = new TPawnSum();
        $oldPawnWithoutSnapshot->forceFill([
            'Receipt_Number' => 'P9999',
            'Receipt_Type' => 'TEST_TYPE',
            'Pawn_Amount' => 100000,
            'Pawn_Date' => '2023-05-10',
            'Final_date' => '2024-05-10',
            'rate1' => null, // missing snapshot
        ]);

        $resolvedFromDb = $this->resolver->resolveForReceipt($oldPawnWithoutSnapshot);
        $this->assertEquals(2.5, (float) $resolvedFromDb->rate3);

        // Clean up
        Recei_Add::where('receiptname', 'TEST_TYPE')->delete();
    }

    public function test_receipt_controller_update_creates_version_and_archives_previous(): void
    {
        Recei_Add::where('receiptname', 'VER_TEST')->delete();

        // Create initial version
        $initial = Recei_Add::create([
            'receiptname' => 'VER_TEST',
            'effective_from' => '2023-01-01',
            'effective_to' => null,
            'is_active' => 1,
            'rate1' => 1.0,
            'period1' => 15,
            'rate2' => 2.0,
            'period2' => 20,
            'rate3' => 2.5,
            'period3' => 30,
            'validPeriod' => 365,
            'service_charge' => 100,
            'documentCharges' => 0,
            'stampduty' => 0,
            'pawn_amount' => 50000,
            'Postage_charge' => 50,
            's_charge_less' => 50,
            's_charge_greater' => 1,
            'letter_1_days' => 21,
            'letter_2_days' => 21,
            'letter_3_days' => 21,
            'forfeit_reminder_days' => 21,
        ]);

        $controller = new ReceiptController();

        // Update via controller with a future effective date
        $effectiveDate = '2024-06-01';
        $request = Request::create('/receipt/update', 'POST', [
            'up_id' => $initial->id,
            'up_receiptname' => 'VER_TEST',
            'up_effective_from' => $effectiveDate,
            'up_rate1' => 1.5,
            'up_rate2' => 2.5,
            'up_rate3' => 3.8,
            'up_period1' => 15,
            'up_period2' => 20,
            'up_period3' => 30,
            'up_valid' => 365,
            'up_s_char_less' => 80,
            'up_s_char_grea' => 2,
            'up_Postage_charge' => 60,
            'up_service_charge' => 250,
            'up_letter_1_days' => 21,
            'up_letter_2_days' => 21,
            'up_letter_3_days' => 21,
            'up_forfeit_reminder_days' => 21,
        ]);

        $response = $controller->update($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Refresh initial record: should now be closed
        $initial->refresh();
        $this->assertFalse((bool) $initial->is_active);
        $this->assertEquals('2024-05-31', $initial->effective_to->toDateString());

        // Find newly created version
        $newVersion = Recei_Add::where('receiptname', 'VER_TEST')
            ->where('is_active', 1)
            ->first();

        $this->assertNotNull($newVersion);
        $this->assertNotEquals($initial->id, $newVersion->id);
        $this->assertEquals('2024-06-01', $newVersion->effective_from->toDateString());
        $this->assertNull($newVersion->effective_to);
        $this->assertEquals(3.8, (float) $newVersion->rate3);
        $this->assertEquals(250, (float) $newVersion->service_charge);

        // Clean up
        Recei_Add::where('receiptname', 'VER_TEST')->delete();
    }
}
