@props(['title' => null])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

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

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=jost:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans">
    <div class="relative flex min-h-full flex-col items-center justify-center overflow-hidden px-4 py-12">

        {{-- Soft brand wash behind the card. --}}
        <div aria-hidden="true"
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(60rem_40rem_at_50%_-10%,theme(colors.brand.200),transparent)] opacity-60 dark:bg-[radial-gradient(60rem_40rem_at_50%_-10%,theme(colors.brand.900),transparent)] dark:opacity-50">
        </div>

        <div class="absolute top-4 right-4">
            <x-theme-toggle />
        </div>

        <div class="relative w-full max-w-md">
            <div class="mb-6 flex flex-col items-center text-center">
                <img src="{{ asset('images/logo2.png') }}" alt="" class="h-16 w-16 object-contain">
                <h1 class="mt-3 text-xl font-semibold text-slate-900 dark:text-white">{{ config('app.name') }}</h1>
                @isset($subtitle)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endisset
            </div>

            <div
                class="rounded-2xl bg-white/90 p-6 shadow-xl ring-1 ring-slate-200 backdrop-blur-sm sm:p-8 dark:bg-slate-900/90 dark:ring-slate-800">
                <x-flash />
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </p>
        </div>
    </div>
</body>

</html>
