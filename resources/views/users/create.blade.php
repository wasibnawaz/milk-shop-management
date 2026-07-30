<x-layouts.app title="New Staff Account">

    <div class="mx-auto max-w-2xl">
        <x-card title="Create Account">
            <form method="post" action="{{ route('users.store') }}" novalidate>
                @csrf

                @include('users._form', ['user' => null])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('users.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Create Account
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
