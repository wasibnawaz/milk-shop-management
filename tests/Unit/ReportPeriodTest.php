<?php

namespace Tests\Unit;

use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportPeriodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fixed date so month-boundary behaviour is deterministic.
        Carbon::setTestNow(Carbon::parse('2026-03-15 14:30:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function period(array $query): ReportPeriod
    {
        return ReportPeriod::fromRequest(new Request($query));
    }

    public function test_it_defaults_to_the_current_month(): void
    {
        $period = $this->period([]);

        $this->assertSame('month', $period->key);
        $this->assertSame('2026-03-01', $period->from->toDateString());
        $this->assertSame('2026-03-15', $period->to->toDateString());
    }

    public function test_an_unrecognised_period_falls_back_to_the_default(): void
    {
        $this->assertSame('month', $this->period(['period' => 'nonsense'])->key);
    }

    public function test_today_covers_a_single_day(): void
    {
        $period = $this->period(['period' => 'today']);

        $this->assertSame('2026-03-15', $period->from->toDateString());
        $this->assertSame('2026-03-15', $period->to->toDateString());
        $this->assertSame(1, $period->days());
    }

    public function test_the_last_seven_days_is_inclusive_of_today(): void
    {
        $period = $this->period(['period' => 'week']);

        $this->assertSame('2026-03-09', $period->from->toDateString());
        $this->assertSame(7, $period->days());
    }

    public function test_last_month_covers_the_whole_previous_month(): void
    {
        $period = $this->period(['period' => 'last_month']);

        $this->assertSame('2026-02-01', $period->from->toDateString());
        $this->assertSame('2026-02-28', $period->to->toDateString());
        $this->assertSame('February 2026', $period->label);
    }

    /**
     * subMonth() on the 31st would roll into the wrong month; the
     * NoOverflow variants must be used.
     */
    public function test_last_month_is_correct_when_today_has_no_equivalent_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-31'));

        $period = $this->period(['period' => 'last_month']);

        $this->assertSame('2026-02-01', $period->from->toDateString());
        $this->assertSame('2026-02-28', $period->to->toDateString());
    }

    public function test_a_custom_range_is_honoured(): void
    {
        $period = $this->period([
            'period' => 'custom',
            'from' => '2026-01-10',
            'to' => '2026-01-20',
        ]);

        $this->assertSame('2026-01-10', $period->from->toDateString());
        $this->assertSame('2026-01-20', $period->to->toDateString());
        $this->assertSame(11, $period->days());
    }

    public function test_a_reversed_custom_range_is_swapped_rather_than_returning_nothing(): void
    {
        $period = $this->period([
            'period' => 'custom',
            'from' => '2026-01-20',
            'to' => '2026-01-10',
        ]);

        $this->assertSame('2026-01-10', $period->from->toDateString());
        $this->assertSame('2026-01-20', $period->to->toDateString());
    }

    public function test_short_ranges_bucket_by_day_and_long_ranges_by_month(): void
    {
        // Under ~3 months, daily columns stay readable.
        $this->assertSame('day', $this->period(['period' => 'month'])->granularity());
        $this->assertSame('day', $this->period(['period' => 'quarter'])->granularity());

        // "This year" on 15 March is only 74 days, so it is still daily.
        $this->assertSame('day', $this->period(['period' => 'year'])->granularity());

        $this->assertSame('month', $this->period(['period' => 'all'])->granularity());

        // Late in the year, the same preset switches to monthly buckets.
        Carbon::setTestNow(Carbon::parse('2026-12-31'));
        $this->assertSame('month', $this->period(['period' => 'year'])->granularity());
    }

    public function test_the_cache_key_distinguishes_different_ranges(): void
    {
        $this->assertNotSame(
            $this->period(['period' => 'today'])->cacheKey(),
            $this->period(['period' => 'week'])->cacheKey()
        );
    }
}
