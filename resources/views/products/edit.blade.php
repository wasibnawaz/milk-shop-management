<x-layouts.app title="Edit Product">

    <div class="mx-auto max-w-2xl">
        <x-card :title="$product->name">
            <form method="post" action="{{ route('products.update', $product) }}" novalidate>
                @csrf
                @method('put')

                @include('products._form', ['product' => $product])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('products.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Save Changes
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
