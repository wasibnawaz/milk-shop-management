<?php

namespace App\Support;

class Money
{
    /**
     * Format an amount for display, e.g. "Rs 1,250.50".
     *
     * Always renders 2 decimal places. The previous implementation called
     * number_format() with no precision argument, which rounded 150.50 to
     * "151" and made displayed totals disagree with stored values.
     */
    public static function format(float|int|string|null $amount, bool $withSymbol = true): string
    {
        $formatted = number_format((float) $amount, 2);

        return $withSymbol
            ? config('shop.currency_symbol').' '.$formatted
            : $formatted;
    }

    /**
     * Format a quantity, trimming trailing zeros so 2.500 reads as "2.5"
     * but 2.750 stays "2.75".
     */
    public static function quantity(float|int|string|null $quantity): string
    {
        $value = (float) $quantity;

        return rtrim(rtrim(number_format($value, 3, '.', ','), '0'), '.');
    }
}
