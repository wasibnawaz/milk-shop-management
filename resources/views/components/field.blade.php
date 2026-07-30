@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'options' => null,
    'required' => false,
    'hint' => null,
    'prefix' => null,
])

@php
    $hasError = $errors->has($name);

    // old() first so a failed submit never wipes what the user typed — the
    // original forms had no old() at all.
    $current = old($name, $value);

    $control = implode(' ', [
        'block w-full rounded-lg border-0 px-3 py-2.5 text-sm shadow-sm ring-1 ring-inset transition-colors',
        'placeholder:text-slate-400 focus:ring-2 focus:ring-inset',
        'bg-white text-slate-900 dark:bg-slate-800 dark:text-white',
        $hasError
            ? 'ring-rose-400 focus:ring-rose-500 dark:ring-rose-500/50'
            : 'ring-slate-300 focus:ring-brand-500 dark:ring-slate-700',
        $prefix ? 'pl-10' : '',
    ]);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($prefix)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">
                {{ $prefix }}
            </span>
        @endif

        @if ($type === 'select')
            <select id="{{ $name }}" name="{{ $name }}" @required($required)
                @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
                {{ $attributes->merge(['class' => $control]) }}>
                {{ $slot }}
                @foreach ($options ?? [] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>
        @elseif ($type === 'textarea')
            <textarea id="{{ $name }}" name="{{ $name }}" rows="3" @required($required)
                @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
                {{ $attributes->merge(['class' => $control]) }}>{{ $current }}</textarea>
        @else
            <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}"
                @required($required)
                @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
                {{ $attributes->merge(['class' => $control]) }}>
        @endif
    </div>

    @if ($hasError)
        <p id="{{ $name }}-error" class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600 dark:text-rose-400">
            <x-icon name="warning" class="h-3.5 w-3.5 shrink-0" />
            {{ $errors->first($name) }}
        </p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif
</div>
