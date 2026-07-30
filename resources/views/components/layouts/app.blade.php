@props(['title' => null])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>

    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    {{--
        Applied before first paint. If this ran from the bundle instead, the
        page would render light and then flip to dark — a visible flash.
    --}}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var dark = stored === 'dark'
                    || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) { /* private mode — fall back to light */ }
        })();
    </script>


    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans">
    <div x-data="{ sidebarOpen: false }" class="min-h-full lg:flex">

        {{-- Mobile backdrop --}}
        <div x-show="sidebarOpen" x-transition.opacity x-on:click="sidebarOpen = false" x-cloak
            class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

        <x-sidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-topbar :title="$title" />

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <x-flash />
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-200 px-4 py-4 text-center text-xs text-slate-500 sm:px-6 lg:px-8 dark:border-slate-800 dark:text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </footer>
        </div>
    </div>
</body>

</html>
