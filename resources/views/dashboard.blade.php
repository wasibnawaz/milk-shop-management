@php use App\Enums\PaymentStatus; use App\Support\Money; @endphp

<x-layouts.app title="Dashboard">

    {{-- Period selector --}}
    <form method="get" action="{{ route('dashboard') }}" x-data="{ custom: @js($period->key === 'custom') }"
        class="mb-6 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-end dark:bg-slate-900 dark:ring-slate-800">

        <div class="flex-1 sm:max-w-xs">
            <x-field name="period" label="Reporting period" type="select" :options="$periodOptions"
                :value="$period->key" x-on:change="custom = $event.target.value === 'custom'" />
        </div>

        <div x-show="custom" x-cloak x-transition.opacity class="flex flex-1 gap-3">
            <div class="flex-1"><x-field name="from" label="From" type="date" :value="$period->from->toDateString()" /></div>
            <div class="flex-1"><x-field name="to" label="To" type="date" :value="$period->to->toDateString()" /></div>
        </div>

        <x-button type="submit">
            <x-icon name="filter" class="h-4 w-4" /> Apply
        </x-button>
    </form>

    {{-- Hero figure — exactly one per view. --}}
    <div class="mb-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6 dark:bg-slate-900 dark:ring-slate-800">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400">
                    Revenue — {{ $period->label }}
                </p>
                <p class="mt-1 text-4xl font-semibold text-slate-900 sm:text-5xl dark:text-white">
                    {{ Money::format($revenue) }}
                </p>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                    {{ number_format($entries) }} {{ Str::plural('sale', $entries) }}
                    &middot; {{ Money::format($averageSale) }} average
                </p>
            </div>

            <x-button :href="route('sales.index', ['from' => $period->from->toDateString(), 'to' => $period->to->toDateString()])"
                variant="secondary" size="sm">
                View entries <x-icon name="receipt" class="h-4 w-4" />
            </x-button>
        </div>
    </div>

    {{-- Supporting stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Collected" :value="Money::format($collected)" icon="check" tone="success"
            :caption="$revenue > 0 ? round(($collected / $revenue) * 100).'% of revenue' : null" />

        <x-stat label="Outstanding" :value="Money::format($outstanding)" icon="clock"
            :tone="$outstanding > 0 ? 'warning' : 'success'"
            :caption="$paymentMix[PaymentStatus::Unpaid->value]['count'].' unpaid, '.$paymentMix[PaymentStatus::Partial->value]['count'].' partial'" />

        <x-stat label="Paid in Full" :value="number_format($paymentMix[PaymentStatus::Paid->value]['count'])"
            icon="receipt" tone="brand" :caption="Money::format($paymentMix[PaymentStatus::Paid->value]['amount'])" />

        <x-stat label="Average Sale" :value="Money::format($averageSale)" icon="chart" tone="brand"
            :caption="'across '.number_format($entries).' '.Str::plural('entry', $entries)" />
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- Revenue trend --}}
        <x-card class="xl:col-span-2">
            <x-slot:title>
                Revenue by {{ $period->granularity() === 'month' ? 'month' : 'day' }}
            </x-slot:title>
            <x-slot:actions>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $period->label }}</span>
            </x-slot:actions>

            <x-charts.column :data="$trend" />
        </x-card>

        {{-- Top products --}}
        <x-card title="Top Products">
            <x-charts.bar :data="$topProducts" />
        </x-card>
    </div>

    {{-- Recent sales --}}
    <x-card class="mt-4" :padded="false">
        <x-slot:title>Recent Sales</x-slot:title>
        <x-slot:actions>
            <x-button :href="route('sales.index')" variant="ghost" size="sm">View all</x-button>
        </x-slot:actions>

        @forelse ($recentSales as $sale)
            <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3 last:border-0 sm:px-5 dark:border-slate-800">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                        {{ $sale->product->name }}
                    </p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        @qty($sale->quantity) {{ $sale->product->unit->abbreviation() }}
                        &middot; {{ $sale->customer_name ?? 'Walk-in' }}
                        &middot; {{ $sale->sale_date->format('d M Y') }}
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <p class="text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                        @money($sale->total_amount)
                    </p>
                    <span
                        class="mt-0.5 inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $sale->payment_status->badgeClasses() }}">
                        {{ $sale->payment_status->label() }}
                    </span>
                </div>
            </div>
        @empty
            <x-empty-state title="No sales recorded yet"
                description="Record your first sale and it will show up here.">
                @can('create', App\Models\Sale::class)
                    <x-slot:action>
                        <x-button :href="route('sales.create')">
                            <x-icon name="plus" class="h-4 w-4" /> Record a Sale
                        </x-button>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @endforelse
    </x-card>

</x-layouts.app>
