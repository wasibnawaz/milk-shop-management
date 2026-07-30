<x-layouts.app title="New Product">

    <div class="mx-auto max-w-2xl">
        <x-card title="Add Product">
            <form method="post" action="{{ route('products.store') }}" novalidate>
                @csrf

                @include('products._form', ['product' => null])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('products.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Create Product
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
