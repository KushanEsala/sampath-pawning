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
        $this->assertSame(0.0, $result['capitalized_interest']);
        $this->assertSame(0.0, $result['unpaid_charges']);
    }

    public function test_payment_below_interest_capitalizes_only_the_unpaid_interest(): void
    {
        $result = (new PartPaymentCalculator())->calculate(5000, 200, 0, 0, 0, 150);

        $this->assertSame(150.0, $result['paid_interest']);
        $this->assertSame(0.0, $result['principal_paid']);
        $this->assertSame(50.0, $result['capitalized_interest']);
        $this->assertSame(5050.0, $result['new_principal']);
        $this->assertSame(0.0, $result['unpaid_charges']);
        $this->assertSame(5200.0, $result['redemption_total']);
    }

    public function test_non_interest_charges_remain_separate_from_capitalized_interest(): void
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
        $this->assertSame(14050.0, $result['new_principal']);
        $this->assertSame(50.0, $result['capitalized_interest']);
        $this->assertSame(185.0, $result['unpaid_charges']);
    }

    public function test_discounted_interest_is_not_capitalized_again(): void
    {
        $result = (new PartPaymentCalculator())->calculate(5000, 200, 0, 0, 0, 150, 50);

        $this->assertSame(100.0, $result['payment_received']);
        $this->assertSame(100.0, $result['paid_interest']);
        $this->assertSame(50.0, $result['capitalized_interest']);
        $this->assertSame(5050.0, $result['new_principal']);
    }

    public function test_second_payment_on_interest_payment_day_reduces_capital_without_service_charge(): void
    {
        $first = (new PartPaymentCalculator())->calculate(10000, 250, 0, 0, 0, 250);
        $this->assertSame(250.0, $first['paid_interest']);
        $this->assertSame(0.0, $first['unpaid_charges']);

        // The controller starts the next interest cycle on the following day.
        $second = (new PartPaymentCalculator())->calculate($first['new_principal'], 0, 0, 0, 0, 1000);
        $this->assertSame(0.0, $second['paid_interest']);
        $this->assertSame(1000.0, $second['principal_paid']);
        $this->assertSame(9000.0, $second['new_principal']);
    }
}
