<x-layouts.guest title="Reset Password">
    <x-slot:subtitle>Choose a new password</x-slot:subtitle>

    <form method="post" action="{{ route('password.store') }}" class="space-y-5" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-field name="email" label="Email address" type="email" required :value="$email" autocomplete="username" />

        <x-field name="password" label="New password" type="password" required autofocus autocomplete="new-password"
            hint="At least 8 characters." />

        <x-field name="password_confirmation" label="Confirm new password" type="password" required
            autocomplete="new-password" />

        <x-button type="submit" class="w-full justify-center" size="lg">Reset Password</x-button>
    </form>
</x-layouts.guest>
