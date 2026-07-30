@php
    $user = $user ?? null;
    $isSelf = $user && $user->is(auth()->user());
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field name="name" label="Full name" required :value="$user?->name" autocomplete="name" />

    <x-field name="email" label="Email address" type="email" required :value="$user?->email"
        autocomplete="username" />

    <x-field name="password" label="{{ $user ? 'New password' : 'Password' }}" type="password"
        :required="! $user" autocomplete="new-password"
        :hint="$user ? 'Leave blank to keep the current password.' : 'At least 8 characters.'" />

    <x-field name="password_confirmation" label="Confirm password" type="password" :required="! $user"
        autocomplete="new-password" />

    <div class="sm:col-span-2">
        <x-field name="role" label="Role" type="select" required :options="$roles"
            :value="$user?->role?->value ?? 'cashier'" :disabled="$isSelf"
            :hint="$isSelf ? 'You cannot change your own role.' : null" />

        @if ($isSelf)
            {{-- Disabled inputs are not submitted; resend the value so the
                 update request still validates. --}}
            <input type="hidden" name="role" value="{{ $user->role->value }}">
        @endif

        <div class="mt-3 space-y-1.5 rounded-lg bg-slate-50 p-3 text-xs dark:bg-slate-800/50">
            @foreach (\App\Enums\UserRole::cases() as $case)
                <p class="text-slate-600 dark:text-slate-400">
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $case->label() }}</span>
                    — {{ $case->description() }}
                </p>
            @endforeach
        </div>
    </div>

    <div class="sm:col-span-2">
        <label class="flex cursor-pointer items-start gap-3 {{ $isSelf ? 'opacity-60' : '' }}">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true))
                @disabled($isSelf)
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800">
            <span>
                <span class="block text-sm font-medium text-slate-700 dark:text-slate-300">Active</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    {{ $isSelf
                        ? 'You cannot deactivate your own account.'
                        : 'Disabled accounts are signed out immediately and cannot sign back in.' }}
                </span>
            </span>
        </label>

        @if ($isSelf)
            <input type="hidden" name="is_active" value="1">
        @endif
    </div>
</div>
