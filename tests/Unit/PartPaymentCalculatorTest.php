<?php

namespace Tests\Unit;

use App\Services\PartPaymentCalculator;
use PHPUnit\Framework\TestCase;

class PartPaymentCalculatorTest extends TestCase
{
    public function test_payment_reduces_principal_only_after_interest_and_charges(): void
    {
        $result = (new PartPaymentCalculator())->calculate(
            principal: 33795,
            interest: 837,
            serviceCharge: 0,
            letterCharge: 0,
            stampDuty: 0,
            enteredPayment: 6000
        );

        $this->assertSame(837.0, $result['paid_interest']);
        $this->assertSame(5163.0, $result['principal_paid']);
        $this->assertSame(28632.0, $result['new_principal']);
        $this->assertSame(0.0, $result['unpaid_charges']);
    }

    public function test_unpaid_interest_is_carried_without_increasing_principal(): void
    {
        $result = (new PartPaymentCalculator())->calculate(
            principal: 14000,
            interest: 350,
            serviceCharge: 60,
            letterCharge: 125,
            stampDuty: 0,
            enteredPayment: 300
        );

        $this->assertSame(0.0, $result['principal_paid']);
        $this->assertSame(14000.0, $result['new_principal']);
        $this->assertSame(235.0, $result['unpaid_charges']);
    }
}
