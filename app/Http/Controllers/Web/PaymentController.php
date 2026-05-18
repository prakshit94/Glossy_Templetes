<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments with filters and searching.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Payment::with(['invoice', 'order.party']);

        // Search (payment no, transaction id, invoice no, order no, party name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function ($iq) use ($search) {
                      $iq->where('invoice_no', 'like', "%{$search}%");
                  })
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_no', 'like', "%{$search}%")
                         ->orWhereHas('party', function ($pq) use ($search) {
                             $pq->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $statuses = explode(',', $request->input('status'));
            $query->whereIn('status', $statuses);
        }

        // Method filter
        if ($request->filled('payment_method')) {
            $methods = explode(',', $request->input('payment_method'));
            $query->whereIn('payment_method', $methods);
        }

        // Date range filters
        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->input('end_date'));
        }

        // Pagination
        $perPage = (int) $request->input('perPage', 15);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 15;

        $payments = $query->latest('payment_date')->paginate($perPage)->withQueryString();

        // Calculate statistics
        $stats = [
            'total_count' => Payment::count(),
            'completed_count' => Payment::where('status', 'completed')->count(),
            'pending_count' => Payment::where('status', 'pending')->count(),
            'failed_count' => Payment::where('status', 'failed')->count(),
            'refunded_count' => Payment::where('status', 'refunded')->count(),
            'total_amount' => (float) Payment::where('status', 'completed')->sum('amount'),
            'pending_amount' => (float) Payment::where('status', 'pending')->sum('amount'),
            'refunded_amount' => (float) Payment::where('status', 'refunded')->sum('amount'),
        ];

        $statusesList = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded'
        ];

        $methodsList = [
            'Cash' => 'Cash',
            'Card' => 'Credit / Debit Card',
            'UPI' => 'UPI / QR Code',
            'Net Banking' => 'Net Banking',
            'Wallet' => 'Wallet / Others',
        ];

        // Also fetch active orders/invoices for the create modal dropdown
        $ordersList = Order::with('party')->latest()->limit(100)->get();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('payments.partials.table', compact('payments'))->render(),
                'stats' => $stats
            ]);
        }

        return view('payments.index', compact('payments', 'stats', 'statusesList', 'methodsList', 'ordersList'));
    }

    /**
     * Store a newly recorded payment in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::with('invoice')->findOrFail($request->order_id);

            $payment = Payment::create([
                'payment_no' => 'PAY-' . strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'invoice_id' => $order->invoice?->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id ?? ('TXN-' . strtoupper(Str::random(10))),
                'payment_date' => $request->payment_date,
                'status' => $request->status,
            ]);

            // If completed, update accounting ledgers if available
            if ($payment->status === 'completed') {
                $cashLedger = DB::table('ledgers')->where('code', 'CASH-001')->first();
                $salesLedger = DB::table('ledgers')->where('code', 'SALES-001')->first();

                if ($cashLedger && $salesLedger) {
                    $txnId = DB::table('accounting_transactions')->insertGetId([
                        'transaction_no' => 'ACC-' . strtoupper(Str::random(8)),
                        'reference_no' => $payment->id,
                        'transaction_date' => now(),
                        'description' => "Payment received for Order #{$order->order_no}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('accounting_entries')->insert([
                        ['transaction_id' => $txnId, 'ledger_id' => $cashLedger->id, 'debit' => $payment->amount, 'credit' => 0, 'created_at' => now(), 'updated_at' => now()],
                        ['transaction_id' => $txnId, 'ledger_id' => $salesLedger->id, 'debit' => 0, 'credit' => $payment->amount, 'created_at' => now(), 'updated_at' => now()],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('payments.index')->with('success', "Payment #{$payment->payment_no} successfully recorded.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', "Failed to record payment: " . $e->getMessage());
        }
    }

    /**
     * Display payment details.
     */
    public function show($id): View
    {
        $payment = Payment::with(['order.party', 'invoice', 'order.warehouse'])->findOrFail($id);
        return view('payments.show', compact('payment'));
    }

    /**
     * Update specified payment.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,completed,failed,refunded',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $payment->update([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id ?? $payment->transaction_id,
            'payment_date' => $request->payment_date,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', "Payment #{$payment->payment_no} successfully updated.");
    }

    /**
     * Remove the specified payment.
     */
    public function destroy($id): RedirectResponse
    {
        $payment = Payment::findOrFail($id);
        $paymentNo = $payment->payment_no;
        $payment->delete();

        return redirect()->route('payments.index')->with('success', "Payment #{$paymentNo} has been deleted.");
    }
}
