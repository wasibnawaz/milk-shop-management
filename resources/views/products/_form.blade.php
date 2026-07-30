@php $product = $product ?? null; @endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-field name="name" label="Product name" required :value="$product?->name"
            placeholder="e.g. Fresh Cow Milk" />
    </div>

    <x-field name="unit" label="Sold by" type="select" required :options="$units" :value="$product?->unit?->value"
        hint="Litre and kilogram allow fractional quantities." />

    <x-field name="default_rate" label="Default rate" type="number" required step="0.01" min="0"
        :value="$product?->default_rate" :prefix="config('shop.currency_symbol')"
        hint="Pre-fills the sale form; can be overridden per sale." />

    <div class="sm:col-span-2">
        <label class="flex cursor-pointer items-start gap-3">
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $product?->is_active ?? true))
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800">
            <span>
                <span class="block text-sm font-medium text-slate-700 dark:text-slate-300">Active</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">
                    Inactive products stay in reports but no longer appear on the sale form.
                </span>
            </span>
        </label>
    </div>
</div>
