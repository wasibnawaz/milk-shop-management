<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'from' => $request->date('from')?->toDateString(),
            'to' => $request->date('to')?->toDateString(),
            'status' => $request->string('status')->toString(),
            'product_id' => $request->integer('product_id') ?: null,
        ];

        $query = Sale::query()
            // Eager loaded: the old index rendered dealer/product names inside
            // the loop, which would now be an N+1 across relationships.
            ->with(['product:id,name,unit', 'dealer:id,name'])
            ->search($filters['search'])
            ->between($filters['from'], $filters['to'])
            ->when($filters['status'], fn ($q, $status) => $q->where('payment_status', $status))
            ->when($filters['product_id'], fn ($q, $id) => $q->where('product_id', $id));

        // Aggregate in the database over the *whole* filtered set. The old
        // controller summed an already-loaded collection, which silently
        // becomes "total of the current page" the moment you paginate.
        $totals = (clone $query)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as collected')
            ->selectRaw('COUNT(*) as entries')
            ->first();

        $sales = $query
            ->latest('sale_date')
            ->latest('id')
            ->paginate(config('shop.per_page'))
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'filters' => $filters,
            'revenue' => (float) $totals->revenue,
            'collected' => (float) $totals->collected,
            'outstanding' => (float) $totals->revenue - (float) $totals->collected,
            'entries' => (int) $totals->entries,
            'products' => Product::active()->orderBy('name')->get(['id', 'name']),
            'statuses' => PaymentStatus::options(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Sale::class);

        return view('sales.create', $this->formData());
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $this->authorize('create', Sale::class);

        $sale = Sale::create($request->saleData() + ['user_id' => $request->user()?->id]);

        return redirect()
            ->route('sales.index')
            ->with('success', "Sale #{$sale->id} recorded successfully.");
    }

    /**
     * Route-model binding replaces the old Milk::find($id) with no null check,
     * which threw a 500 for any stale or guessed id. A missing sale is now a 404.
     */
    public function edit(Sale $sale): View
    {
        $this->authorize('update', $sale);

        return view('sales.edit', ['sale' => $sale] + $this->formData());
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $this->authorize('update', $sale);

        $sale->update($request->saleData());

        return redirect()
            ->route('sales.index')
            ->with('success', "Sale #{$sale->id} updated successfully.");
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $this->authorize('delete', $sale);

        // Soft delete — financial history stays recoverable.
        $sale->delete();

        return redirect()
            ->route('sales.index')
            ->with('success', "Sale #{$sale->id} deleted. It can be restored if needed.");
    }

    /**
     * Shared select options for the create/edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'products' => Product::active()->orderBy('name')->get(['id', 'name', 'unit', 'default_rate']),
            'dealers' => Dealer::active()->orderBy('name')->get(['id', 'name']),
            'statuses' => PaymentStatus::options(),
        ];
    }
}
