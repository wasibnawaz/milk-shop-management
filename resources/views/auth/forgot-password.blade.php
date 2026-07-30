<x-layouts.guest title="Forgot Password">
    <x-slot:subtitle>Reset your password</x-slot:subtitle>

    <p class="mb-5 text-sm text-slate-600 dark:text-slate-400">
        Enter your email address and we will send you a link to choose a new password.
    </p>

    <form method="post" action="{{ route('password.email') }}" class="space-y-5" novalidate>
        @csrf

        <x-field name="email" label="Email address" type="email" required autofocus autocomplete="username"
            placeholder="you@example.com" />

        <x-button type="submit" class="w-full justify-center" size="lg">Send Reset Link</x-button>
    </form>

    <div class="mt-6 border-t border-slate-200 pt-5 text-center dark:border-slate-800">
        <a href="{{ route('login') }}"
            class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
            Back to sign in
        </a>
    </div>
</x-layouts.guest>
