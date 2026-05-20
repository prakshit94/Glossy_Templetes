<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Refund::with(['payment.order.party']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereHas('payment', function ($pq) use ($s) {
                    $pq->where('payment_no', 'LIKE', "%{$s}%")
                       ->orWhere('transaction_id', 'LIKE', "%{$s}%")
                       ->orWhereHas('order', function ($oq) use ($s) {
                           $oq->where('order_no', 'LIKE', "%{$s}%")
                              ->orWhereHas('party', function ($partyQ) use ($s) {
                                  $partyQ->where('firstname', 'LIKE', "%{$s}%")
                                         ->orWhere('lastname', 'LIKE', "%{$s}%")
                                         ->orWhere('company_name', 'LIKE', "%{$s}%");
                              });
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
            'pending'   => (clone $query)->where('status', 'pending')->count(),
            'processed' => (clone $query)->where('status', 'processed')->count(),
            'failed'    => (clone $query)->where('status', 'failed')->count(),
        ];

        $perPage = (int) $request->get('perPage', 15);
        $refunds = $query->latest()->paginate($perPage)->withQueryString();
        $statusesList = ['pending', 'processed', 'failed'];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('refunds.partials.table', compact('refunds'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('refunds.index', compact('refunds', 'stats', 'statusesList'));
    }

    public function create(Request $request)
    {
        $paymentId = $request->get('payment_id');
        $payment = null;
        
        if ($paymentId) {
            $payment = Payment::with('order.party')->findOrFail($paymentId);
            $existingRefunds = $payment->refunds()->whereIn('status', ['pending', 'processed'])->sum('amount');
            if ($existingRefunds >= $payment->amount) {
                return redirect()->route('refunds.create')->with('error', 'This payment has already been fully refunded.');
            }
        }
        
        $payments = Payment::where('status', 'completed')
                           ->with('order.party')
                           ->latest()
                           ->limit(50)
                           ->get();

        return view('refunds.create', compact('payment', 'payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount'     => 'required|numeric|min:0.01',
            'reason'     => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Lock payment record for update to prevent concurrent refund creation
                $payment = Payment::lockForUpdate()->findOrFail($request->payment_id);

                $existingRefunds = $payment->refunds()
                    ->whereIn('status', ['pending', 'processed'])
                    ->lockForUpdate()
                    ->sum('amount');
                
                $maxRefundable = $payment->amount - $existingRefunds;

                if ($request->amount > $maxRefundable) {
                    throw new \Exception("Refund amount cannot exceed the maximum refundable amount of ₹" . number_format($maxRefundable, 2));
                }

                Refund::create([
                    'payment_id' => $payment->id,
                    'amount'     => $request->amount,
                    'reason'     => $request->reason,
                    'status'     => 'pending',
                ]);
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('refunds.index')->with('success', 'Refund request created successfully.');
    }

    public function show($id)
    {
        $refund = Refund::with(['payment.order.party'])->findOrFail($id);
        return view('refunds.show', compact('refund'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processed,failed',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                // Lock refund record for update
                $refund = Refund::lockForUpdate()->findOrFail($id);
                
                if ($refund->status === 'processed' || $refund->status === 'failed') {
                    throw new \Exception('Cannot update status of a finalized refund.');
                }

                // Lock the associated payment record for update
                $payment = Payment::lockForUpdate()->findOrFail($refund->payment_id);

                $newStatus = $request->status;
                $updateData = ['status' => $newStatus];

                if ($newStatus === 'processed') {
                    $updateData['processed_at'] = now();
                    
                    $existingRefunds = $payment->refunds()
                        ->where('status', 'processed')
                        ->where('id', '!=', $refund->id)
                        ->lockForUpdate()
                        ->sum('amount');

                    if (($existingRefunds + $refund->amount) >= $payment->amount) {
                        $payment->update(['status' => 'refunded']);
                    }
                }

                $refund->update($updateData);
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating refund status: ' . $e->getMessage());
        }

        return back()->with('success', "Refund status updated to {$request->status}.");
    }
}
