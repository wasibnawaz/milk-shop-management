<x-layouts.app title="Products">

    <x-card :padded="false">
        <x-slot:title>Product Catalogue</x-slot:title>
        <x-slot:actions>
            @can('create', App\Models\Product::class)
                <x-button :href="route('products.create')" size="sm">
                    <x-icon name="plus" class="h-4 w-4" /> New Product
                </x-button>
            @endcan
        </x-slot:actions>

        <form method="get" action="{{ route('products.index') }}"
            class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-end sm:px-5 dark:border-slate-800">
            <div class="flex-1">
                <x-field name="search" label="Search" :value="$search" placeholder="Product name" />
            </div>
            <div class="flex gap-2">
                <x-button type="submit" size="sm">
                    <x-icon name="search" class="h-4 w-4" /> Search
                </x-button>
                @if ($search)
                    <x-button :href="route('products.index')" variant="secondary" size="sm">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($products->isEmpty())
            <x-empty-state icon="box" title="No products found"
                :description="$search ? 'No product matches that search.' : 'Add the products your shop sells.'">
                @can('create', App\Models\Product::class)
                    <x-slot:action>
                        <x-button :href="route('products.create')">
                            <x-icon name="plus" class="h-4 w-4" /> New Product
                        </x-button>
                    </x-slot:action>
                @endcan
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <x-sort-header column="name" :sort="$sort" class="sm:pl-5">Name</x-sort-header>
                            <x-sort-header column="unit" :sort="$sort">Unit</x-sort-header>
                            <x-sort-header column="default_rate" :sort="$sort" align="right" class="text-right">Default Rate</x-sort-header>
                            <x-sort-header column="sales_count" :sort="$sort" align="right" class="text-right">Sales</x-sort-header>
                            <x-sort-header column="is_active" :sort="$sort">Status</x-sort-header>
                            <th scope="col" class="px-4 py-3 text-right sm:px-5"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($products as $product)
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 font-medium text-slate-900 sm:px-5 dark:text-white">
                                    {{ $product->name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $product->unit->label() }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    @money($product->default_rate)
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-slate-600 dark:text-slate-400">
                                    {{ number_format($product->sales_count) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset
                                        {{ $product->is_active
                                            ? 'bg-emerald-100 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/30'
                                            : 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-700 dark:text-slate-300 dark:ring-slate-500/30' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap sm:px-5">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}"
                                            class="inline-flex rounded-lg p-2 text-slate-500 transition-colors hover:bg-brand-50 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <span class="sr-only">Edit {{ $product->name }}</span>
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        @endcan

                                        @can('delete', $product)
                                        <x-delete-form :action="route('products.destroy', $product)"
                                            :confirm="$product->sales_count > 0
                                                ? $product->name.' has '.$product->sales_count.' recorded sales, so it will be deactivated rather than deleted. Continue?'
                                                : 'Delete '.$product->name.'?'" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 sm:px-5 dark:border-slate-800">
                {{ $products->links() }}
            </div>
        @endif
    </x-card>

</x-layouts.app>
