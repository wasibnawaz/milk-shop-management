@php $dealer = $dealer ?? null; @endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <x-field name="name" label="Dealer name" required :value="$dealer?->name" placeholder="e.g. Bilal Dairy Farm" />

    <x-field name="phone" label="Phone" :value="$dealer?->phone" placeholder="0300 1234567" inputmode="tel" />

    <div class="sm:col-span-2">
        <x-field name="address" label="Address" :value="$dealer?->address" placeholder="Street, area or village" />
    </div>

    <div class="sm:col-span-2">
        <label class="flex cursor-pointer items-start gap-3">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $dealer?->is_active ?? true))
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800">
            <span>
                <span class="block text-sm font-medium text-slate-700 dark:text-slate-300">Active</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    Inactive dealers no longer appear on the sale form.
                </span>
            </span>
        </label>
    </div>
</div>
