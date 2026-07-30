<x-layouts.app title="Edit Sale">

    <div class="mx-auto max-w-3xl">
        <x-card title="Sale #{{ $sale->id }}">
            <x-slot:actions>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    Recorded {{ $sale->created_at->diffForHumans() }}
                </span>
            </x-slot:actions>

            <form method="post" action="{{ route('sales.update', $sale) }}" novalidate>
                @csrf
                @method('put')

                @include('sales._form', ['sale' => $sale])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('sales.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Save Changes
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
