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

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('order_no', 'like', "%$s%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
        ];

        return view('orders.index', compact('orders', 'stats'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $parties = Party::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        return view('orders.create', compact('warehouses', 'parties', 'products'));
    }

    public function store(Request $request)
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

        DB::transaction(function() use ($request) {
            $order = Order::create([
                'order_no' => 'ORD-' . strtoupper(uniqid()),
                'type' => $request->type,
                'party_id' => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'order_date' => $request->order_date,
                'status' => 'pending',
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $total += $lineTotal;

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $lineTotal,
                ]);
            }
            
            $order->update(['total_amount' => $total, 'net_amount' => $total]);
        });

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
}
