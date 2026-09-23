<?php

namespace Tests\Unit;

use App\Models\Recei_Add;
use App\Models\TPawnSum;
use App\Services\RepawningCalculator;
use PHPUnit\Framework\TestCase;

class RepawningCalculatorTest extends TestCase
{
    public function test_it_builds_the_expected_month_by_month_repayment_values(): void
    {
        $receipt = new TPawnSum();
        $receipt->Pawn_Amount = 30000;
        $receipt->Amount = 30000;
        $receipt->Receipt_Type = 'A';
        $receipt->Valid_Period = 9;

        $detail = (object) ['Karatage' => ' 22k ', 'Weight' => 8];
        $karatage = (object) ['descrption' => '22K', 'pawningrate' => 38800];

        $currentType = new Recei_Add();
        $currentType->stampduty = 0;
        $currentType->rate3 = 2.5;

        $repawnType = new Recei_Add();
        $repawnType->rate3 = 2.5;

        $preview = (new RepawningCalculator())->preview(
            $receipt,
            collect([$detail]),
            collect([$karatage]),
            [
                'interest' => 486.05,
                'service_charge' => 125,
                'letter_charge' => 0,
            ],
            $currentType,
            $repawnType
        );

        $this->assertSame(38800.0, $preview['article_value']);
        $this->assertSame(970.0, $preview['monthly_interest']);
        $this->assertSame(8188.95, $preview['month_options'][1]);
        $this->assertSame(7218.95, $preview['month_options'][2]);
        $this->assertSame(428.95, $preview['month_options'][9]);
        $this->assertNull($preview['month_options'][10]);
    }
}
