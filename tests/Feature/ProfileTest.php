<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_update_their_own_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.test',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_the_email_must_be_unique_across_other_users(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => 'taken@example.test',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_keeping_your_own_email_is_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Same Email',
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_a_user_can_change_their_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'current-password',
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('a-brand-new-password', $user->fresh()->password));
    }

    /**
     * Without the current-password check a hijacked session could silently
     * take over the account.
     */
    public function test_changing_the_password_requires_the_current_one(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password'), [
                'current_password' => 'not-the-right-password',
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }

    public function test_a_user_can_close_their_own_account(): void
    {
        User::factory()->admin()->create(); // keep an admin around
        $user = User::factory()->cashier()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'current-password'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_closing_an_account_requires_the_password(): void
    {
        $user = User::factory()->cashier()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->assertNotSoftDeleted($user);
    }

    /** Losing the only admin would lock everyone out of staff management. */
    public function test_the_last_administrator_cannot_close_their_own_account(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'current-password']);

        $this->actingAs($admin)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'current-password'])
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($admin);
    }
}
