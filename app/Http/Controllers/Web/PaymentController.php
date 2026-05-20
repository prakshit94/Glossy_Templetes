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
                             $pq->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%")
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
        $totalInvoiceAmount = (float) Invoice::where('status', '!=', 'cancelled')->sum('net_amount');
        $totalCollected = (float) Payment::where('status', 'completed')->sum('amount');
        $outstandingAmount = max(0, $totalInvoiceAmount - $totalCollected);

        $stats = [
            'total_count' => Payment::count(),
            'completed_count' => Payment::where('status', 'completed')->count(),
            'pending_count' => Payment::where('status', 'pending')->count(),
            'failed_count' => Payment::where('status', 'failed')->count(),
            'refunded_count' => Payment::where('status', 'refunded')->count(),
            'total_amount' => $totalCollected,
            'pending_amount' => $outstandingAmount,
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

        // We will fetch orders dynamically via AJAX in the create modal.
        $ordersList = collect([]);

        if ($request->ajax()) {
            return response()->json([
                'table' => view('payments.partials.table', compact('payments'))->render(),
                'stats' => $stats
            ]);
        }

        return view('payments.index', compact('payments', 'stats', 'statusesList', 'methodsList', 'ordersList'));
    }

    /**
     * Search orders/invoices for the record payment modal.
     */
    public function searchOrders(Request $request): JsonResponse
    {
        $search = $request->input('q');
        if (!$search) {
            return response()->json([]);
        }

        $orders = Order::with('party', 'invoice')
            ->where('order_no', 'like', "%{$search}%")
            ->orWhereHas('invoice', function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();
            
        $results = $orders->map(function($order) {
            $invoiceText = $order->invoice ? " | Inv: {$order->invoice->invoice_no}" : "";
            $partyName = $order->party ? "{$order->party->firstname} {$order->party->lastname}" : "Internal Node";
            
            $paid = $order->payments()->where('status', 'completed')->sum('amount');
            $due = max(0, $order->net_amount - $paid);
            
            return [
                'id' => $order->id,
                'text' => "{$order->order_no}{$invoiceText} ({$partyName})",
                'total_amount' => $order->net_amount,
                'paid_amount' => $paid,
                'due_amount' => $due
            ];
        });

        return response()->json($results);
    }

    /**
     * Bulk update payments via CSV.
     */
    public function bulkUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $data = array_map('str_getcsv', file($path));
        
        if (count($data) < 2) {
            return back()->with('error', 'CSV file is empty or missing data rows.');
        }

        // Extract header
        $header = array_map('strtolower', array_map('trim', array_shift($data)));
        
        // Expected columns (flexible order)
        $paymentNoIdx = array_search('payment_no', $header);
        if ($paymentNoIdx === false) {
            $paymentNoIdx = array_search('payment reference', $header);
        }

        if ($paymentNoIdx === false) {
            return back()->with('error', 'CSV must contain a "payment_no" or "payment reference" column to identify payments.');
        }

        $updatedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $rowIndex => $row) {
                if (empty(array_filter($row))) continue; // Skip empty rows

                $paymentNo = trim($row[$paymentNoIdx] ?? '');
                if (!$paymentNo) continue;

                $payment = Payment::where('payment_no', $paymentNo)->first();
                
                if (!$payment) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": Payment {$paymentNo} not found.";
                    continue;
                }

                $updateData = [];

                foreach (['transaction_id', 'status', 'amount', 'payment_method', 'payment_date'] as $field) {
                    $idx = array_search($field, $header);
                    if ($idx !== false && isset($row[$idx]) && trim($row[$idx]) !== '') {
                        $updateData[$field] = trim($row[$idx]);
                    }
                }

                if (!empty($updateData)) {
                    $validator = \Illuminate\Support\Facades\Validator::make($updateData, [
                        'amount' => 'nullable|numeric|min:0.01',
                        'payment_method' => 'nullable|string|max:255',
                        'payment_date' => 'nullable|date',
                        'status' => 'nullable|in:pending,completed,failed,refunded',
                        'transaction_id' => 'nullable|string|max:255',
                    ]);

                    if ($validator->fails()) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": " . implode(', ', $validator->errors()->all());
                        continue;
                    }

                    $payment->update($updateData);
                    $updatedCount++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing CSV: ' . $e->getMessage());
        }

        if (count($errors) > 0) {
            $msg = "{$updatedCount} payments updated. Issues: " . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) $msg .= " and " . (count($errors) - 3) . " more.";
            return back()->with('warning', $msg);
        }

        return back()->with('success', "{$updatedCount} payments successfully updated via CSV.");
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
