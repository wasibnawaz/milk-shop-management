@props([
    'label',
    'value',
    'icon' => 'cash',
    'tone' => 'brand',
    'caption' => null,
])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
        'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'danger' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    ];
@endphp

<div
    class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 transition-shadow hover:shadow-md sm:p-5 dark:bg-slate-900 dark:ring-slate-800">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-xs font-medium tracking-wide text-slate-500 uppercase dark:text-slate-400">
                {{ $label }}
            </p>
            <p class="mt-2 truncate text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">
                {{ $value }}
            </p>
            @if ($caption)
                <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">{{ $caption }}</p>
            @endif
        </div>

        <span class="shrink-0 rounded-lg p-2.5 {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
</div>
