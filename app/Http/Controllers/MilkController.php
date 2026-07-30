<?php

namespace App\Http\Controllers;

use App\Models\Milk;
use Illuminate\Http\Request;

class MilkController extends Controller
{
    public function addMilk(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'dealer_name' => 'required|string',
            'product_name' => 'required|string'
        ]);

        // Create new milk entry
        $milk = new Milk();
        $milk->name = $request->input('name');
        $milk->price = $request->input('price');
        $milk->dealer_name = $request->input('dealer_name');
        $milk->product_name = $request->input('product_name');
        $milk->save();

        return redirect()->route('milk.add')->with('success', 'Milk added successfully');
    }

    public function viewEarnings()
    {
        // Fetch all milk entries
        $milks = Milk::all();

        // Calculate total earnings
        $totalEarnings = $milks->sum('price');

        return view('milk.index', compact('milks', 'totalEarnings'));
    }

    public function editMilk($id)
    {
        $milk = Milk::find($id);

        return view('milk.edit', compact('milk'));
    }

    public function updateMilk(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'dealer_name' => 'required|string',
            'product_name' => 'required|string'
        ]);

        $milk = Milk::find($id);

        $milk->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'dealer_name' => $request->input('dealer_name'),
            'product_name' => $request->input('product_name'),
        ]);

        return redirect()->route('milk.add')->with('success', 'Milk updated successfully');
    }
    public function deleteMilk($id)
    {
        $milk = Milk::find($id);

        if (!$milk) {
            return redirect()->route('milk.index')->with('error', 'Milk not found');
        }

        $milk->delete();

        return redirect()->route('milk.index')->with('success', 'Milk deleted successfully');
    }
}
