<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Core CRUD and rendering paths. Authorization lives in AuthorizationTest.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

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
            route('users.index'),
            route('users.create'),
            route('users.edit', $this->admin),
            route('profile.edit'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_a_sale_can_be_recorded_and_totals_are_derived(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('sales.store'), [
                'product_id' => $product->id,
                'quantity' => 2.5,
                'unit_rate' => 200,
                'payment_status' => PaymentStatus::Paid->value,
                'sale_date' => now()->toDateString(),
            ])
            ->assertRedirect(route('sales.index'))
            ->assertSessionHas('success');

        $sale = Sale::sole();

        // total_amount is derived server-side, never taken from the request.
        $this->assertEquals(500.00, (float) $sale->total_amount);
        $this->assertEquals(500.00, (float) $sale->amount_paid);
        $this->assertSame(PaymentStatus::Paid, $sale->payment_status);

        // The recording user is attributed automatically.
        $this->assertSame($this->admin->id, $sale->user_id);
    }

    public function test_a_sale_can_be_updated_and_soft_deleted(): void
    {
        $sale = Sale::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('sales.update', $sale), [
                'product_id' => $sale->product_id,
                'quantity' => 4,
                'unit_rate' => 50,
                'payment_status' => PaymentStatus::Unpaid->value,
                'sale_date' => now()->toDateString(),
            ])
            ->assertRedirect(route('sales.index'));

        $sale->refresh();
        $this->assertEquals(200.00, (float) $sale->total_amount);
        $this->assertSame(PaymentStatus::Unpaid, $sale->payment_status);

        $this->actingAs($this->admin)
            ->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'));

        $this->assertSoftDeleted($sale);
    }

    /**
     * The original controller called Milk::find() with no null check, so a
     * stale id threw a 500. Route-model binding must 404 instead.
     */
    public function test_a_missing_sale_returns_404_rather_than_500(): void
    {
        $this->actingAs($this->admin)->get(route('sales.edit', 99999))->assertNotFound();
        $this->actingAs($this->admin)->put(route('sales.update', 99999), [])->assertNotFound();
        $this->actingAs($this->admin)->delete(route('sales.destroy', 99999))->assertNotFound();
    }

    public function test_invalid_input_is_rejected_with_errors(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('sales.create'))
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

        $this->actingAs($this->admin)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertViewHas('revenue', 2000.00)
            ->assertViewHas('entries', 20);
    }

    public function test_a_product_with_sales_is_deactivated_rather_than_deleted(): void
    {
        $product = Product::factory()->create();
        Sale::factory()->for($product)->create();

        $this->actingAs($this->admin)
            ->delete(route('products.destroy', $product))
            ->assertSessionHas('info');

        $this->assertNotSoftDeleted($product);
        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_the_last_administrator_cannot_be_deleted(): void
    {
        $other = User::factory()->create(['role' => UserRole::Cashier]);

        // $this->admin is the only admin, so deleting them must be refused.
        $this->actingAs($other)->get(route('dashboard'))->assertOk();

        $second = User::factory()->admin()->create();

        $this->actingAs($second)
            ->delete(route('users.destroy', $this->admin))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($this->admin);

        // $second is now the last admin and cannot be removed by themselves.
        $this->actingAs($second)
            ->delete(route('users.destroy', $second))
            ->assertForbidden();
    }
}
