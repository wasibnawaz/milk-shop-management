{{--
    Theme switcher. Reads and writes the Alpine `theme` store defined in
    resources/js/theme.js, which persists the choice to localStorage.
--}}
<button type="button" x-data x-on:click="$store.theme.toggle()"
    class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
    :aria-label="$store.theme.active === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'">

    <x-icon name="sun" class="h-5 w-5 rotate-0 scale-100 transition-transform duration-200 dark:-rotate-90 dark:scale-0" />
    <x-icon name="moon"
        class="absolute inset-0 m-auto h-5 w-5 rotate-90 scale-0 transition-transform duration-200 dark:rotate-0 dark:scale-100" />
</button>
