@props(['title' => null, 'padded' => true])

<section
    {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800']) }}>

    @if ($title || isset($actions))
        <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3.5 sm:px-5 dark:border-slate-800">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h2>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div class="{{ $padded ? 'p-4 sm:p-5' : '' }}">{{ $slot }}</div>
</section>
