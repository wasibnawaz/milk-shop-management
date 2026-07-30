<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_an_admin_can_create_a_staff_account(): void
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'New Cashier',
                'email' => 'cashier@example.test',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
                'role' => UserRole::Cashier->value,
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'cashier@example.test')->sole();

        $this->assertSame(UserRole::Cashier, $user->role);
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }

    public function test_passwords_must_be_confirmed_and_long_enough(): void
    {
        $this->actingAs($this->admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Mismatch',
                'email' => 'mismatch@example.test',
                'password' => 'short',
                'password_confirmation' => 'different',
                'role' => UserRole::Cashier->value,
            ])
            ->assertSessionHasErrors('password');

        $this->assertSame(1, User::count());
    }

    public function test_duplicate_emails_are_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($this->admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Copycat',
                'email' => 'taken@example.test',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
                'role' => UserRole::Cashier->value,
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_an_invalid_role_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Wannabe',
                'email' => 'wannabe@example.test',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
                'role' => 'superuser',
            ])
            ->assertSessionHasErrors('role');
    }

    /** A blank password field on update means "leave it alone". */
    public function test_updating_without_a_password_keeps_the_existing_one(): void
    {
        $user = User::factory()->create(['password' => 'original-password']);
        $originalHash = $user->password;

        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => 'Renamed',
                'email' => $user->email,
                'password' => '',
                'password_confirmation' => '',
                'role' => $user->role->value,
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame('Renamed', $user->name);
        $this->assertSame($originalHash, $user->password);
    }

    public function test_an_admin_can_reset_another_users_password(): void
    {
        $user = User::factory()->create(['password' => 'original-password']);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
                'role' => $user->role->value,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_deactivating_a_user_is_persisted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                // is_active omitted entirely, as an unchecked checkbox would be
            ])
            ->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_an_admin_cannot_delete_their_own_account_from_the_staff_screen(): void
    {
        User::factory()->admin()->create(); // so the "last admin" rule is not the blocker

        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->admin))
            ->assertForbidden();

        $this->assertNotSoftDeleted($this->admin);
    }

    /** Soft delete keeps attribution on sales the user recorded. */
    public function test_deleting_a_user_preserves_their_recorded_sales(): void
    {
        $cashier = User::factory()->cashier()->create();
        $sale = Sale::factory()->create(['user_id' => $cashier->id]);

        $this->actingAs($this->admin)
            ->delete(route('users.destroy', $cashier))
            ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted($cashier);
        $this->assertNotSoftDeleted($sale);
        $this->assertSame($cashier->id, $sale->fresh()->user_id);
    }

    public function test_a_deleted_users_email_can_be_reused(): void
    {
        $user = User::factory()->create(['email' => 'recycled@example.test']);
        $user->delete();

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'Replacement',
                'email' => 'recycled@example.test',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
                'role' => UserRole::Cashier->value,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors();
    }
}
