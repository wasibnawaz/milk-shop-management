<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Sale model derives total_amount and payment_status on every write.
 * These are the money rules, so they are pinned down directly.
 */
class SaleCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_total_is_quantity_times_rate(): void
    {
        $sale = Sale::factory()->create(['quantity' => 2.5, 'unit_rate' => 200]);

        $this->assertEquals(500.00, (float) $sale->total_amount);
    }

    public function test_the_total_is_recalculated_on_update(): void
    {
        $sale = Sale::factory()->create(['quantity' => 1, 'unit_rate' => 100]);

        $sale->update(['quantity' => 3]);

        $this->assertEquals(300.00, (float) $sale->fresh()->total_amount);
    }

    /**
     * total_amount is not fillable, so a crafted request cannot store a total
     * that disagrees with quantity x rate.
     */
    public function test_a_submitted_total_cannot_override_the_calculated_one(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1,
            'unit_rate' => 100,
            'total_amount' => 999999,
        ]);

        $this->assertEquals(100.00, (float) $sale->total_amount);
    }

    public function test_fractional_quantities_round_to_two_decimals(): void
    {
        $sale = Sale::factory()->create(['quantity' => 0.333, 'unit_rate' => 100]);

        $this->assertEquals(33.30, (float) $sale->total_amount);
    }

    public function test_status_is_paid_when_the_full_amount_is_paid(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1, 'unit_rate' => 100, 'amount_paid' => 100,
        ]);

        $this->assertSame(PaymentStatus::Paid, $sale->payment_status);
        $this->assertSame(0.0, $sale->outstanding);
    }

    public function test_status_is_unpaid_when_nothing_is_paid(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1, 'unit_rate' => 100, 'amount_paid' => 0,
        ]);

        $this->assertSame(PaymentStatus::Unpaid, $sale->payment_status);
        $this->assertSame(100.0, $sale->outstanding);
    }

    public function test_status_is_partial_when_some_is_paid(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1, 'unit_rate' => 100, 'amount_paid' => 40,
        ]);

        $this->assertSame(PaymentStatus::Partial, $sale->payment_status);
        $this->assertSame(60.0, $sale->outstanding);
    }

    public function test_overpayment_is_clamped_to_the_sale_total(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1, 'unit_rate' => 100, 'amount_paid' => 5000,
        ]);

        $this->assertEquals(100.00, (float) $sale->amount_paid);
        $this->assertSame(PaymentStatus::Paid, $sale->payment_status);
        $this->assertSame(0.0, $sale->outstanding);
    }

    public function test_a_negative_payment_is_clamped_to_zero(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1, 'unit_rate' => 100, 'amount_paid' => -50,
        ]);

        $this->assertEquals(0.00, (float) $sale->amount_paid);
        $this->assertSame(PaymentStatus::Unpaid, $sale->payment_status);
    }

    /**
     * A sale marked paid, then edited upward, must stop being "paid" —
     * otherwise the outstanding figure silently loses money.
     */
    public function test_increasing_the_quantity_of_a_paid_sale_makes_it_partial(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1, 'unit_rate' => 100, 'amount_paid' => 100,
        ]);

        $this->assertSame(PaymentStatus::Paid, $sale->payment_status);

        $sale->update(['quantity' => 2]);
        $sale->refresh();

        $this->assertEquals(200.00, (float) $sale->total_amount);
        $this->assertSame(PaymentStatus::Partial, $sale->payment_status);
        $this->assertSame(100.0, $sale->outstanding);
    }

    public function test_a_zero_rate_sale_is_treated_as_paid(): void
    {
        $sale = Sale::factory()->create([
            'quantity' => 1, 'unit_rate' => 0, 'amount_paid' => 0,
        ]);

        $this->assertEquals(0.00, (float) $sale->total_amount);
        $this->assertSame(PaymentStatus::Paid, $sale->payment_status);
    }

    public function test_soft_deleted_sales_are_excluded_from_totals_but_recoverable(): void
    {
        $sale = Sale::factory()->create(['quantity' => 1, 'unit_rate' => 100]);

        $sale->delete();

        $this->assertSame(0, Sale::count());
        $this->assertEquals(0, Sale::sum('total_amount'));

        $sale->restore();

        $this->assertSame(1, Sale::count());
        $this->assertEquals(100, Sale::sum('total_amount'));
    }
}
