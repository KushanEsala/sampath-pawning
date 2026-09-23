<?php

namespace Tests\Unit;

use App\Models\TOpeningPawnSum;
use App\Models\TPawnSum;
use App\Services\ReceiptPaymentEligibility;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReceiptBlockEligibilityTest extends TestCase
{
    public function test_blocked_pawn_and_opening_pawn_cannot_be_redeemed(): void
    {
        foreach ([new TPawnSum(), new TOpeningPawnSum()] as $receipt) {
            $receipt->is_blocked = true;
            try {
                ReceiptPaymentEligibility::assertRedemptionAllowed($receipt);
                $this->fail('Blocked receipt was accepted for redemption.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('receipt_number', $exception->errors());
            }
        }
    }

    public function test_unblocked_receipt_can_be_redeemed(): void
    {
        $receipt = new TPawnSum();
        $receipt->is_blocked = false;
        ReceiptPaymentEligibility::assertRedemptionAllowed($receipt);
        $this->assertFalse($receipt->is_blocked);
    }
}
