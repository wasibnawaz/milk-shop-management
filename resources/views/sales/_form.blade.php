@php
    $sale = $sale ?? null;

    // Product metadata drives the live total and the quantity step.
    $productMeta = $products->mapWithKeys(fn ($p) => [
        $p->id => [
            'rate' => (float) $p->default_rate,
            'unit' => $p->unit->abbreviation(),
            'step' => $p->unit->allowsFractions() ? '0.001' : '1',
        ],
    ]);

    $initial = [
        'productId' => (string) old('product_id', $sale?->product_id ?? ''),
        'quantity' => (float) old('quantity', $sale?->quantity ?? 1),
        'rate' => (float) old('unit_rate', $sale?->unit_rate ?? 0),
        'status' => old('payment_status', $sale?->payment_status?->value ?? 'paid'),
        'amountPaid' => (float) old('amount_paid', $sale?->amount_paid ?? 0),
    ];
@endphp

<div x-data="{
        meta: @js($productMeta),
        productId: @js($initial['productId']),
        quantity: @js($initial['quantity']),
        rate: @js($initial['rate']),
        status: @js($initial['status']),
        amountPaid: @js($initial['amountPaid']),

        get total() {
            const value = Math.round((Number(this.quantity) || 0) * (Number(this.rate) || 0) * 100) / 100;

            // `0 * -5` is -0, which formats as the nonsensical '-0.00'.
            return value === 0 ? 0 : value;
        },
        get unit() {
            return this.meta[this.productId]?.unit ?? '';
        },
        get step() {
            return this.meta[this.productId]?.step ?? '0.001';
        },
        get formattedTotal() {
            return this.total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        // Prefill the rate from the product's default, but never overwrite a
        // rate the user has already typed for this sale.
        onProductChange() {
            if (!this.rate && this.meta[this.productId]) {
                this.rate = this.meta[this.productId].rate;
            }
        },
    }"
    class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    <x-field name="product_id" label="Product" type="select" required :options="$products->pluck('name', 'id')"
        x-model="productId" x-on:change="onProductChange()">
        <option value="">Select a product…</option>
    </x-field>

    <x-field name="dealer_id" label="Dealer" type="select" :options="$dealers->pluck('name', 'id')"
        hint="Optional — who supplied this stock.">
        <option value="">No dealer</option>
    </x-field>

    <x-field name="quantity" label="Quantity" type="number" required x-model="quantity" :step="0.001" min="0.001"
        x-bind:step="step" />

    <x-field name="unit_rate" label="Rate per unit" type="number" required x-model="rate" step="0.01" min="0"
        :prefix="config('shop.currency_symbol')" />

    <x-field name="customer_name" label="Customer name" placeholder="Leave blank for walk-in customers" />

    <x-field name="sale_date" label="Sale date" type="date" required
        :value="$sale?->sale_date?->toDateString() ?? now()->toDateString()" :max="now()->toDateString()" />

    {{-- Live total. Recomputed client-side for feedback; the server always
         recalculates it on save, so a tampered value cannot be submitted. --}}
    <div class="sm:col-span-2">
        <div
            class="flex items-center justify-between gap-3 rounded-xl bg-brand-50 px-4 py-3.5 ring-1 ring-inset ring-brand-600/10 dark:bg-brand-500/10 dark:ring-brand-400/20">
            <div>
                <p class="text-xs font-medium tracking-wide text-brand-700 uppercase dark:text-brand-300">Total</p>
                <p class="mt-0.5 text-xs text-brand-600/80 dark:text-brand-400/80">
                    <span x-text="quantity || 0"></span><span x-text="unit ? ' ' + unit : ''"></span>
                    &times; {{ config('shop.currency_symbol') }}<span x-text="rate || 0"></span>
                </p>
            </div>
            <p class="text-2xl font-semibold tabular-nums text-brand-800 dark:text-brand-200">
                {{ config('shop.currency_symbol') }} <span x-text="formattedTotal">0.00</span>
            </p>
        </div>
    </div>

    <x-field name="payment_status" label="Payment status" type="select" required :options="$statuses"
        x-model="status" />

    {{-- Only relevant for a partial payment. --}}
    <div x-show="status === 'partial'" x-cloak x-transition.opacity>
        <x-field name="amount_paid" label="Amount paid" type="number" step="0.01" min="0" x-model="amountPaid"
            :prefix="config('shop.currency_symbol')"
            hint="How much the customer has paid so far." />
    </div>

    <div class="sm:col-span-2">
        <x-field name="notes" label="Notes" type="textarea" placeholder="Anything worth remembering about this sale" />
    </div>
</div>
