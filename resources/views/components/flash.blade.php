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
        'success' => ['icon' => 'check', 'classes' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20'],
        'info' => ['icon' => 'info', 'classes' => 'bg-brand-50 text-brand-800 ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-400/20'],
        'warning' => ['icon' => 'warning', 'classes' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20'],
        'error' => ['icon' => 'warning', 'classes' => 'bg-rose-50 text-rose-800 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/20'],
    ];
@endphp

@foreach ($messages as $type => $message)
    <div x-data="{ show: true }" x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-end="opacity-0"
        role="status"
        class="mb-4 flex items-start gap-3 rounded-xl px-4 py-3 text-sm font-medium ring-1 ring-inset {{ $styles[$type]['classes'] }}">

        <x-icon :name="$styles[$type]['icon']" class="mt-0.5 h-5 w-5 shrink-0" />
        <p class="flex-1">{{ $message }}</p>

        <button type="button" x-on:click="show = false" class="shrink-0 opacity-60 transition-opacity hover:opacity-100">
            <span class="sr-only">Dismiss</span>
            <x-icon name="x" class="h-4 w-4" />
        </button>
    </div>
@endforeach

{{-- Validation summary. Individual fields also show their own message. --}}
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
