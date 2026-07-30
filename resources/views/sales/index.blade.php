@php use App\Support\Money; @endphp

<x-layouts.app title="Sales">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat label="Revenue" :value="Money::format($revenue)" icon="cash" tone="brand"
            :caption="number_format($entries).' '.Str::plural('entry', $entries).' matching'" />
        <x-stat label="Collected" :value="Money::format($collected)" icon="check" tone="success" />
        <x-stat label="Outstanding" :value="Money::format($outstanding)" icon="clock"
            :tone="$outstanding > 0 ? 'warning' : 'success'" />
    </div>

    <x-card class="mt-6" :padded="false">
        <x-slot:title>Sale Entries</x-slot:title>
        <x-slot:actions>
            <x-button :href="route('sales.export', request()->query())" variant="secondary" size="sm">
                <x-icon name="download" class="h-4 w-4" />
                <span class="hidden sm:inline">Export CSV</span>
            </x-button>

            @can('create', App\Models\Sale::class)
                <x-button :href="route('sales.create')" size="sm">
                    <x-icon name="plus" class="h-4 w-4" />
                    <span class="hidden sm:inline">New Sale</span>
                </x-button>
            @endcan
        </x-slot:actions>

        {{-- Filters. Collapsed on mobile so the table is reachable without scrolling past them. --}}
        <div x-data="{ open: window.innerWidth >= 1024 || @js((bool) array_filter($filters)) }"
            class="border-b border-slate-200 dark:border-slate-800">

            <button type="button" x-on:click="open = !open"
                class="flex w-full items-center justify-between gap-2 px-4 py-3 text-sm font-medium text-slate-700 sm:px-5 lg:hidden dark:text-slate-300">
                <span class="flex items-center gap-2">
                    <x-icon name="filter" class="h-4 w-4" /> Filters
                    @if (array_filter($filters))
                        <span class="rounded-full bg-brand-100 px-2 py-0.5 text-xs text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                            {{ count(array_filter($filters)) }}
                        </span>
                    @endif
                </span>
                <x-icon name="arrow-down" class="h-4 w-4 transition-transform" ::class="open && 'rotate-180'" />
            </button>

            <form method="get" action="{{ route('sales.index') }}" x-show="open" x-cloak
                x-transition.opacity
                class="grid grid-cols-1 gap-3 px-4 pb-4 sm:grid-cols-2 sm:px-5 lg:grid-cols-6 lg:pt-4">

                {{-- Preserve sort when filters change. --}}
                <input type="hidden" name="sort" value="{{ $sort['column'] }}">
                <input type="hidden" name="direction" value="{{ $sort['direction'] }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="lg:col-span-2">
                    <x-field name="search" label="Search" :value="$filters['search']"
                        placeholder="Customer, product, dealer or note" />
                </div>

                <x-field name="from" label="From" type="date" :value="$filters['from']" />
                <x-field name="to" label="To" type="date" :value="$filters['to']" />

                <x-field name="status" label="Status" type="select" :value="$filters['status']" :options="$statuses">
                    <option value="">All statuses</option>
                </x-field>

                <x-field name="product_id" label="Product" type="select" :value="$filters['product_id']"
                    :options="$products->pluck('name', 'id')">
                    <option value="">All products</option>
                </x-field>

                <div class="sm:col-span-2 lg:col-span-6">
                    <div class="flex flex-wrap items-end gap-2">
                        <x-button type="submit" size="sm">
                            <x-icon name="search" class="h-4 w-4" /> Apply
                        </x-button>

                        @if (array_filter($filters))
                            <x-button :href="route('sales.index')" variant="secondary" size="sm">Clear all</x-button>
                        @endif

                        <div class="ml-auto flex items-center gap-2">
                            <label for="per_page_select" class="text-xs text-slate-500 dark:text-slate-400">Per page</label>
                            <select id="per_page_select" name="per_page"
                                x-on:change="$el.closest('form').submit()"
                                class="rounded-lg border-0 bg-white py-1.5 pr-8 pl-2.5 text-xs ring-1 ring-slate-300 ring-inset focus:ring-2 focus:ring-brand-500 dark:bg-slate-800 dark:text-white dark:ring-slate-700">
                                @foreach (config('shop.per_page_options') as $option)
                                    <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @if ($sales->isEmpty())
            <x-empty-state title="No sales found"
                :description="array_filter($filters) ? 'No entries match these filters. Try widening your search.' : 'Record your first sale to get started.'">
                @can('create', App\Models\Sale::class)
                    <x-slot:action>
                        <x-button :href="route('sales.create')">
                            <x-icon name="plus" class="h-4 w-4" /> Record a Sale
                        </x-button>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            {{-- Mobile: cards. A 9-column table cannot be read on a phone. --}}
            <ul class="divide-y divide-slate-100 lg:hidden dark:divide-slate-800">
                @foreach ($sales as $sale)
                    <li class="px-4 py-3.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">
                                    {{ $sale->product->name }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $sale->sale_date->format('d M Y') }}
                                    &middot; {{ $sale->customer_name ?? 'Walk-in' }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                                @money($sale->total_amount)
                            </p>
                        </div>

                        <div class="mt-2.5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $sale->payment_status->badgeClasses() }}">
                                    {{ $sale->payment_status->label() }}
                                </span>
                                <span class="text-xs tabular-nums text-slate-500 dark:text-slate-400">
                                    @qty($sale->quantity) {{ $sale->product->unit->abbreviation() }}
                                    &times; @money($sale->unit_rate)
                                </span>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                @can('update', $sale)
                                    <a href="{{ route('sales.edit', $sale) }}"
                                        class="inline-flex rounded-lg p-2 text-slate-500 hover:bg-brand-50 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-brand-500/10">
                                        <span class="sr-only">Edit sale {{ $sale->id }}</span>
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                @endcan
                                @can('delete', $sale)
                                    <x-delete-form :action="route('sales.destroy', $sale)"
                                        :confirm="'Delete this sale of '.$sale->product->name.'?'" />
                                @endcan
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Desktop: full sortable table, scrolling inside its own container. --}}
            <div class="hidden overflow-x-auto lg:block">
                <table class="w-full min-w-[54rem] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <x-sort-header column="sale_date" :sort="$sort" class="sm:pl-5">Date</x-sort-header>
                            <x-sort-header column="product" :sort="$sort">Product</x-sort-header>
                            <x-sort-header column="customer_name" :sort="$sort">Customer</x-sort-header>
                            <x-sort-header column="dealer" :sort="$sort">Dealer</x-sort-header>
                            <x-sort-header column="quantity" :sort="$sort" align="right" class="text-right">Qty</x-sort-header>
                            <x-sort-header column="unit_rate" :sort="$sort" align="right" class="text-right">Rate</x-sort-header>
                            <x-sort-header column="total_amount" :sort="$sort" align="right" class="text-right">Total</x-sort-header>
                            <x-sort-header column="payment_status" :sort="$sort">Status</x-sort-header>
                            <th scope="col" class="px-4 py-3 text-right sm:px-5"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($sales as $sale)
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 sm:pl-5 dark:text-slate-400">
                                    {{ $sale->sale_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                    {{ $sale->product->name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $sale->customer_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $sale->dealer?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    @qty($sale->quantity) {{ $sale->product->unit->abbreviation() }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    @money($sale->unit_rate)
                                </td>
                                <td class="px-4 py-3 text-right font-semibold tabular-nums whitespace-nowrap text-slate-900 dark:text-white">
                                    @money($sale->total_amount)
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $sale->payment_status->badgeClasses() }}">
                                        {{ $sale->payment_status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap sm:px-5">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('update', $sale)
                                            <a href="{{ route('sales.edit', $sale) }}"
                                                class="inline-flex rounded-lg p-2 text-slate-500 transition-colors hover:bg-brand-50 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                                <span class="sr-only">Edit sale {{ $sale->id }}</span>
                                                <x-icon name="pencil" class="h-4 w-4" />
                                            </a>
                                        @endcan

                                        @can('delete', $sale)
                                            <x-delete-form :action="route('sales.destroy', $sale)"
                                                :confirm="'Delete this sale of '.$sale->product->name.'?'" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 sm:px-5 dark:border-slate-800">
                {{ $sales->links() }}
            </div>
        @endif
    </x-card>

</x-layouts.app>
