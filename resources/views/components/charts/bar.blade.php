@props([
    'data',
    'valueKey' => 'revenue',
    'labelKey' => 'name',
])

@php
    use App\Support\Money;

    $rows = collect($data)->values();
    $max = (float) $rows->max($valueKey) ?: 1;
@endphp

@if ($rows->isEmpty())
    <x-empty-state icon="chart" title="No sales in this period"
        description="Product performance appears once sales are recorded." />
@else
    <ul class="space-y-3.5">
        @foreach ($rows as $row)
            @php $pct = max(($row[$valueKey] / $max) * 100, 1.5); @endphp

            <li class="group">
                <div class="flex items-baseline justify-between gap-3">
                    {{-- Text wears text tokens, never the series colour. --}}
                    <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $row[$labelKey] }}
                    </p>
                    {{-- Direct value label at the bar's tip. --}}
                    <p class="shrink-0 text-sm font-semibold tabular-nums text-slate-900 dark:text-white">
                        {{ Money::format($row[$valueKey]) }}
                    </p>
                </div>

                <div class="mt-1.5 flex items-center gap-2">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-(--chart-series) transition-[width] duration-500 ease-out"
                            style="width: {{ $pct }}%"></div>
                    </div>

                    @isset($row['quantity'])
                        <span class="shrink-0 text-xs tabular-nums text-slate-500 dark:text-slate-400">
                            {{ Money::quantity($row['quantity']) }} {{ $row['unit'] ?? '' }}
                        </span>
                    @endisset
                </div>
            </li>
        @endforeach
    </ul>
@endif
