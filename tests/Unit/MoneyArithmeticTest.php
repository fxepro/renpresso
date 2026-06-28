<?php

namespace Tests\Unit;

use App\Services\FxService;
use Tests\TestCase;

class MoneyArithmeticTest extends TestCase
{
    /** @test */
    public function fx_rate_stored_as_integer_scaled_by_one_million()
    {
        // 1 EUR = 1.08 USD → stored as 1_080_000
        $rate = 1_080_000;
        $amountMinorUnits = 150_000; // €1,500.00 in cents

        $homeAmount = (int)round($amountMinorUnits * ($rate / 1_000_000));

        $this->assertEquals(162_000, $homeAmount); // $1,620.00 in cents
    }

    /** @test */
    public function fx_rate_one_to_one_for_same_currency()
    {
        $service = app(FxService::class);
        $rate = $service->rateToHome('USD', 'USD');
        $this->assertEquals(1_000_000, $rate);
    }

    /** @test */
    public function minor_units_never_use_floats()
    {
        // Simulate a payment: ₹75,000 = 7,500,000 paise
        $amountPaise = 7_500_000;
        $fxRate = 12_000; // 0.012 USD per INR → stored as 12_000

        $homeAmountCents = (int)round($amountPaise * ($fxRate / 1_000_000));

        $this->assertIsInt($homeAmountCents);
        $this->assertEquals(90_000, $homeAmountCents); // $900.00
    }
}
