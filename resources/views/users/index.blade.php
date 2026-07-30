<x-layouts.app title="Staff">

    <x-card :padded="false">
        <x-slot:title>Staff Accounts</x-slot:title>
        <x-slot:actions>
            <x-button :href="route('users.create')" size="sm">
                <x-icon name="plus" class="h-4 w-4" /> New Account
            </x-button>
        </x-slot:actions>

        <form method="get" action="{{ route('users.index') }}"
            class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-end sm:px-5 dark:border-slate-800">
            <div class="flex-1">
                <x-field name="search" label="Search" :value="$search" placeholder="Name or email" />
            </div>
            <div class="flex gap-2">
                <x-button type="submit" size="sm">
                    <x-icon name="search" class="h-4 w-4" /> Search
                </x-button>
                @if ($search)
                    <x-button :href="route('users.index')" variant="secondary" size="sm">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($users->isEmpty())
            <x-empty-state icon="shield" title="No accounts found"
                :description="$search ? 'No account matches that search.' : 'Create accounts for your staff.'" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr class="text-xs font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400">
                            <th scope="col" class="px-4 py-3 sm:px-5">Name</th>
                            <th scope="col" class="px-4 py-3">Email</th>
                            <th scope="col" class="px-4 py-3">Role</th>
                            <th scope="col" class="px-4 py-3 text-right">Sales</th>
                            <th scope="col" class="px-4 py-3">Last Seen</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3 text-right sm:px-5"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($users as $staff)
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3 font-medium text-slate-900 sm:px-5 dark:text-white">
                                    {{ $staff->name }}
                                    @if ($staff->is(auth()->user()))
                                        <span class="ml-1 text-xs font-normal text-slate-500 dark:text-slate-400">(you)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $staff->email }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $staff->role->badgeClasses() }}">
                                        {{ $staff->role->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums text-slate-600 dark:text-slate-400">
                                    {{ number_format($staff->sales_count) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $staff->last_login_at?->diffForHumans() ?? 'Never' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset
                                        {{ $staff->is_active
                                            ? 'bg-emerald-100 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/30'
                                            : 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-700 dark:text-slate-300 dark:ring-slate-500/30' }}">
                                        {{ $staff->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap sm:px-5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('users.edit', $staff) }}"
                                            class="inline-flex rounded-lg p-2 text-slate-500 transition-colors hover:bg-brand-50 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-brand-500/10 dark:hover:text-brand-400">
                                            <span class="sr-only">Edit {{ $staff->name }}</span>
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>

                                        @can('delete', $staff)
                                            <x-delete-form :action="route('users.destroy', $staff)"
                                                :confirm="'Remove '.$staff->name.'? Their recorded sales are kept.'" />
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 sm:px-5 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </x-card>

</x-layouts.app>
