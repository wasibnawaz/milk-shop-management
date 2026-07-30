@props(['code', 'title', 'message'])

<x-layouts.guest :title="$code.' — '.$title">
    <div class="text-center">
        <p class="text-6xl font-bold tracking-tight text-brand-600 tabular-nums dark:text-brand-400">
            {{ $code }}
        </p>

        <h2 class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h2>

        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $message }}</p>

        <div class="mt-6 flex flex-col-reverse justify-center gap-3 sm:flex-row">
            <x-button href="javascript:history.back()" variant="secondary">Go Back</x-button>
            <x-button :href="auth()->check() ? route('dashboard') : route('login')">
                {{ auth()->check() ? 'Dashboard' : 'Sign In' }}
            </x-button>
        </div>
    </div>
</x-layouts.guest>
