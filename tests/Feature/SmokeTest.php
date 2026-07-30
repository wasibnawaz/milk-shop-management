<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone 1 verification: every route renders and the core CRUD paths work.
 * Exhaustive coverage lands in Milestone 5.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_renders(): void
    {
        $product = Product::factory()->create();
        $dealer = Dealer::factory()->create();
        $sale = Sale::factory()->for($product)->for($dealer)->create();

        $routes = [
            route('dashboard'),
            route('sales.index'),
            route('sales.create'),
            route('sales.edit', $sale),
            route('products.index'),
            route('products.create'),
            route('products.edit', $product),
            route('dealers.index'),
            route('dealers.create'),
            route('dealers.edit', $dealer),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_a_sale_can_be_recorded_and_totals_are_derived(): void
    {
        $product = Product::factory()->create();

        $response = $this->post(route('sales.store'), [
            'product_id' => $product->id,
            'quantity' => 2.5,
            'unit_rate' => 200,
            'payment_status' => PaymentStatus::Paid->value,
            'sale_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('sales.index'))->assertSessionHas('success');

        $sale = Sale::sole();

        // total_amount is derived server-side, never taken from the request.
        $this->assertEquals(500.00, (float) $sale->total_amount);
        $this->assertEquals(500.00, (float) $sale->amount_paid);
        $this->assertSame(PaymentStatus::Paid, $sale->payment_status);
    }

    public function test_a_sale_can_be_updated_and_soft_deleted(): void
    {
        $sale = Sale::factory()->create();

        $this->put(route('sales.update', $sale), [
            'product_id' => $sale->product_id,
            'quantity' => 4,
            'unit_rate' => 50,
            'payment_status' => PaymentStatus::Unpaid->value,
            'sale_date' => now()->toDateString(),
        ])->assertRedirect(route('sales.index'));

        $sale->refresh();
        $this->assertEquals(200.00, (float) $sale->total_amount);
        $this->assertSame(PaymentStatus::Unpaid, $sale->payment_status);

        $this->delete(route('sales.destroy', $sale))->assertRedirect(route('sales.index'));

        $this->assertSoftDeleted($sale);
    }

    /**
     * The original controller called Milk::find() with no null check, so a
     * stale id threw a 500. Route-model binding must 404 instead.
     */
    public function test_a_missing_sale_returns_404_rather_than_500(): void
    {
        $this->get(route('sales.edit', 99999))->assertNotFound();
        $this->put(route('sales.update', 99999), [])->assertNotFound();
        $this->delete(route('sales.destroy', 99999))->assertNotFound();
    }

    public function test_invalid_input_is_rejected_with_errors(): void
    {
        $product = Product::factory()->create();

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'product_id' => $product->id,
                'quantity' => 0,          // must be > 0
                'unit_rate' => -50,       // negative rate corrupted totals before
                'payment_status' => PaymentStatus::Paid->value,
                'sale_date' => now()->addDay()->toDateString(), // future-dated
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors(['quantity', 'unit_rate', 'sale_date']);

        $this->assertSame(0, Sale::count());
    }

    public function test_index_totals_cover_the_whole_filtered_set_not_just_one_page(): void
    {
        $product = Product::factory()->create();

        // More rows than fit on a page, each worth exactly 100.
        Sale::factory()->count(20)->for($product)->create([
            'quantity' => 1,
            'unit_rate' => 100,
            'amount_paid' => 100,
        ]);

        $this->get(route('sales.index'))
            ->assertOk()
            ->assertViewHas('revenue', 2000.00)
            ->assertViewHas('entries', 20);
    }

    public function test_a_product_with_sales_is_deactivated_rather_than_deleted(): void
    {
        $product = Product::factory()->create();
        Sale::factory()->for($product)->create();

        $this->delete(route('products.destroy', $product))->assertSessionHas('info');

        $this->assertNotSoftDeleted($product);
        $this->assertFalse($product->fresh()->is_active);
    }
}
