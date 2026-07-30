<x-layouts.app title="Sales">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat label="Revenue" :value="\App\Support\Money::format($revenue)" icon="cash" tone="brand"
            :caption="$entries.' '.\Illuminate\Support\Str::plural('entry', $entries).' matching'" />
        <x-stat label="Collected" :value="\App\Support\Money::format($collected)" icon="check" tone="success" />
        <x-stat label="Outstanding" :value="\App\Support\Money::format($outstanding)" icon="clock"
            :tone="$outstanding > 0 ? 'warning' : 'success'" />
    </div>

    <x-card class="mt-6" :padded="false">
        <x-slot:title>Sale Entries</x-slot:title>
        <x-slot:actions>
            <x-button :href="route('sales.create')" size="sm">
                <x-icon name="plus" class="h-4 w-4" /> New Sale
            </x-button>
        </x-slot:actions>

        {{-- Filters --}}
        <form method="get" action="{{ route('sales.index') }}"
            class="grid grid-cols-1 gap-3 border-b border-slate-200 px-4 py-4 sm:grid-cols-2 sm:px-5 lg:grid-cols-5 dark:border-slate-800">

            <div class="lg:col-span-2">
                <x-field name="search" label="Search" :value="$filters['search']" prefix=""
                    placeholder="Customer, product, dealer or note" />
            </div>

            <x-field name="from" label="From" type="date" :value="$filters['from']" />
            <x-field name="to" label="To" type="date" :value="$filters['to']" />

            <x-field name="status" label="Status" type="select" :value="$filters['status']" :options="$statuses">
                <option value="">All statuses</option>
            </x-field>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
                <x-button type="submit" size="sm">
                    <x-icon name="search" class="h-4 w-4" /> Apply
                </x-button>

                @if (array_filter($filters))
                    <x-button :href="route('sales.index')" variant="secondary" size="sm">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($sales->isEmpty())
            <x-empty-state title="No sales found"
                :description="array_filter($filters) ? 'No entries match these filters. Try widening your search.' : 'Record your first sale to get started.'">
                <x-slot:action>
                    <x-button :href="route('sales.create')">
                        <x-icon name="plus" class="h-4 w-4" /> Record a Sale
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            {{-- Wide content scrolls inside its own container so the page body never scrolls sideways. --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr class="text-xs font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400">
                            <th scope="col" class="px-4 py-3 sm:px-5">Date</th>
                            <th scope="col" class="px-4 py-3">Product</th>
                            <th scope="col" class="px-4 py-3">Customer</th>
                            <th scope="col" class="px-4 py-3">Dealer</th>
                            <th scope="col" class="px-4 py-3 text-right">Qty</th>
                            <th scope="col" class="px-4 py-3 text-right">Rate</th>
                            <th scope="col" class="px-4 py-3 text-right">Total</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3 text-right sm:px-5">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($sales as $sale)
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 sm:px-5 dark:text-slate-400">
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
                                        <a href="{{ route('sales.edit', $sale) }}"
                                            class="inline-flex rounded-lg p-2 text-slate-500 transition-colors hover:bg-brand-50 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <span class="sr-only">Edit sale {{ $sale->id }}</span>
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>

                                        <x-delete-form :action="route('sales.destroy', $sale)"
                                            :confirm="'Delete this sale of '.$sale->product->name.'?'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Server-side pagination. The old build rendered every row into
                 the DOM and hid the extras with jQuery. --}}
            <div class="border-t border-slate-200 px-4 py-3 sm:px-5 dark:border-slate-800">
                {{ $sales->links() }}
            </div>
        @endif
    </x-card>

</x-layouts.app>
