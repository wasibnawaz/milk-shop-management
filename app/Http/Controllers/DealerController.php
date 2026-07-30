<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealerRequest;
use App\Models\Dealer;
use App\Support\TableSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $sorter = TableSort::make([
            'name' => 'name',
            'phone' => 'phone',
            'sales_count' => 'sales_count',
            'is_active' => 'is_active',
        ], 'name', 'asc');

        $query = Dealer::query()
            ->withCount('sales')
            ->when($search, fn ($q, $term) => $q->where(
                fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
            ));

        $sorter->apply($query, $request);

        return view('dealers.index', [
            'dealers' => $query->paginate(config('shop.per_page'))->withQueryString(),
            'search' => $search,
            'sort' => $sorter->state($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Dealer::class);

        return view('dealers.create');
    }

    public function store(StoreDealerRequest $request): RedirectResponse
    {
        $this->authorize('create', Dealer::class);

        $dealer = Dealer::create($request->validated());

        return redirect()
            ->route('dealers.index')
            ->with('success', "Dealer \"{$dealer->name}\" created.");
    }

    public function edit(Dealer $dealer): View
    {
        $this->authorize('update', $dealer);

        return view('dealers.edit', compact('dealer'));
    }

    public function update(StoreDealerRequest $request, Dealer $dealer): RedirectResponse
    {
        $this->authorize('update', $dealer);

        $dealer->update($request->validated());

        return redirect()
            ->route('dealers.index')
            ->with('success', "Dealer \"{$dealer->name}\" updated.");
    }

    public function destroy(Dealer $dealer): RedirectResponse
    {
        $this->authorize('delete', $dealer);

        // sales.dealer_id is nullOnDelete, so removing a dealer detaches their
        // sales rather than destroying them.
        $dealer->delete();

        return redirect()
            ->route('dealers.index')
            ->with('success', "Dealer \"{$dealer->name}\" deleted.");
    }
}
