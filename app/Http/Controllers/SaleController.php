<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use App\Support\Money;
use App\Support\TableSort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $sort = $this->sorter()->state($request);

        $query = $this->filteredQuery($request)
            ->with(['product:id,name,unit', 'dealer:id,name']);

        $this->sorter()->apply($query, $request);

        // Aggregate in the database over the *whole* filtered set, not the
        // page. Cloned before pagination so the two cannot drift apart.
        $totals = $this->filteredQuery($request)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as collected')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
            ->selectRaw('COUNT(*) as entries')
            ->first();

        $perPage = $this->perPage($request);

        return view('sales.index', [
            'sales' => $query->paginate($perPage)->withQueryString(),
            'filters' => $filters,
            'sort' => $sort,
            'perPage' => $perPage,
            'revenue' => (float) $totals->revenue,
            'collected' => (float) $totals->collected,
            'outstanding' => (float) $totals->revenue - (float) $totals->collected,
            'entries' => (int) $totals->entries,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'dealers' => Dealer::orderBy('name')->get(['id', 'name']),
            'statuses' => PaymentStatus::options(),
        ]);
    }

    /**
     * Stream the filtered set as CSV. Streamed and chunked rather than built
     * in memory so a multi-year export cannot exhaust the PHP memory limit.
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Sale::class);

        $query = $this->filteredQuery($request)->with(['product:id,name,unit', 'dealer:id,name', 'user:id,name']);
        $this->sorter()->apply($query, $request);

        $filename = 'sales-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');

            // BOM so Excel opens UTF-8 correctly.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID', 'Date', 'Product', 'Unit', 'Customer', 'Dealer',
                'Quantity', 'Rate', 'Total', 'Paid', 'Outstanding',
                'Status', 'Recorded By', 'Notes',
            ]);

            $query->chunk(500, function ($sales) use ($handle): void {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->id,
                        $sale->sale_date->toDateString(),
                        $sale->product->name,
                        $sale->product->unit->abbreviation(),
                        $sale->customer_name,
                        $sale->dealer?->name,
                        Money::quantity($sale->quantity),
                        number_format((float) $sale->unit_rate, 2, '.', ''),
                        number_format((float) $sale->total_amount, 2, '.', ''),
                        number_format((float) $sale->amount_paid, 2, '.', ''),
                        number_format($sale->outstanding, 2, '.', ''),
                        $sale->payment_status->label(),
                        $sale->user?->name,
                        $sale->notes,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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

    /*
    |--------------------------------------------------------------------------
    | Query helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'from' => $request->date('from')?->toDateString(),
            'to' => $request->date('to')?->toDateString(),
            'status' => $request->string('status')->toString(),
            'product_id' => $request->integer('product_id') ?: null,
            'dealer_id' => $request->integer('dealer_id') ?: null,
        ];
    }

    /** @return Builder<Sale> */
    private function filteredQuery(Request $request): Builder
    {
        $filters = $this->filters($request);

        return Sale::query()
            ->search($filters['search'])
            ->between($filters['from'], $filters['to'])
            ->when($filters['status'], fn (Builder $q, $status) => $q->where('payment_status', $status))
            ->when($filters['product_id'], fn (Builder $q, $id) => $q->where('product_id', $id))
            ->when($filters['dealer_id'], fn (Builder $q, $id) => $q->where('dealer_id', $id));
    }

    private function sorter(): TableSort
    {
        return TableSort::make([
            'sale_date' => 'sale_date',
            'customer_name' => 'customer_name',
            'quantity' => 'quantity',
            'unit_rate' => 'unit_rate',
            'total_amount' => 'total_amount',
            'payment_status' => 'payment_status',

            // Sorting by a related name without a join, so the relation stays
            // eager loaded and pagination counts stay correct.
            'product' => fn (Builder $q, string $dir) => $q->orderBy(
                Product::select('name')->whereColumn('products.id', 'sales.product_id'), $dir
            ),
            'dealer' => fn (Builder $q, string $dir) => $q->orderBy(
                Dealer::select('name')->whereColumn('dealers.id', 'sales.dealer_id'), $dir
            ),
        ], 'sale_date');
    }

    private function perPage(Request $request): int
    {
        $requested = $request->integer('per_page');

        return in_array($requested, config('shop.per_page_options'), true)
            ? $requested
            : config('shop.per_page');
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
