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
        
        $stats = [
            'total' => Stock::count(),
            'low_stock' => Stock::where('quantity', '<=', 10)->count(),
            'out_of_stock' => Stock::where('quantity', '<=', 0)->count(),
            'warehouses_count' => Warehouse::count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('inventory.partials.table', compact('stocks'))->render(),
                'stats' => $stats
            ]);
        }

        return view('inventory.index', compact('stocks', 'warehouses', 'stats'));
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
