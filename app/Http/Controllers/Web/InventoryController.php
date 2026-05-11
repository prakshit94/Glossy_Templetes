<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::with(['product', 'warehouse']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('product', function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%");
            });
        }

        $stocks = $query->paginate(15)->withQueryString();
        $warehouses = Warehouse::all();

        if ($request->ajax()) {
            return view('inventory.partials.table', compact('stocks'))->render();
        }

        return view('inventory.index', compact('stocks', 'warehouses'));
    }

    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
        ]);

        $stock->update([
            'quantity' => $request->quantity,
        ]);

        return back()->with('success', 'Inventory updated successfully.');
    }
}
