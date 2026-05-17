<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices with filters and searching.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Invoice::with(['order.party', 'order.warehouse']);

        // Search (invoice no, order no, party name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_no', 'like', "%{$search}%")
                         ->orWhereHas('party', function ($pq) use ($search) {
                             $pq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $statuses = explode(',', $request->input('status'));
            $query->whereIn('status', $statuses);
        }

        // Date range filters
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('end_date'));
        }

        // Pagination
        $perPage = (int) $request->input('perPage', 15);
        $perPage = ($perPage > 0 && $perPage <= 100) ? $perPage : 15;

        $invoices = $query->latest('invoice_date')->paginate($perPage)->withQueryString();

        // Calculate statistics
        $stats = [
            'total' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'partially_paid' => Invoice::where('status', 'partially_paid')->count(),
            'unpaid' => Invoice::where('status', 'unpaid')->count(),
            'cancelled' => Invoice::where('status', 'cancelled')->count(),
            'total_amount' => (float) Invoice::sum('net_amount'),
            'paid_amount' => (float) Invoice::where('status', 'paid')->sum('net_amount'),
            'unpaid_amount' => (float) Invoice::whereIn('status', ['unpaid', 'partially_paid'])->sum('net_amount'),
        ];

        $statusesList = [
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled'
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('invoices.partials.table', compact('invoices'))->render(),
                'stats' => $stats
            ]);
        }

        return view('invoices.index', compact('invoices', 'stats', 'statusesList'));
    }
}
