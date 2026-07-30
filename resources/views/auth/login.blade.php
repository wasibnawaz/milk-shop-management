<x-layouts.guest title="Sign In">
    <x-slot:subtitle>Sign in to manage your shop</x-slot:subtitle>

    <form method="post" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        <x-field name="email" label="Email address" type="email" required autofocus autocomplete="username"
            placeholder="you@example.com" />

        <div>
            <x-field name="password" label="Password" type="password" required autocomplete="current-password"
                placeholder="••••••••" />

            <div class="mt-2 flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                        class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Remember me</span>
                </label>

                <a href="{{ route('password.request') }}"
                    class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                    Forgot password?
                </a>
            </div>
        </div>

        <x-button type="submit" class="w-full justify-center" size="lg">Sign In</x-button>
    </form>

    <p class="mt-6 border-t border-slate-200 pt-5 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
        Accounts are created by an administrator. Contact the shop owner if you need access.
    </p>
</x-layouts.guest>
