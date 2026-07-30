@props(['title' => null])

<header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md sm:px-6 lg:px-8 dark:border-slate-800 dark:bg-slate-900/80">

    <button type="button" x-on:click="sidebarOpen = true"
        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800">
        <span class="sr-only">Open navigation</span>
        <x-icon name="menu" />
    </button>

    <h1 class="min-w-0 flex-1 truncate text-base font-semibold text-slate-900 sm:text-lg dark:text-white">
        {{ $title ?? config('app.name') }}
    </h1>

    <x-theme-toggle />

    <div class="flex items-center gap-2 border-l border-slate-200 pl-3 dark:border-slate-800">
        <img src="{{ asset('images/admin.png') }}" alt=""
            class="h-9 w-9 rounded-full object-cover ring-2 ring-white dark:ring-slate-800">
        <span class="hidden text-sm font-medium text-slate-700 sm:block dark:text-slate-300">
            {{ auth()->user()?->name ?? 'Guest' }}
        </span>
    </div>
</header>
