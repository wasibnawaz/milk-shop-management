<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealerRequest;
use App\Models\Dealer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $dealers = Dealer::query()
            ->withCount('sales')
            ->when($search, fn ($q, $term) => $q->where(
                fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
            ))
            ->orderBy('name')
            ->paginate(config('shop.per_page'))
            ->withQueryString();

        return view('dealers.index', compact('dealers', 'search'));
    }

    public function create(): View
    {
        return view('dealers.create');
    }

    public function store(StoreDealerRequest $request): RedirectResponse
    {
        $dealer = Dealer::create($request->validated());

        return redirect()
            ->route('dealers.index')
            ->with('success', "Dealer \"{$dealer->name}\" created.");
    }

    public function edit(Dealer $dealer): View
    {
        return view('dealers.edit', compact('dealer'));
    }

    public function update(StoreDealerRequest $request, Dealer $dealer): RedirectResponse
    {
        $dealer->update($request->validated());

        return redirect()
            ->route('dealers.index')
            ->with('success', "Dealer \"{$dealer->name}\" updated.");
    }

    public function destroy(Dealer $dealer): RedirectResponse
    {
        // sales.dealer_id is nullOnDelete, so removing a dealer detaches their
        // sales rather than destroying them.
        $dealer->delete();

        return redirect()
            ->route('dealers.index')
            ->with('success', "Dealer \"{$dealer->name}\" deleted.");
    }
}
