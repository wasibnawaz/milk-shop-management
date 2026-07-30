<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route-level `auth` middleware gates access; per-record policies
        // arrive with roles in Milestone 2.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],

            'dealer_id' => ['nullable', Rule::exists('dealers', 'id')->whereNull('deleted_at')],

            'customer_name' => ['nullable', 'string', 'max:255'],

            // min:0.001 rather than min:0 — a zero-quantity sale is meaningless
            // and would silently contribute nothing to totals.
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:'.config('shop.max_quantity')],

            // The original rules allowed negative prices, which corrupted the
            // earnings total, and had no upper bound, so anything over the
            // DECIMAL range threw a raw SQL error.
            'unit_rate' => ['required', 'numeric', 'min:0', 'max:'.config('shop.max_unit_rate')],

            'payment_status' => ['required', Rule::enum(PaymentStatus::class)],

            'amount_paid' => ['nullable', 'numeric', 'min:0'],

            'sale_date' => ['required', 'date', 'before_or_equal:today'],

            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Please choose a product.',
            'product_id.exists' => 'That product no longer exists.',
            'dealer_id.exists' => 'That dealer no longer exists.',
            'quantity.min' => 'Quantity must be greater than zero.',
            'unit_rate.min' => 'Rate cannot be negative.',
            'sale_date.before_or_equal' => 'A sale cannot be dated in the future.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'dealer_id' => 'dealer',
            'unit_rate' => 'rate',
            'sale_date' => 'sale date',
            'amount_paid' => 'amount paid',
        ];
    }

    /**
     * Translate the chosen payment status into a concrete amount_paid, so the
     * model has a single source of truth to derive the stored status from.
     */
    protected function passedValidation(): void
    {
        $total = round((float) $this->input('quantity') * (float) $this->input('unit_rate'), 2);

        $amountPaid = match ($this->enum('payment_status', PaymentStatus::class)) {
            PaymentStatus::Paid => $total,
            PaymentStatus::Unpaid => 0.0,
            PaymentStatus::Partial => min((float) $this->input('amount_paid', 0), $total),
            default => 0.0,
        };

        $this->merge(['amount_paid' => $amountPaid]);
    }

    /**
     * The payload the controller hands to the model. payment_status and
     * total_amount are intentionally excluded — the model derives both.
     *
     * @return array<string, mixed>
     */
    public function saleData(): array
    {
        return [
            'product_id' => $this->integer('product_id'),
            'dealer_id' => $this->input('dealer_id') ?: null,
            'customer_name' => $this->input('customer_name'),
            'quantity' => $this->float('quantity'),
            'unit_rate' => $this->float('unit_rate'),
            'amount_paid' => (float) $this->input('amount_paid', 0),
            'sale_date' => $this->date('sale_date'),
            'notes' => $this->input('notes'),
        ];
    }
}
