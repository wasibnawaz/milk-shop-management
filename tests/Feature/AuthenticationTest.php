<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    public function test_guests_are_redirected_to_login_from_every_protected_route(): void
    {
        foreach ([
            route('dashboard'),
            route('sales.index'),
            route('sales.create'),
            route('products.index'),
            route('dealers.index'),
            route('users.index'),
            route('profile.edit'),
        ] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_the_login_screen_renders(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign In', false);
    }

    public function test_a_user_can_sign_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_a_user_cannot_sign_in_with_a_bad_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_deactivated_user_cannot_sign_in(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'password']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * Deactivation must take effect on the next request, not the next login —
     * otherwise a live "remember me" session keeps working after revocation.
     */
    public function test_a_user_deactivated_mid_session_is_signed_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_repeated_failures_are_rate_limited(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        // The per-email lockout trips after 5 failures.
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password', // correct now, but locked out
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    public function test_a_user_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_there_is_no_public_registration_route(): void
    {
        $this->assertFalse(
            app('router')->getRoutes()->hasNamedRoute('register'),
            'This is a private back office — self-registration must not exist.'
        );
    }

    public function test_password_reset_does_not_leak_whether_an_account_exists(): void
    {
        User::factory()->create(['email' => 'real@example.test']);

        $known = $this->post(route('password.email'), ['email' => 'real@example.test']);
        $unknown = $this->post(route('password.email'), ['email' => 'nobody@example.test']);

        // Both paths must produce an indistinguishable response.
        $known->assertRedirect();
        $unknown->assertRedirect();
        $this->assertSame(
            $known->getSession()->get('success') ?? $known->getSession()->get('info'),
            $unknown->getSession()->get('success') ?? $unknown->getSession()->get('info')
        );
    }
}
