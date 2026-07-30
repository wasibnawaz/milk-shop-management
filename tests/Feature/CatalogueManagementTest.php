<?php

namespace Tests\Feature;

use App\Enums\ProductUnit;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogueManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->manager()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    public function test_a_product_can_be_created(): void
    {
        $this->actingAs($this->manager)
            ->post(route('products.store'), [
                'name' => 'Fresh Cow Milk',
                'unit' => ProductUnit::Litre->value,
                'default_rate' => 220.50,
                'is_active' => '1',
            ])
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $product = Product::sole();

        $this->assertSame('Fresh Cow Milk', $product->name);
        $this->assertSame(ProductUnit::Litre, $product->unit);
        $this->assertEquals(220.50, (float) $product->default_rate);
        $this->assertTrue($product->is_active);
    }

    public function test_duplicate_product_names_are_rejected(): void
    {
        Product::factory()->create(['name' => 'Desi Ghee']);

        $this->actingAs($this->manager)
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => 'Desi Ghee',
                'unit' => ProductUnit::Kilogram->value,
                'default_rate' => 3000,
            ])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Product::count());
    }

    public function test_a_product_rejects_a_negative_rate(): void
    {
        $this->actingAs($this->manager)
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => 'Bad Product',
                'unit' => ProductUnit::Litre->value,
                'default_rate' => -10,
            ])
            ->assertSessionHasErrors('default_rate');

        $this->assertSame(0, Product::count());
    }

    public function test_an_invalid_unit_is_rejected(): void
    {
        $this->actingAs($this->manager)
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => 'Odd Product',
                'unit' => 'furlongs',
                'default_rate' => 10,
            ])
            ->assertSessionHasErrors('unit');
    }

    public function test_a_product_can_be_updated(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->manager)
            ->put(route('products.update', $product), [
                'name' => 'New Name',
                'unit' => ProductUnit::Packet->value,
                'default_rate' => 75,
                'is_active' => '0',
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame('New Name', $product->name);
        $this->assertFalse($product->is_active);
    }

    /** Renaming a product must not trip its own uniqueness rule. */
    public function test_a_product_can_keep_its_own_name_on_update(): void
    {
        $product = Product::factory()->create(['name' => 'Butter']);

        $this->actingAs($this->manager)
            ->put(route('products.update', $product), [
                'name' => 'Butter',
                'unit' => $product->unit->value,
                'default_rate' => 999,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_a_product_without_sales_is_deleted_outright(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)
            ->delete(route('products.destroy', $product))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($product);
    }

    /** Inactive products stay in reports but must not be selectable. */
    public function test_inactive_products_are_hidden_from_the_sale_form(): void
    {
        $active = Product::factory()->create(['name' => 'Active Milk']);
        $inactive = Product::factory()->inactive()->create(['name' => 'Retired Milk']);

        $options = $this->actingAs($this->manager)
            ->get(route('sales.create'))
            ->viewData('products');

        $this->assertTrue($options->contains('id', $active->id));
        $this->assertFalse($options->contains('id', $inactive->id));
    }

    public function test_a_sale_cannot_reference_an_inactive_or_missing_product(): void
    {
        $this->actingAs($this->manager)
            ->from(route('sales.create'))
            ->post(route('sales.store'), [
                'product_id' => 999999,
                'quantity' => 1,
                'unit_rate' => 100,
                'payment_status' => 'paid',
                'sale_date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Dealers
    |--------------------------------------------------------------------------
    */

    public function test_a_dealer_can_be_created(): void
    {
        $this->actingAs($this->manager)
            ->post(route('dealers.store'), [
                'name' => 'Bilal Dairy Farm',
                'phone' => '0300 1234567',
                'address' => 'Sahiwal Road',
                'is_active' => '1',
            ])
            ->assertRedirect(route('dealers.index'));

        $this->assertSame('Bilal Dairy Farm', Dealer::sole()->name);
    }

    public function test_a_dealer_rejects_a_malformed_phone_number(): void
    {
        $this->actingAs($this->manager)
            ->from(route('dealers.create'))
            ->post(route('dealers.store'), [
                'name' => 'Dodgy Dealer',
                'phone' => 'call me maybe',
            ])
            ->assertSessionHasErrors('phone');
    }

    public function test_a_dealer_may_be_created_without_optional_details(): void
    {
        $this->actingAs($this->manager)
            ->post(route('dealers.store'), ['name' => 'Minimal Dealer'])
            ->assertSessionHasNoErrors();

        $dealer = Dealer::sole();

        $this->assertNull($dealer->phone);
        $this->assertNull($dealer->address);
    }

    /** dealer_id is nullOnDelete: the sale survives, detached. */
    public function test_deleting_a_dealer_keeps_their_sales(): void
    {
        $dealer = Dealer::factory()->create();
        $sale = Sale::factory()->for($dealer)->create();

        $this->actingAs($this->manager)
            ->delete(route('dealers.destroy', $dealer))
            ->assertRedirect(route('dealers.index'));

        $this->assertSoftDeleted($dealer);
        $this->assertNotSoftDeleted($sale);
    }

    public function test_the_sale_form_accepts_no_dealer(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)
            ->post(route('sales.store'), [
                'product_id' => $product->id,
                'dealer_id' => '',
                'quantity' => 1,
                'unit_rate' => 100,
                'payment_status' => 'paid',
                'sale_date' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull(Sale::sole()->dealer_id);
    }
}
