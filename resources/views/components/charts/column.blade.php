@props([
    'data',
    'height' => 220,
])

@php
    use App\Support\Money;

    $rows = collect($data)->values();
    $max = (float) $rows->max('value');

    /*
    | Round the axis top up to a "nice" number so ticks land on clean values
    | (0 / 2,000 / 4,000) instead of arbitrary ones.
    */
    $niceTop = static function (float $value): float {
        if ($value <= 0) {
            return 1.0;
        }

        $magnitude = 10 ** floor(log10($value));
        $normalised = $value / $magnitude;

        $step = match (true) {
            $normalised <= 1 => 1,
            $normalised <= 2 => 2,
            $normalised <= 2.5 => 2.5,
            $normalised <= 5 => 5,
            default => 10,
        };

        return $step * $magnitude;
    };

    $top = $niceTop($max);
    $tickCount = 4;
    $ticks = collect(range($tickCount, 0))->map(fn ($i) => $top * $i / $tickCount);

    // Label every nth column so the axis never turns into an unreadable smear.
    $labelEvery = (int) max(1, ceil($rows->count() / 12));

    // Cap bar thickness per the mark spec; the band's leftover stays as air.
    $barMax = 24;
@endphp

<div class="w-full" x-data="{ hovered: null }">

    @if ($rows->isEmpty() || $max <= 0)
        <div class="flex items-center justify-center rounded-lg bg-slate-50 text-sm text-slate-500 dark:bg-slate-800/50 dark:text-slate-400"
            style="height: {{ $height }}px">
            No revenue recorded in this period.
        </div>
    @else
        <div class="flex gap-3">

            {{-- Y axis --}}
            <div class="flex shrink-0 flex-col justify-between py-px text-right text-[11px] tabular-nums text-slate-400 dark:text-slate-500"
                style="height: {{ $height }}px" aria-hidden="true">
                @foreach ($ticks as $tick)
                    <span class="leading-none">{{ number_format($tick) }}</span>
                @endforeach
            </div>

            {{-- Plot + x-axis share one column so the labels cannot drift out
                 of alignment with the columns above them. --}}
            <div class="min-w-0 flex-1">
            <div class="relative" style="height: {{ $height }}px">

                {{-- Recessive hairline gridlines --}}
                <div class="pointer-events-none absolute inset-0 flex flex-col justify-between" aria-hidden="true">
                    @foreach ($ticks as $tick)
                        <div class="h-px w-full bg-slate-200/70 dark:bg-slate-700/50"></div>
                    @endforeach
                </div>

                {{-- Columns. gap-0.5 is the 2px surface gap between adjacent marks. --}}
                <div class="absolute inset-0 flex items-end gap-0.5">
                    @foreach ($rows as $index => $row)
                        @php
                            $pct = $top > 0 ? max(($row['value'] / $top) * 100, $row['value'] > 0 ? 1.5 : 0) : 0;
                        @endphp

                        <div class="group relative flex h-full flex-1 items-end justify-center"
                            style="max-width: {{ $barMax }}px"
                            x-on:mouseenter="hovered = {{ $index }}" x-on:mouseleave="hovered = null"
                            x-on:focus="hovered = {{ $index }}" x-on:blur="hovered = null"
                            tabindex="0" role="img"
                            aria-label="{{ $row['full'] }}: {{ Money::format($row['value']) }}">

                            {{-- Full-height hit target: the bar itself is often
                                 only a few pixels tall and hard to hover. --}}
                            <span class="absolute inset-0" aria-hidden="true"></span>

                            <div class="w-full rounded-t bg-(--chart-series) transition-[height,opacity] duration-300 ease-out group-hover:opacity-80"
                                style="height: {{ $pct }}%"></div>

                            {{-- Tooltip --}}
                            <div x-show="hovered === {{ $index }}" x-cloak x-transition.opacity.duration.100ms
                                class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 w-max max-w-[12rem] -translate-x-1/2 rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs shadow-lg dark:bg-slate-700">
                                <p class="font-medium text-white">{{ Money::format($row['value']) }}</p>
                                <p class="text-slate-300">{{ $row['full'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

                {{-- X axis --}}
                <div class="mt-2 flex gap-0.5">
                    @foreach ($rows as $index => $row)
                        <div class="flex-1 text-center text-[11px] tabular-nums text-slate-400 dark:text-slate-500"
                            style="max-width: {{ $barMax }}px">
                            {{ $index % $labelEvery === 0 ? $row['label'] : '' }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
