<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function index(Request $request)
    {
        $taxRates = TaxRate::latest()->get();
        
        $stats = [
            'total' => TaxRate::count(),
            'active' => TaxRate::where('status', 'active')->count(),
            'avg_rate' => round(TaxRate::avg('rate') ?? 0, 2),
        ];

        return view('tax-rates.index', compact('taxRates', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:tax_rates',
            'rate' => 'required|numeric|min:0',
            'status' => 'required|string',
        ]);

        TaxRate::create($data);

        return back()->with('success', 'Tax Rate created successfully.');
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:tax_rates,name,' . $taxRate->id,
            'rate' => 'required|numeric|min:0',
            'status' => 'required|string',
        ]);

        $taxRate->update($data);

        return back()->with('success', 'Tax Rate updated successfully.');
    }

    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return back()->with('success', 'Tax Rate deleted successfully.');
    }
}
