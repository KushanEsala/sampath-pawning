<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Services\CustomerPawnPolicy;
use PHPUnit\Framework\TestCase;

class CustomerPawnPolicyTest extends TestCase
{
    public function test_legacy_inactive_status_and_positive_limits_survive_branchless_duplicates(): void
    {
        $legacy = new Customer(['NIC' => '732762027V', 'Status' => 0,
            'Limit_Amount' => 400000, 'Limit_Pawn_Count' => 3]);
        $branch = new Customer(['NIC' => '732762027v', 'Status' => 1,
            'Limit_Amount' => null, 'Limit_Pawn_Count' => null]);

        $policy = (new CustomerPawnPolicy())->policy(collect([$legacy, $branch]));

        $this->assertFalse($policy['active']);
        $this->assertSame(400000.0, $policy['amount_limit']);
        $this->assertSame(3, $policy['count_limit']);
    }

    public function test_null_and_zero_limits_mean_unlimited_and_null_status_is_active(): void
    {
        $policy = (new CustomerPawnPolicy())->policy(collect([
            new Customer(['Status' => null, 'Limit_Amount' => null, 'Limit_Pawn_Count' => 0]),
        ]));

        $this->assertTrue($policy['active']);
        $this->assertSame(0.0, $policy['amount_limit']);
        $this->assertSame(0, $policy['count_limit']);
    }

    public function test_conflicting_positive_limits_take_the_stricter_value(): void
    {
        $policy = (new CustomerPawnPolicy())->policy(collect([
            new Customer(['Status' => 1, 'Limit_Amount' => 50000, 'Limit_Pawn_Count' => 5]),
            new Customer(['Status' => 1, 'Limit_Amount' => 40000, 'Limit_Pawn_Count' => 3]),
        ]));

        $this->assertSame(40000.0, $policy['amount_limit']);
        $this->assertSame(3, $policy['count_limit']);
    }

    public function test_new_pawns_check_projected_count_and_amount(): void
    {
        $policy = ['amount_limit' => 10000.0, 'count_limit' => 2];
        $exposure = ['pawn_amount' => 8000.0, 'pawn_count' => 1];

        $this->assertNull(CustomerPawnPolicy::violation($policy, $exposure, 2000.0, 1));
        $this->assertSame(['amount' => 'Customer pawn amount limit exceeded.'],
            CustomerPawnPolicy::violation($policy, $exposure, 2000.01, 1));
        $this->assertSame(['customer_nic' => 'Customer pawn count limit exceeded.'],
            CustomerPawnPolicy::violation($policy, $exposure, 1, 2));
    }

    public function test_repawn_rechecks_existing_exposure_without_adding_a_receipt(): void
    {
        $policy = ['amount_limit' => 10000.0, 'count_limit' => 2];
        $exposure = ['pawn_amount' => 9500.0, 'pawn_count' => 2];

        $this->assertNull(CustomerPawnPolicy::violation($policy, $exposure, 500.0, 0));
        $this->assertNotNull(CustomerPawnPolicy::violation($policy, $exposure, 500.01, 0));
    }

    public function test_new_customer_limits_cannot_be_below_active_capital_or_receipt_count(): void
    {
        $exposure = ['pawn_amount' => 574000.0, 'pawn_count' => 3];

        $errors = CustomerPawnPolicy::limitFloorErrors($exposure, 400000.0, 2);
        $this->assertArrayHasKey('limit_amount', $errors);
        $this->assertArrayHasKey('limit_pawn_count', $errors);
        $this->assertStringContainsString('574,000.00', $errors['limit_amount']);
        $this->assertStringContainsString('3 active receipt(s)', $errors['limit_pawn_count']);

        $this->assertSame([], CustomerPawnPolicy::limitFloorErrors($exposure, 574000.0, 3));
        $this->assertSame([], CustomerPawnPolicy::limitFloorErrors($exposure, 0.0, 0));
        $this->assertSame([], CustomerPawnPolicy::limitFloorErrors($exposure, null, null));
    }
}
