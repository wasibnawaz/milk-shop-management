<x-layouts.app title="New Dealer">

    <div class="mx-auto max-w-2xl">
        <x-card title="Add Dealer">
            <form method="post" action="{{ route('dealers.store') }}" novalidate>
                @csrf

                @include('dealers._form', ['dealer' => null])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('dealers.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Create Dealer
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
