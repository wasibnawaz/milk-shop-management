<?php

namespace App\Http\Requests;

use App\Enums\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')
                    ->ignore($this->route('product'))
                    ->whereNull('deleted_at'),
            ],
            'unit' => ['required', Rule::enum(ProductUnit::class)],
            'default_rate' => ['required', 'numeric', 'min:0', 'max:'.config('shop.max_unit_rate')],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // An unchecked checkbox is absent from the payload entirely.
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A product with that name already exists.',
            'default_rate.min' => 'Rate cannot be negative.',
        ];
    }
}
