<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Sale;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = ReportPeriod::fromRequest($request);

        /*
        | Dashboard aggregates are identical for every user and change only
        | when a sale changes, so they are cached against a version stamp that
        | the Sale observer bumps on write. A quiet shop reads from cache all
        | day; a sale invalidates it immediately rather than after a TTL.
        */
        $data = Cache::remember(
            'dashboard:'.Sale::cacheVersion().':'.$period->cacheKey(),
            now()->addHours(6),
            fn () => $this->buildReport($period)
        );

        return view('dashboard', $data + [
            'period' => $period,
            'periodOptions' => ReportPeriod::options(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReport(ReportPeriod $period): array
    {
        /*
        | Filtered via the `between` scope (whereDate) rather than a plain
        | whereBetween on date strings: the `date` cast stores sale_date as
        | "Y-m-d 00:00:00", so a string upper bound of "Y-m-d" sorts *before*
        | the stored value and silently excludes the final day.
        */
        $totals = Sale::between($period->from->toDateString(), $period->to->toDateString())
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as collected')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
            ->selectRaw('COUNT(*) as entries')
            ->first();

        $revenue = (float) $totals->revenue;
        $entries = (int) $totals->entries;

        return [
            'revenue' => $revenue,
            'collected' => (float) $totals->collected,
            'outstanding' => $revenue - (float) $totals->collected,
            'entries' => $entries,
            'averageSale' => $entries > 0 ? $revenue / $entries : 0.0,

            'trend' => $this->trend($period),
            'topProducts' => $this->topProducts($period),
            'paymentMix' => $this->paymentMix($period),

            'recentSales' => Sale::with(['product:id,name,unit', 'dealer:id,name'])
                ->latest('sale_date')
                ->latest('id')
                ->limit(6)
                ->get(),
        ];
    }

    /**
     * Revenue per bucket across the period, including empty buckets — a gap in
     * the data must render as a zero column, not a missing one.
     *
     * @return Collection<int, array{label: string, full: string, value: float}>
     */
    private function trend(ReportPeriod $period): Collection
    {
        // Grouped on the raw date column (portable across SQLite and MySQL),
        // then bucketed in PHP — at most 366 rows for any supported range.
        $daily = Sale::between($period->from->toDateString(), $period->to->toDateString())
            ->groupBy('sale_date')
            ->selectRaw('sale_date, SUM(total_amount) as revenue')
            ->pluck('revenue', 'sale_date');

        $byBucket = [];

        foreach ($daily as $date => $amount) {
            $key = $period->granularity() === 'month'
                ? Carbon::parse($date)->format('Y-m')
                : Carbon::parse($date)->toDateString();

            $byBucket[$key] = ($byBucket[$key] ?? 0) + (float) $amount;
        }

        $buckets = collect();
        $cursor = $period->granularity() === 'month'
            ? $period->from->copy()->startOfMonth()
            : $period->from->copy();

        // Guard against an unbounded loop if "all time" spans many years.
        $limit = 400;

        while ($cursor->lte($period->to) && $buckets->count() < $limit) {
            if ($period->granularity() === 'month') {
                $key = $cursor->format('Y-m');
                $buckets->push([
                    'label' => $cursor->format('M'),
                    'full' => $cursor->format('F Y'),
                    'value' => (float) ($byBucket[$key] ?? 0),
                ]);
                $cursor->addMonthNoOverflow();
            } else {
                $key = $cursor->toDateString();
                $buckets->push([
                    'label' => $cursor->format('j'),
                    'full' => $cursor->format('D, j M Y'),
                    'value' => (float) ($byBucket[$key] ?? 0),
                ]);
                $cursor->addDay();
            }
        }

        return $buckets;
    }

    /**
     * @return Collection<int, array{name: string, unit: string, revenue: float, quantity: float}>
     */
    private function topProducts(ReportPeriod $period): Collection
    {
        return Sale::between($period->from->toDateString(), $period->to->toDateString())
            ->with('product:id,name,unit')
            ->selectRaw('product_id, SUM(total_amount) as revenue, SUM(quantity) as quantity')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get()
            ->map(fn (Sale $row) => [
                'name' => $row->product->name,
                'unit' => $row->product->unit->abbreviation(),
                'revenue' => (float) $row->revenue,
                'quantity' => (float) $row->quantity,
            ]);
    }

    /**
     * @return array<string, array{count: int, amount: float}>
     */
    private function paymentMix(ReportPeriod $period): array
    {
        $rows = Sale::between($period->from->toDateString(), $period->to->toDateString())
            ->groupBy('payment_status')
            ->selectRaw('payment_status, COUNT(*) as entries, SUM(total_amount) as amount')
            ->get()
            ->keyBy('payment_status');

        $mix = [];

        foreach (PaymentStatus::cases() as $status) {
            $row = $rows->get($status->value);

            $mix[$status->value] = [
                'count' => (int) ($row->entries ?? 0),
                'amount' => (float) ($row->amount ?? 0),
            ];
        }

        return $mix;
    }
}
