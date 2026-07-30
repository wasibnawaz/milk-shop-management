@php
    $links = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'sales.index', 'label' => 'Sales', 'icon' => 'receipt'],
        ['route' => 'products.index', 'label' => 'Products', 'icon' => 'box'],
        ['route' => 'dealers.index', 'label' => 'Dealers', 'icon' => 'users'],
    ];
@endphp

<aside x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-out lg:static lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900">

    <div class="flex h-16 items-center justify-between gap-2 border-b border-slate-200 px-4 dark:border-slate-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0">
            <img src="{{ asset('images/logo2.png') }}" alt="" class="h-9 w-9 shrink-0 object-contain">
            <span class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                {{ config('app.name') }}
            </span>
        </a>

        <button type="button" x-on:click="sidebarOpen = false"
            class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800">
            <span class="sr-only">Close navigation</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto p-3">
        @foreach ($links as $link)
            @php
                $active = request()->routeIs(str_replace('.index', '.*', $link['route']))
                    || request()->routeIs($link['route']);
            @endphp

            <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                    {{ $active
                        ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                <x-icon :name="$link['icon']" class="h-5 w-5 shrink-0" />
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="border-t border-slate-200 p-3 dark:border-slate-800">
        <x-button :href="route('sales.create')" class="w-full justify-center">
            <x-icon name="plus" class="h-4 w-4" />
            New Sale
        </x-button>
    </div>
</aside>
