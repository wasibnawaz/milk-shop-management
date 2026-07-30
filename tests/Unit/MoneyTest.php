<?php

namespace Tests\Unit;

use App\Support\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    /**
     * The original views called number_format() with no precision, rendering
     * 150.50 as "151" so displayed totals disagreed with stored values.
     */
    public function test_amounts_always_render_two_decimal_places(): void
    {
        $this->assertSame('Rs 150.50', Money::format(150.5));
        $this->assertSame('Rs 151.00', Money::format(151));
        $this->assertSame('Rs 0.00', Money::format(0));
        $this->assertSame('Rs 1,234,567.89', Money::format(1234567.89));
    }

    public function test_a_null_amount_renders_as_zero_rather_than_blank(): void
    {
        $this->assertSame('Rs 0.00', Money::format(null));
    }

    public function test_string_amounts_from_decimal_casts_are_handled(): void
    {
        // Eloquent's decimal cast returns strings, not floats.
        $this->assertSame('Rs 99.90', Money::format('99.90'));
    }

    public function test_the_symbol_can_be_suppressed(): void
    {
        $this->assertSame('150.50', Money::format(150.5, withSymbol: false));
    }

    public function test_the_currency_symbol_comes_from_config(): void
    {
        config(['shop.currency_symbol' => '₨']);

        $this->assertSame('₨ 10.00', Money::format(10));
    }

    public function test_quantities_trim_trailing_zeros_but_keep_real_precision(): void
    {
        $this->assertSame('2.5', Money::quantity(2.5));
        $this->assertSame('2.75', Money::quantity(2.75));
        $this->assertSame('2', Money::quantity(2.0));
        $this->assertSame('0.125', Money::quantity(0.125));
        $this->assertSame('1,000', Money::quantity(1000));
    }
}
