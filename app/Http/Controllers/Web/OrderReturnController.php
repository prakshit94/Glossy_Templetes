<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderReturn::with(['order.party', 'order.warehouse'])->withCount('items');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('return_no', 'LIKE', "%{$s}%")
                  ->orWhereHas('order', function ($oq) use ($s) {
                      $oq->where('order_no', 'LIKE', "%{$s}%")
                         ->orWhereHas('party', function ($pq) use ($s) {
                             $pq->where('firstname', 'LIKE', "%{$s}%")
                                ->orWhere('lastname', 'LIKE', "%{$s}%")
                                ->orWhere('company_name', 'LIKE', "%{$s}%")
                                ->orWhere('phone', 'LIKE', "%{$s}%");
                         });
                  });
            });
        }

        if ($request->filled('status')) {
            $statuses = array_filter(array_map('trim', explode(',', $request->status)));
            $query->whereIn('status', $statuses);
        }

        $stats = [
            'total'     => (clone $query)->count(),
            'requested' => (clone $query)->where('status', 'requested')->count(),
            'received'  => (clone $query)->where('status', 'received')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];

        $perPage = (int) $request->get('perPage', 10);
        $returns = $query->latest()->paginate($perPage)->withQueryString();
        $statusesList = ['requested', 'received', 'inspected', 'completed', 'rejected'];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('returns.partials.table', compact('returns'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('returns.index', compact('returns', 'stats', 'statusesList'));
    }

    public function create(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = null;
        
        if ($orderId) {
            $order = Order::with('items.product')->findOrFail($orderId);
            if (!in_array($order->status, array_merge(Order::inTransitStatuses(), ['delivered', 'processing']), true)) {
                return redirect()->route('orders.show', $order)->with('error', 'Only dispatched, delivered, or processing orders can be returned.');
            }
        }
        
        $orders = Order::whereIn('status', array_merge(Order::inTransitStatuses(), ['delivered', 'processing']))
            ->latest()
            ->limit(50)
            ->get();

        return view('returns.create', compact('order', 'orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason'   => 'nullable|string',
            'items'    => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
        ]);

        $order = Order::with('items')->findOrFail($request->order_id);

        try {
            DB::transaction(function () use ($request, $order) {
                $returnNo = 'RET-' . strtoupper(uniqid());
                
                $orderReturn = OrderReturn::create([
                    'return_no' => $returnNo,
                    'order_id'  => $order->id,
                    'reason'    => $request->reason,
                    'status'    => 'requested',
                    'refund_amount' => 0,
                ]);

                $totalRefund = 0;

                foreach ($request->items as $itemData) {
                    $orderItem = $order->items->firstWhere('id', $itemData['order_item_id']);
                    
                    if (!$orderItem) {
                        throw ValidationException::withMessages(['items' => "Invalid order item selected."]);
                    }

                    if ($itemData['quantity'] > $orderItem->quantity) {
                        throw ValidationException::withMessages(['items' => "Return quantity cannot exceed order quantity for item {$orderItem->product_id}."]);
                    }

                    OrderReturnItem::create([
                        'return_id'     => $orderReturn->id,
                        'order_item_id' => $orderItem->id,
                        'quantity'      => $itemData['quantity'],
                    ]);

                    $totalRefund += $itemData['quantity'] * $orderItem->unit_price;
                }

                $orderReturn->update(['refund_amount' => $totalRefund]);
                
                // Set order status to returned
                $order->update(['status' => 'returned']);
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('returns.index')->with('success', 'Return request created successfully.');
    }

    public function show($id)
    {
        $return = OrderReturn::with(['order.party', 'order.warehouse', 'items.orderItem.product'])->findOrFail($id);
        return view('returns.show', compact('return'));
    }

    public function updateStatus(Request $request, $id, InventoryService $inventoryService)
    {
        $request->validate([
            'status' => 'required|in:requested,received,inspected,completed,rejected',
        ]);

        $return = OrderReturn::with(['items.orderItem.product', 'order'])->findOrFail($id);
        
        if ($return->status === 'completed' || $return->status === 'rejected') {
            return back()->with('error', 'Cannot update status of a completed or rejected return.');
        }

        try {
            DB::transaction(function () use ($request, $return, $inventoryService) {
                $newStatus = $request->status;

                // When goods are received/completed, we should put them back into inventory if applicable.
                // Assuming completed means stock is returned.
                if ($newStatus === 'completed' && $return->status !== 'completed') {
                    foreach ($return->items as $returnItem) {
                        $orderItem = $returnItem->orderItem;
                        // Add stock back to the warehouse using the single source of truth InventoryService
                        $inventoryService->addStock(
                            $orderItem->product_id,
                            $return->order->warehouse_id,
                            $returnItem->quantity,
                            \App\Models\OrderReturn::class,
                            $return->id
                        );
                    }

                    // Auto-generate Refund Requests for paid amounts up to refund_amount
                    if ($return->refund_amount > 0) {
                        $payments = \App\Models\Payment::where('order_id', $return->order_id)
                            ->where('status', 'completed')
                            ->orderBy('id', 'desc')
                            ->get();
                        
                        $amountToRefund = $return->refund_amount;

                        foreach ($payments as $payment) {
                            if ($amountToRefund <= 0) break;
                            
                            $existingRefunds = $payment->refunds()->whereIn('status', ['pending', 'processed'])->sum('amount');
                            $refundableOnPayment = $payment->amount - $existingRefunds;

                            if ($refundableOnPayment > 0) {
                                $refundAmountForThisPayment = min($amountToRefund, $refundableOnPayment);

                                \App\Models\Refund::create([
                                    'payment_id' => $payment->id,
                                    'amount' => $refundAmountForThisPayment,
                                    'reason' => "Automatic refund for Return {$return->return_no}",
                                    'status' => 'pending'
                                ]);

                                $amountToRefund -= $refundAmountForThisPayment;
                            }
                        }
                    }
                }

                $return->update(['status' => $newStatus]);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating return status: ' . $e->getMessage());
        }

        return back()->with('success', "Return status updated to {$request->status}.");
    }
}
