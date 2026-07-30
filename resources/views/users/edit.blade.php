<x-layouts.app title="Edit Staff Account">

    <div class="mx-auto max-w-2xl">
        <x-card :title="$user->name">
            <x-slot:actions>
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    Last seen {{ $user->last_login_at?->diffForHumans() ?? 'never' }}
                </span>
            </x-slot:actions>

            <form method="post" action="{{ route('users.update', $user) }}" novalidate>
                @csrf
                @method('put')

                @include('users._form', ['user' => $user])

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                    <x-button :href="route('users.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Save Changes
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
