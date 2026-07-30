<?php

namespace App\Http\Controllers;

use App\Enums\ProductUnit;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $products = Product::query()
            ->withCount('sales')
            ->when($search, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate(config('shop.per_page'))
            ->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('products.create', ['units' => ProductUnit::options()]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = Product::create($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$product->name}\" created.");
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', [
            'product' => $product,
            'units' => ProductUnit::options(),
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $product->update($request->validated());

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$product->name}\" updated.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        // The sales.product_id foreign key is restrictOnDelete, so a product
        // with history cannot be removed. Deactivating hides it from the sale
        // form while leaving past sales intact and reportable.
        if ($product->sales()->exists()) {
            $product->update(['is_active' => false]);

            return redirect()
                ->route('products.index')
                ->with('info', "\"{$product->name}\" has recorded sales, so it was deactivated instead of deleted.");
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "Product \"{$product->name}\" deleted.");
    }
}
