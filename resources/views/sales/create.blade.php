<x-layouts.app title="Record Sale">

    <div class="mx-auto max-w-3xl">
        <x-card title="New Sale Entry">
            <form method="post" action="{{ route('sales.store') }}" novalidate>
                @csrf

                @include('sales._form', ['sale' => null])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('sales.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Record Sale
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
