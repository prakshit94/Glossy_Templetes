<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Warehouse;
use App\Models\Party;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['party', 'warehouse'])->withCount('items');

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('order_no', 'like', "%$s%")
                  ->orWhereHas('party', function($q) use ($s) {
                      $q->where('name', 'like', "%$s%");
                  });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $statuses = array_filter(array_map('trim', explode(',', $request->status)));
            $query->whereIn('status', $statuses);
        }
        
        if ($request->filled('type')) {
            $types = array_filter(array_map('trim', explode(',', $request->type)));
            $query->whereIn('type', $types);
        }
        
        if ($request->filled('party')) {
            $parties = array_filter(array_map('trim', explode(',', $request->party)));
            $query->whereHas('party', function($q) use ($parties) {
                $q->whereIn('name', $parties);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | STATS & DATA
        |--------------------------------------------------------------------------
        */
        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'shipped' => (clone $query)->where('status', 'shipped')->count(),
        ];

        $perPage = (int) $request->get('perPage', 10);
        $orders = $query->latest()->paginate($perPage)->withQueryString();

        // Get Dynamic Lists for Filters
        $statusesList = Order::distinct()->pluck('status')->filter()->sort()->values();
        $typesList = Order::distinct()->pluck('type')->filter()->sort()->values();
        
        $partiesList = Party::whereHas('orders', function($q) use ($request) {
            if ($request->filled('status')) {
                $q->whereIn('status', array_map('trim', explode(',', $request->status)));
            }
        })->distinct()->pluck('name')->filter()->sort()->values();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('orders.partials.table', compact('orders'))->render(),
                'parties' => $partiesList,
                'stats' => $stats
            ]);
        }

        return view('orders.index', compact(
            'orders', 
            'stats', 
            'statusesList', 
            'typesList', 
            'partiesList'
        ));
    }

    public function edit(Order $order)
    {
        // Currently, you might want to edit the order details, items, etc.
        // We will just redirect to show for now, or you can build an edit view later.
        // For the sake of this task, we will load edit view if it exists, or just redirect to show.
        if (view()->exists('orders.edit')) {
            $warehouses = Warehouse::where('status', 'active')->get();
            $parties = Party::where('status', 'active')->get();
            $products = Product::where('status', 'active')->get();
            return view('orders.edit', compact('order', 'warehouses', 'parties', 'products'));
        }
        return redirect()->route('orders.show', $order)->with('info', 'Edit functionality is integrated into the detail view or coming soon.');
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $parties = Party::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        return view('orders.create', compact('warehouses', 'parties', 'products'));
    }

    public function store(Request $request, \App\Services\OrderService $orderService)
    {
        $items = collect($request->input('items', []))
            ->filter(function ($item) {
                return !empty($item['product_id']) && isset($item['quantity']) && isset($item['unit_price']);
            })
            ->values()
            ->all();

        $request->merge(['items' => $items]);

        $request->validate([
            'type' => 'required|in:sale,purchase',
            'party_id' => 'required|exists:parties,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $party = Party::findOrFail($request->party_id);
        if ($request->type === 'sale' && $party->type !== 'customer') {
            return back()->withInput()->with('error', 'Sale orders require a customer party.');
        }
        if ($request->type === 'purchase' && $party->type !== 'supplier') {
            return back()->withInput()->with('error', 'Purchase orders require a supplier party.');
        }

        $order = $orderService->createOrder([
            'type' => $request->type,
            'party_id' => $request->party_id,
            'warehouse_id' => $request->warehouse_id,
            'order_date' => $request->order_date,
            'items' => $request->items,
        ]);

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    public function show(string $id)
    {
        $order = Order::with(['party', 'warehouse', 'items.product'])->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function confirm(string $id, InventoryService $inventoryService)
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'pending') return back()->with('error', 'Only pending orders can be confirmed.');

        try {
            $inventoryService->confirmOrder($order);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to confirm order.');
        }

        return back()->with('success', 'Order confirmed.');
    }

    public function ship(string $id, InventoryService $inventoryService)
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'confirmed') return back()->with('error', 'Only confirmed orders can be shipped.');

        try {
            $inventoryService->shipOrder($order);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to ship order.');
        }

        return back()->with('success', 'Order shipped and inventory updated.');
    }

    public function receipt(string $id, \App\Services\OrderService $orderService)
    {
        $order = $orderService->getOrderForReceipt((int)$id);
        return view('orders.receipt', compact('order'));
    }
}
