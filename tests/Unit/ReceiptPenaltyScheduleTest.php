<?php

namespace Tests\Unit;

use App\Models\ForfeitReminderPromise;
use App\Models\TPawnSum;
use App\Services\ForfeitReminderService;
use App\Services\ReceiptLifecycleService;
use App\Services\ReceiptPenaltySchedule;
use Carbon\Carbon;
use Tests\TestCase;

class ReceiptPenaltyScheduleTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_custom_intervals_and_zero_first_interval(): void
    {
        $receipt = new TPawnSum(['Final_date'=>'2026-01-31', 'letter_1_days'=>0, 'letter_2_days'=>7, 'letter_3_days'=>14]);
        $schedule = new ReceiptPenaltySchedule();
        $this->assertSame('2026-01-31', $schedule->letterDueDate($receipt, 1)->toDateString());
        $this->assertSame('2026-02-07', $schedule->letterDueDate($receipt, 2)->toDateString());
        $this->assertSame('2026-02-21', $schedule->letterDueDate($receipt, 3)->toDateString());
    }

    public function test_reminder_waits_until_twenty_one_days_after_actual_third_letter(): void
    {
        $receipt = new TPawnSum(['Final_date'=>'2026-01-01', 'is_letter_3'=>1, 'letter_3_date'=>'2026-03-05']);
        $schedule = new ReceiptPenaltySchedule();
        $this->assertFalse($schedule->reminderIsDue($receipt, Carbon::parse('2026-03-25')));
        $this->assertTrue($schedule->reminderIsDue($receipt, Carbon::parse('2026-03-26')));
        $receipt->forfeit_reminder_days = 5;
        $this->assertTrue($schedule->reminderIsDue($receipt, Carbon::parse('2026-03-10')));
    }

    public function test_late_printing_waits_full_configured_interval_before_next_letter(): void
    {
        $receipt = new TPawnSum([
            'Final_date' => '2026-01-01',
            'letter_1_days' => 21, 'letter_2_days' => 21, 'letter_3_days' => 21,
            'letter_1_date' => '2026-02-10',
        ]);
        $schedule = new ReceiptPenaltySchedule();
        $this->assertSame('2026-01-22', $schedule->letterDueDate($receipt, 1)->toDateString());
        $this->assertSame('2026-03-03', $schedule->letterDueDate($receipt, 2)->toDateString());
        $receipt->letter_2_date = '2026-03-10';
        $this->assertSame('2026-03-31', $schedule->letterDueDate($receipt, 3)->toDateString());
    }

    public function test_silver_receipt_uses_the_same_expiry_and_letter_intervals(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Type' => 'SILVER', 'receiptname' => 'SILVER',
            'Final_date' => '2027-09-01', 'To_Date' => '2026-09-01', 'letter_1_days' => 0,
            'letter_2_days' => 21, 'letter_3_days' => 21,
        ]);
        $schedule = new ReceiptPenaltySchedule();
        $this->assertSame('2026-09-01', $schedule->letterDueDate($receipt, 1)->toDateString());
        $this->assertSame('2026-09-22', $schedule->letterDueDate($receipt, 2)->toDateString());
    }

    public function test_stale_silver_to_date_uses_current_cycle_instead_of_old_expiry(): void
    {
        $receipt = new TPawnSum([
            'Receipt_Type' => 'SILVER', 'Final_date' => '2027-09-01',
            'To_Date' => '2026-01-31', 'RePawning_date' => '2026-09-10',
            'period3' => 30, 'validPeriod' => 0, 'letter_1_days' => 0,
        ]);
        $this->assertSame('2026-10-10', (new ReceiptPenaltySchedule())->letterDueDate($receipt, 1)->toDateString());
    }

    public function test_letter_sql_uses_silver_saved_days_without_a_fixed_thirty_day_term(): void
    {
        \Illuminate\Support\Facades\Schema::shouldReceive('hasColumn')->andReturn(true);
        $sql = (new ReceiptPenaltySchedule())->letterDueSql(1);
        $this->assertStringContainsString('To_Date', $sql);
        $this->assertStringContainsString('NULLIF(validPeriod, 0)', $sql);
        $this->assertStringContainsString('NULLIF(period3, 0)', $sql);
        $this->assertStringNotContainsString('INTERVAL 30 DAY', $sql);
    }

    public function test_each_letter_tab_query_keeps_printed_rows_until_the_next_stage(): void
    {
        \Illuminate\Support\Facades\Schema::shouldReceive('hasColumn')->andReturn(true);
        $filter = new \ReflectionMethod(\App\Http\Controllers\RedeemLateLettersController::class, 'applyStageFilter');
        $filter->setAccessible(true);
        $controller = new \App\Http\Controllers\RedeemLateLettersController();
        $schedule = new ReceiptPenaltySchedule();

        foreach ([1, 2, 3] as $letter) {
            $sql = $filter->invoke($controller, TPawnSum::query(), $schedule, $letter)->toSql();
            $this->assertStringContainsString('is_letter_'.$letter, $sql);
            $this->assertStringContainsString('or', strtolower($sql));
            if ($letter === 3) $this->assertStringContainsString('letter_3_date', $sql);
        }
    }

    public function test_unsent_redeemed_and_forfeited_receipts_are_not_reminders(): void
    {
        Carbon::setTestNow('2026-03-26');
        $schedule = new ReceiptPenaltySchedule();
        $receipt = new TPawnSum(['is_letter_3'=>0, 'letter_3_date'=>'2026-03-05']);
        $this->assertFalse($schedule->reminderIsDue($receipt));
        $receipt->is_letter_3 = true;
        $receipt->IsRedeemed = true;
        $this->assertFalse($schedule->reminderIsDue($receipt));
        $receipt->IsRedeemed = false;
        $receipt->isForfeit = true;
        $this->assertFalse($schedule->reminderIsDue($receipt));
    }

    public function test_manual_forfeit_respects_promises_and_existing_queue(): void
    {
        Carbon::setTestNow('2026-03-26');
        $receipt = new TPawnSum(['is_letter_3'=>1, 'letter_3_date'=>'2026-03-05']);
        $service = new ForfeitReminderService(new ReceiptPenaltySchedule(), new ReceiptLifecycleService());
        $this->assertTrue($service->canQueue($receipt, null));
        $promise = new ForfeitReminderPromise(['status'=>'PENDING', 'promise_date'=>'2026-03-27']);
        $this->assertFalse($service->canQueue($receipt, $promise));
        $promise->promise_date = '2026-03-26';
        $this->assertFalse($service->canQueue($receipt, $promise));
        $promise->promise_date = '2026-03-25';
        $this->assertTrue($service->canQueue($receipt, $promise));
        $receipt->forfeit_queued_at = now();
        $this->assertFalse($service->canQueue($receipt, $promise));
    }

    public function test_interval_validation_rejects_negative_fractional_and_excessive_values(): void
    {
        \Illuminate\Support\Facades\Schema::shouldReceive('hasColumn')->andReturn(true);
        $method = new \ReflectionMethod(\App\Http\Controllers\ReceiptController::class, 'validatedIntervals');
        $method->setAccessible(true);
        $controller = new \App\Http\Controllers\ReceiptController();
        foreach ([-1, 1.5, 3651] as $invalid) {
            $request = new \Illuminate\Http\Request(array_fill_keys(ReceiptPenaltySchedule::FIELDS, $invalid));
            try {
                $method->invoke($controller, $request);
                $this->fail('Invalid interval accepted.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertArrayHasKey('letter_1_days', $exception->errors());
            }
        }
        $values = $method->invoke($controller, new \Illuminate\Http\Request(array_fill_keys(ReceiptPenaltySchedule::FIELDS, 21)));
        $this->assertSame(21, $values['forfeit_reminder_days']);
    }
}
