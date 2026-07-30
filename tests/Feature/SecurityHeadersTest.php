<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_hardening_headers_are_present_on_guest_pages(): void
    {
        $this->get(route('login'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_hardening_headers_are_present_when_signed_in(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    /** The export streams, so headers must not break it. */
    public function test_the_csv_export_still_streams_correctly(): void
    {
        $product = Product::factory()->create();
        Sale::factory()->for($product)->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('sales.export'));

        $response->assertOk();
        $this->assertNotEmpty($response->streamedContent());
    }

    /** HSTS must not be advertised over plain HTTP. */
    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get(route('login'))->assertHeaderMissing('Strict-Transport-Security');
    }
}
