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
