<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Resolves the dashboard's date range from request input, with a whitelist of
 * named presets plus an explicit custom range.
 */
class ReportPeriod
{
    public function __construct(
        public readonly string $key,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly string $label,
    ) {}

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            'today' => 'Today',
            'week' => 'Last 7 days',
            'month' => 'This month',
            'last_month' => 'Last month',
            'quarter' => 'Last 90 days',
            'year' => 'This year',
            'all' => 'All time',
            'custom' => 'Custom range',
        ];
    }

    public static function fromRequest(Request $request): self
    {
        $key = $request->string('period')->toString();
        $today = Carbon::today();

        if ($key === 'custom') {
            $from = $request->date('from') ?? $today->copy()->subDays(29);
            $to = $request->date('to') ?? $today;

            // Tolerate a reversed range rather than returning nothing.
            if ($from->gt($to)) {
                [$from, $to] = [$to, $from];
            }

            return new self(
                'custom',
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->startOfDay(),
                $from->format('d M Y').' – '.$to->format('d M Y'),
            );
        }

        return match ($key) {
            'today' => new self('today', $today->copy(), $today->copy(), 'Today'),
            'week' => new self('week', $today->copy()->subDays(6), $today->copy(), 'Last 7 days'),
            'last_month' => new self(
                'last_month',
                $today->copy()->subMonthNoOverflow()->startOfMonth(),
                $today->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay(),
                $today->copy()->subMonthNoOverflow()->format('F Y'),
            ),
            'quarter' => new self('quarter', $today->copy()->subDays(89), $today->copy(), 'Last 90 days'),
            'year' => new self('year', $today->copy()->startOfYear(), $today->copy(), $today->format('Y')),
            'all' => new self('all', Carbon::create(2000, 1, 1), $today->copy(), 'All time'),
            default => new self('month', $today->copy()->startOfMonth(), $today->copy(), $today->format('F Y')),
        };
    }

    public function days(): int
    {
        return $this->from->diffInDays($this->to) + 1;
    }

    /**
     * Buckets longer ranges by month so the trend chart never renders hundreds
     * of unreadably thin columns.
     */
    public function granularity(): string
    {
        return $this->days() > 92 ? 'month' : 'day';
    }

    /** Stable cache key for this range. */
    public function cacheKey(): string
    {
        return 'period:'.$this->key.':'.$this->from->toDateString().':'.$this->to->toDateString();
    }
}
