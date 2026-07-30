<x-layouts.app title="My Profile">

    <div class="mx-auto max-w-2xl space-y-6">

        {{-- Account details --}}
        <x-card title="Account Details">
            <form method="post" action="{{ route('profile.update') }}" novalidate>
                @csrf
                @method('put')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field name="name" label="Full name" required :value="$user->name" autocomplete="name" />
                    <x-field name="email" label="Email address" type="email" required :value="$user->email"
                        autocomplete="username" />
                </div>

                <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-200 pt-5 dark:border-slate-800">
                    <span
                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $user->role->badgeClasses() }}">
                        {{ $user->role->label() }}
                    </span>
                    <x-button type="submit">
                        <x-icon name="check" class="h-4 w-4" /> Save
                    </x-button>
                </div>
            </form>
        </x-card>

        {{-- Password --}}
        <x-card title="Change Password">
            <form method="post" action="{{ route('profile.password') }}" novalidate>
                @csrf
                @method('put')

                <div class="grid grid-cols-1 gap-5">
                    <x-field name="current_password" label="Current password" type="password" required
                        autocomplete="current-password" />

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-field name="password" label="New password" type="password" required
                            autocomplete="new-password" hint="At least 8 characters." />
                        <x-field name="password_confirmation" label="Confirm new password" type="password" required
                            autocomplete="new-password" />
                    </div>
                </div>

                <div class="mt-5 flex justify-end border-t border-slate-200 pt-5 dark:border-slate-800">
                    <x-button type="submit">
                        <x-icon name="lock" class="h-4 w-4" /> Update Password
                    </x-button>
                </div>
            </form>
        </x-card>

        {{-- Danger zone --}}
        <x-card title="Close Account">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Closing your account signs you out permanently. Sales you recorded are kept for the
                shop's records.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-4" x-data
                x-on:submit.prevent="if (window.confirm('Close your account? This cannot be undone.')) $el.submit()"
                novalidate>
                @csrf
                @method('delete')

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <x-field name="password" label="Confirm with your password" type="password" required
                            autocomplete="current-password" />
                    </div>
                    <x-button type="submit" variant="danger">
                        <x-icon name="trash" class="h-4 w-4" /> Close Account
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

</x-layouts.app>
