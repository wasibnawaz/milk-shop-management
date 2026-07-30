@props(['action', 'confirm' => 'Are you sure? This cannot be undone from the interface.'])

{{--
    Delete control. The original had no confirmation at all — one click removed
    a record permanently. Alpine guards the submit; the record is soft deleted
    server-side, so it stays recoverable in the database either way.
--}}
<form method="post" action="{{ $action }}" x-data
    x-on:submit.prevent="if (window.confirm(@js($confirm))) $el.submit()" class="inline">
    @csrf
    @method('delete')

    <button type="submit"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg p-2 text-slate-500 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-400']) }}>
        <span class="sr-only">Delete</span>
        <x-icon name="trash" class="h-4 w-4" />
    </button>
</form>
