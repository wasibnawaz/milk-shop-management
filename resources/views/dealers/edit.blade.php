<x-layouts.app title="Edit Dealer">

    <div class="mx-auto max-w-2xl">
        <x-card :title="$dealer->name">
            <form method="post" action="{{ route('dealers.update', $dealer) }}" novalidate>
                @csrf
                @method('put')

                @include('dealers._form', ['dealer' => $dealer])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('dealers.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Save Changes
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
