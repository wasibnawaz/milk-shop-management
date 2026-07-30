<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Previously "PKR" was hardcoded into the Blade templates. Centralised here
    | so the shop can be rebranded or localised without touching views.
    |
    */

    'currency' => env('SHOP_CURRENCY', 'PKR'),

    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'Rs'),

    /*
    |--------------------------------------------------------------------------
    | Display
    |--------------------------------------------------------------------------
    */

    'per_page' => 15,

    'per_page_options' => [15, 25, 50, 100],

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    |
    | Upper bounds enforced by the sale form. These exist so validation fails
    | with a readable message instead of the database throwing on an
    | out-of-range DECIMAL column.
    |
    */

    'max_quantity' => 99999.999,

    'max_unit_rate' => 99999999.99,

];
