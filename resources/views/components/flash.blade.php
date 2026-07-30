@php
    // The old app flashed an 'error' key from deleteMilk that no view ever
    // rendered. All four channels are handled here, in one place.
    $messages = array_filter([
        'success' => session('success'),
        'info' => session('info'),
        'warning' => session('warning'),
        'error' => session('error'),
    ]);

    $styles = [
        'success' => ['icon' => 'check', 'classes' => 'bg-emerald-50 text-emerald-900 ring-emerald-600/20 dark:bg-emerald-950 dark:text-emerald-200 dark:ring-emerald-400/25', 'accent' => 'bg-emerald-500'],
        'info' => ['icon' => 'info', 'classes' => 'bg-brand-50 text-brand-900 ring-brand-600/20 dark:bg-brand-950 dark:text-brand-200 dark:ring-brand-400/25', 'accent' => 'bg-brand-500'],
        'warning' => ['icon' => 'warning', 'classes' => 'bg-amber-50 text-amber-900 ring-amber-600/20 dark:bg-amber-950 dark:text-amber-200 dark:ring-amber-400/25', 'accent' => 'bg-amber-500'],
        'error' => ['icon' => 'warning', 'classes' => 'bg-rose-50 text-rose-900 ring-rose-600/20 dark:bg-rose-950 dark:text-rose-200 dark:ring-rose-400/25', 'accent' => 'bg-rose-500'],
    ];
@endphp

{{-- Toasts: fixed so a confirmation never pushes the page content down. --}}
@if ($messages)
    <div class="pointer-events-none fixed top-20 right-4 z-50 flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-2 sm:right-6"
        role="status" aria-live="polite">

        @foreach ($messages as $type => $message)
            <div x-data="{ show: false }" x-init="$nextTick(() => show = true); setTimeout(() => show = false, 6000)"
                x-show="show" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-6"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-end="opacity-0 translate-x-6"
                class="pointer-events-auto flex items-start gap-3 overflow-hidden rounded-xl px-4 py-3 text-sm font-medium shadow-lg ring-1 ring-inset {{ $styles[$type]['classes'] }}">

                <span class="absolute inset-y-0 left-0 w-1 {{ $styles[$type]['accent'] }}" aria-hidden="true"></span>

                <x-icon :name="$styles[$type]['icon']" class="mt-0.5 h-5 w-5 shrink-0" />
                <p class="flex-1">{{ $message }}</p>

                <button type="button" x-on:click="show = false"
                    class="shrink-0 rounded opacity-60 transition-opacity hover:opacity-100">
                    <span class="sr-only">Dismiss</span>
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
        @endforeach
    </div>
@endif

{{-- Validation summary stays inline: it refers to the form directly below it,
     and must not disappear on a timer while the user is fixing fields. --}}
@if ($errors->any())
    <div role="alert"
        class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:ring-rose-400/20">
        <div class="flex items-start gap-3">
            <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <div class="flex-1">
                <p class="font-semibold text-rose-800 dark:text-rose-300">
                    Please fix {{ $errors->count() === 1 ? 'this problem' : 'these '.$errors->count().' problems' }}:
                </p>
                <ul class="mt-1.5 list-inside list-disc space-y-1 text-rose-700 dark:text-rose-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
