@props(['title' => null])

@php $user = auth()->user(); @endphp

<header
    class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md sm:px-6 lg:px-8 dark:border-slate-800 dark:bg-slate-900/80">

    <button type="button" x-on:click="sidebarOpen = true"
        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800">
        <span class="sr-only">Open navigation</span>
        <x-icon name="menu" />
    </button>

    <h1 class="min-w-0 flex-1 truncate text-base font-semibold text-slate-900 sm:text-lg dark:text-white">
        {{ $title ?? config('app.name') }}
    </h1>

    <x-theme-toggle />

    @if ($user)
        <div x-data="{ open: false }" class="relative border-l border-slate-200 pl-2 dark:border-slate-800">
            <button type="button" x-on:click="open = !open" :aria-expanded="open" aria-haspopup="menu"
                class="flex items-center gap-2 rounded-lg p-1.5 transition-colors hover:bg-slate-100 dark:hover:bg-slate-800">
                <img src="{{ asset('images/admin.png') }}" alt=""
                    class="h-8 w-8 rounded-full object-cover ring-2 ring-white dark:ring-slate-800">
                <span class="hidden text-left sm:block">
                    <span class="block text-sm leading-tight font-medium text-slate-700 dark:text-slate-200">
                        {{ $user->name }}
                    </span>
                    <span class="block text-xs leading-tight text-slate-500 dark:text-slate-400">
                        {{ $user->role->label() }}
                    </span>
                </span>
            </button>

            <div x-show="open" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false" x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-end="opacity-0 scale-95"
                role="menu"
                class="absolute right-0 z-30 mt-2 w-56 origin-top-right overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">

                <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-700">
                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                </div>

                <a href="{{ route('profile.edit') }}" role="menuitem"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">
                    <x-icon name="user" class="h-4 w-4" /> My Profile
                </a>

                <form method="post" action="{{ route('logout') }}" class="border-t border-slate-100 dark:border-slate-700">
                    @csrf
                    <button type="submit" role="menuitem"
                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                        <x-icon name="logout" class="h-4 w-4" /> Sign Out
                    </button>
                </form>
            </div>
        </div>
    @endif
</header>
