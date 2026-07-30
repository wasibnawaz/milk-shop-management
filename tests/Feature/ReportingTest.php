<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->admin()->create();
    }

    public function test_sorting_reorders_results(): void
    {
        $product = Product::factory()->create();

        $cheap = Sale::factory()->for($product)->create(['quantity' => 1, 'unit_rate' => 10]);
        $dear = Sale::factory()->for($product)->create(['quantity' => 1, 'unit_rate' => 900]);

        $ascending = $this->actingAs($this->user)
            ->get(route('sales.index', ['sort' => 'total_amount', 'direction' => 'asc']))
            ->viewData('sales');

        $this->assertSame($cheap->id, $ascending->first()->id);

        $descending = $this->actingAs($this->user)
            ->get(route('sales.index', ['sort' => 'total_amount', 'direction' => 'desc']))
            ->viewData('sales');

        $this->assertSame($dear->id, $descending->first()->id);
    }

    /**
     * Sort input reaches orderBy(), so an unrecognised column must never be
     * passed through to the database.
     */
    public function test_an_unknown_sort_column_falls_back_to_the_default(): void
    {
        Sale::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->get(route('sales.index', ['sort' => 'total_amount); drop table sales;--']))
            ->assertOk();

        $this->assertSame(3, Sale::count());
    }

    public function test_filters_narrow_the_result_set_and_the_totals(): void
    {
        $milk = Product::factory()->create(['name' => 'Fresh Milk']);
        $ghee = Product::factory()->create(['name' => 'Desi Ghee']);

        Sale::factory()->for($milk)->create(['quantity' => 1, 'unit_rate' => 100, 'amount_paid' => 100]);
        Sale::factory()->for($ghee)->create(['quantity' => 1, 'unit_rate' => 500, 'amount_paid' => 500]);

        $this->actingAs($this->user)
            ->get(route('sales.index', ['product_id' => $milk->id]))
            ->assertOk()
            ->assertViewHas('revenue', 100.0)
            ->assertViewHas('entries', 1);
    }

    public function test_date_range_filtering_uses_the_sale_date(): void
    {
        $product = Product::factory()->create();

        Sale::factory()->for($product)->create([
            'sale_date' => now()->subMonths(2)->toDateString(),
            'quantity' => 1, 'unit_rate' => 100,
        ]);
        Sale::factory()->for($product)->create([
            'sale_date' => now()->toDateString(),
            'quantity' => 1, 'unit_rate' => 250,
        ]);

        $this->actingAs($this->user)
            ->get(route('sales.index', [
                'from' => now()->subDays(7)->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertViewHas('revenue', 250.0)
            ->assertViewHas('entries', 1);
    }

    public function test_search_matches_across_related_records(): void
    {
        $product = Product::factory()->create(['name' => 'Buffalo Milk']);
        Sale::factory()->for($product)->create(['customer_name' => 'Ayesha']);
        Sale::factory()->create(['customer_name' => 'Bilal']);

        $results = $this->actingAs($this->user)
            ->get(route('sales.index', ['search' => 'Buffalo']))
            ->viewData('sales');

        $this->assertCount(1, $results);
        $this->assertSame('Ayesha', $results->first()->customer_name);
    }

    public function test_csv_export_streams_the_filtered_set(): void
    {
        $product = Product::factory()->create(['name' => 'Desi Ghee']);
        Sale::factory()->for($product)->create(['quantity' => 2, 'unit_rate' => 1000, 'amount_paid' => 2000]);
        Sale::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('sales.export', ['product_id' => $product->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $lines = array_values(array_filter(explode("\n", trim($csv))));

        // Header plus exactly one filtered row.
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('Desi Ghee', $csv);
        $this->assertStringContainsString('2000.00', $csv);
    }

    public function test_the_dashboard_respects_the_selected_period(): void
    {
        $product = Product::factory()->create();

        Sale::factory()->for($product)->create([
            'sale_date' => now()->toDateString(),
            'quantity' => 1, 'unit_rate' => 300,
        ]);
        Sale::factory()->for($product)->create([
            'sale_date' => now()->subYear()->toDateString(),
            'quantity' => 1, 'unit_rate' => 900,
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard', ['period' => 'today']))
            ->assertOk()
            ->assertViewHas('revenue', 300.0);

        $this->actingAs($this->user)
            ->get(route('dashboard', ['period' => 'all']))
            ->assertOk()
            ->assertViewHas('revenue', 1200.0);
    }

    public function test_a_custom_range_with_reversed_dates_is_tolerated(): void
    {
        Sale::factory()->create([
            'sale_date' => now()->subDays(3)->toDateString(),
            'quantity' => 1, 'unit_rate' => 500,
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard', [
                'period' => 'custom',
                'from' => now()->toDateString(),          // deliberately after "to"
                'to' => now()->subDays(7)->toDateString(),
            ]))
            ->assertOk()
            ->assertViewHas('revenue', 500.0);
    }

    /**
     * Dashboard aggregates are cached; recording a sale must invalidate them
     * immediately rather than leaving a stale figure until a TTL expires.
     */
    public function test_recording_a_sale_invalidates_the_cached_dashboard(): void
    {
        Cache::clear();
        $product = Product::factory()->create();

        $this->actingAs($this->user)
            ->get(route('dashboard', ['period' => 'today']))
            ->assertViewHas('revenue', 0.0);

        Sale::factory()->for($product)->create([
            'sale_date' => now()->toDateString(),
            'quantity' => 1, 'unit_rate' => 450,
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard', ['period' => 'today']))
            ->assertViewHas('revenue', 450.0);
    }

    public function test_the_trend_includes_empty_buckets(): void
    {
        Sale::factory()->create([
            'sale_date' => now()->toDateString(),
            'quantity' => 1, 'unit_rate' => 100,
        ]);

        $trend = $this->actingAs($this->user)
            ->get(route('dashboard', ['period' => 'week']))
            ->viewData('trend');

        // A gap in the data renders as a zero column, not a missing one.
        $this->assertCount(7, $trend);
        $this->assertSame(0.0, $trend->first()['value']);
        $this->assertSame(100.0, $trend->last()['value']);
    }

    public function test_per_page_only_accepts_whitelisted_sizes(): void
    {
        Sale::factory()->count(30)->create();

        $this->actingAs($this->user)
            ->get(route('sales.index', ['per_page' => 25]))
            ->assertViewHas('perPage', 25);

        // 9999 is not on the whitelist, so it falls back to the default.
        $this->actingAs($this->user)
            ->get(route('sales.index', ['per_page' => 9999]))
            ->assertViewHas('perPage', config('shop.per_page'));
    }
}
