<x-layouts.app title="Dashboard">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Today's Sales" :value="\App\Support\Money::format($todayRevenue)" icon="cash" tone="success"
            :caption="$todayEntries.' '.\Illuminate\Support\Str::plural('entry', $todayEntries).' today'" />

        <x-stat label="This Month" :value="\App\Support\Money::format($monthRevenue)" icon="chart" tone="brand"
            :caption="now()->format('F Y')" />

        <x-stat label="All Time" :value="\App\Support\Money::format($totalRevenue)" icon="receipt" tone="brand" />

        <x-stat label="Outstanding" :value="\App\Support\Money::format($outstanding)" icon="clock"
            :tone="$outstanding > 0 ? 'warning' : 'success'" caption="Unpaid and partial" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Recent sales --}}
        <x-card class="lg:col-span-2" :padded="false">
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
                    <x-slot:action>
                        <x-button :href="route('sales.create')">
                            <x-icon name="plus" class="h-4 w-4" /> Record a Sale
                        </x-button>
                    </x-slot:action>
                </x-empty-state>
            @endforelse
        </x-card>

        {{-- Top products this month --}}
        <x-card :padded="false">
            <x-slot:title>Top Products — {{ now()->format('M Y') }}</x-slot:title>

            @php $max = $topProducts->max('revenue') ?: 1; @endphp

            @forelse ($topProducts as $row)
                <div class="border-b border-slate-100 px-4 py-3 last:border-0 sm:px-5 dark:border-slate-800">
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                            {{ $row->product->name }}
                        </p>
                        <p class="shrink-0 text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                            @money($row->revenue)
                        </p>
                    </div>

                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-brand-500 transition-all duration-500"
                            style="width: {{ round(($row->revenue / $max) * 100, 1) }}%"></div>
                    </div>

                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                        @qty($row->quantity) {{ $row->product->unit->abbreviation() }} sold
                    </p>
                </div>
            @empty
                <x-empty-state icon="chart" title="No sales this month"
                    description="Product performance appears once sales are recorded." />
            @endforelse
        </x-card>
    </div>

</x-layouts.app>
