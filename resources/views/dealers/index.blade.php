<x-layouts.app title="Dealers">

    <x-card :padded="false">
        <x-slot:title>Dealers &amp; Suppliers</x-slot:title>
        <x-slot:actions>
            <x-button :href="route('dealers.create')" size="sm">
                <x-icon name="plus" class="h-4 w-4" /> New Dealer
            </x-button>
        </x-slot:actions>

        <form method="get" action="{{ route('dealers.index') }}"
            class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-end sm:px-5 dark:border-slate-800">
            <div class="flex-1">
                <x-field name="search" label="Search" :value="$search" placeholder="Name or phone number" />
            </div>
            <div class="flex gap-2">
                <x-button type="submit" size="sm">
                    <x-icon name="search" class="h-4 w-4" /> Search
                </x-button>
                @if ($search)
                    <x-button :href="route('dealers.index')" variant="secondary" size="sm">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($dealers->isEmpty())
            <x-empty-state icon="users" title="No dealers found"
                :description="$search ? 'No dealer matches that search.' : 'Add the dealers who supply your shop.'">
                <x-slot:action>
                    <x-button :href="route('dealers.create')">
                        <x-icon name="plus" class="h-4 w-4" /> New Dealer
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr class="text-xs font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400">
                            <th scope="col" class="px-4 py-3 sm:px-5">Name</th>
                            <th scope="col" class="px-4 py-3">Phone</th>
                            <th scope="col" class="px-4 py-3">Address</th>
                            <th scope="col" class="px-4 py-3 text-right">Sales</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3 text-right sm:px-5"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($dealers as $dealer)
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 font-medium text-slate-900 sm:px-5 dark:text-white">
                                    {{ $dealer->name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $dealer->phone ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $dealer->address ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-slate-600 dark:text-slate-400">
                                    {{ number_format($dealer->sales_count) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset
                                        {{ $dealer->is_active
                                            ? 'bg-emerald-100 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/30'
                                            : 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-700 dark:text-slate-300 dark:ring-slate-500/30' }}">
                                        {{ $dealer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap sm:px-5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('dealers.edit', $dealer) }}"
                                            class="inline-flex rounded-lg p-2 text-slate-500 transition-colors hover:bg-brand-50 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <span class="sr-only">Edit {{ $dealer->name }}</span>
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>

                                        <x-delete-form :action="route('dealers.destroy', $dealer)"
                                            :confirm="'Delete '.$dealer->name.'? Their '.$dealer->sales_count.' recorded sales will be kept but detached.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 sm:px-5 dark:border-slate-800">
                {{ $dealers->links() }}
            </div>
        @endif
    </x-card>

</x-layouts.app>
