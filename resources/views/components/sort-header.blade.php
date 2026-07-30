@props([
    'column',
    'sort',
    'align' => 'left',
])

@php
    $isActive = ($sort['column'] ?? null) === $column;

    // Clicking the active column flips direction; a new column starts descending.
    $nextDirection = $isActive && ($sort['direction'] ?? 'desc') === 'desc' ? 'asc' : 'desc';

    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection, 'page' => null]);

    $justify = match ($align) {
        'right' => 'justify-end',
        'center' => 'justify-center',
        default => 'justify-start',
    };
@endphp

<th scope="col" {{ $attributes->merge(['class' => 'px-4 py-3']) }}
    @if ($isActive) aria-sort="{{ $sort['direction'] === 'asc' ? 'ascending' : 'descending' }}" @endif>

    <a href="{{ $url }}"
        class="group inline-flex items-center gap-1 {{ $justify }} text-xs font-medium tracking-wide uppercase transition-colors
            {{ $isActive
                ? 'text-brand-600 dark:text-brand-400'
                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">

        {{ $slot }}

        @if ($isActive)
            <x-icon :name="$sort['direction'] === 'asc' ? 'arrow-up' : 'arrow-down'" class="h-3.5 w-3.5" />
        @else
            <x-icon name="arrow-down"
                class="h-3.5 w-3.5 opacity-0 transition-opacity group-hover:opacity-50" />
        @endif
    </a>
</th>
