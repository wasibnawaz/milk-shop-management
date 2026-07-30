<?php

namespace Tests\Feature;

use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_reach_staff_management(): void
    {
        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('users.index'))->assertForbidden();

        $this->actingAs(User::factory()->manager()->create())
            ->get(route('users.index'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('users.index'))->assertOk();
    }

    public function test_cashiers_cannot_manage_the_catalogue(): void
    {
        $cashier = User::factory()->cashier()->create();
        $product = Product::factory()->create();
        $dealer = Dealer::factory()->create();

        $this->actingAs($cashier)->get(route('products.create'))->assertForbidden();
        $this->actingAs($cashier)->get(route('products.edit', $product))->assertForbidden();
        $this->actingAs($cashier)->delete(route('products.destroy', $product))->assertForbidden();

        $this->actingAs($cashier)->get(route('dealers.create'))->assertForbidden();
        $this->actingAs($cashier)->delete(route('dealers.destroy', $dealer))->assertForbidden();

        // But they can still see the lists, which the sale form depends on.
        $this->actingAs($cashier)->get(route('products.index'))->assertOk();
        $this->actingAs($cashier)->get(route('dealers.index'))->assertOk();
    }

    public function test_managers_can_manage_the_catalogue(): void
    {
        $manager = User::factory()->manager()->create();
        $product = Product::factory()->create();

        $this->actingAs($manager)->get(route('products.create'))->assertOk();
        $this->actingAs($manager)->get(route('products.edit', $product))->assertOk();
    }

    public function test_a_cashier_may_only_edit_their_own_sale_and_only_on_the_same_day(): void
    {
        $cashier = User::factory()->cashier()->create();
        $colleague = User::factory()->cashier()->create();

        $own = Sale::factory()->create(['user_id' => $cashier->id]);
        $someoneElses = Sale::factory()->create(['user_id' => $colleague->id]);

        $this->actingAs($cashier)->get(route('sales.edit', $own))->assertOk();
        $this->actingAs($cashier)->get(route('sales.edit', $someoneElses))->assertForbidden();

        // Yesterday's entry is out of reach; a supervisor has to adjust it.
        $stale = Sale::factory()->create(['user_id' => $cashier->id]);
        $stale->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();

        $this->actingAs($cashier)->get(route('sales.edit', $stale))->assertForbidden();
    }

    public function test_cashiers_cannot_delete_sales_but_managers_can(): void
    {
        $sale = Sale::factory()->create();

        $this->actingAs(User::factory()->cashier()->create())
            ->delete(route('sales.destroy', $sale))->assertForbidden();

        $this->assertNotSoftDeleted($sale);

        $this->actingAs(User::factory()->manager()->create())
            ->delete(route('sales.destroy', $sale))->assertRedirect();

        $this->assertSoftDeleted($sale);
    }

    public function test_an_admin_cannot_change_their_own_role_or_deactivate_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'cashier',
                'is_active' => '0',
            ])
            ->assertSessionHasErrors(['role', 'is_active']);

        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_every_role_can_record_a_sale(): void
    {
        $product = Product::factory()->create();

        foreach (['admin', 'manager', 'cashier'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->post(route('sales.store'), [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_rate' => 100,
                    'payment_status' => 'paid',
                    'sale_date' => now()->toDateString(),
                ])
                ->assertRedirect(route('sales.index'));
        }

        $this->assertSame(3, Sale::count());
    }
}
