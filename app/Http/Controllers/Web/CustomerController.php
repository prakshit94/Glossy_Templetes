<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Customer::query()
            ->select('parties.*')
            ->addSelect([
                'orders_count' => DB::table('orders')
                    ->selectRaw('count(*)')
                    ->whereColumn('party_id', 'parties.id')
                    ->limit(1),
            ]);

        if ($request->input('filter') === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->whereNull('parties.deleted_at');
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->latest('parties.created_at');

        $perPage  = $request->input('perPage', 10);
        $customers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('customers.partials.table', compact('customers'))->render();
        }

        $stats = [
            'total'        => Customer::count(),
            'active'       => Customer::where('status', 'active')->count(),
            'newThisMonth' => Customer::whereMonth('created_at', now()->month)->count(),
            'trashed'      => Customer::onlyTrashed()->count(),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create()
    {
        return view('customers.create');
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:20',
            'gst_no'       => 'nullable|string|max:20',
            'pan_no'       => 'nullable|string|max:10',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days'  => 'nullable|integer|min:0',
            'status'       => 'required|in:active,inactive,suspended',
        ]);

        $data['type'] = 'customer';

        $customer = Customer::create($data);

        activity('customers')
            ->performedOn($customer)
            ->log("Created customer: {$customer->name}");

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Customer $customer)
    {
        $customer->load('addresses.village');
        $categories = \App\Models\Category::whereNull('parent_id')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();
        return view('customers.show', compact('customer', 'categories', 'warehouses'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(Customer $customer)
    {
        $customer->load('addresses.village');
        return view('customers.edit', compact('customer'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:20',
            'gst_no'       => 'nullable|string|max:20',
            'pan_no'       => 'nullable|string|max:10',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days'  => 'nullable|integer|min:0',
            'status'       => 'required|in:active,inactive,suspended',
        ]);

        $customer->update($data);

        activity('customers')
            ->performedOn($customer)
            ->log("Updated customer: {$customer->name}");

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('success', 'Customer moved to archive.');
    }

    // ─── Restore ──────────────────────────────────────────────────────────────

    public function restore($id)
    {
        $customer = Customer::withoutGlobalScope('customer')
            ->withTrashed()
            ->where('type', 'customer')
            ->findOrFail($id);
        $customer->restore();
        return back()->with('success', 'Customer restored successfully.');
    }

    // ─── Force Delete ─────────────────────────────────────────────────────────

    public function forceDelete($id)
    {
        $customer = Customer::withoutGlobalScope('customer')
            ->withTrashed()
            ->where('type', 'customer')
            ->findOrFail($id);
        $customer->forceDelete();
        return back()->with('success', 'Customer permanently deleted.');
    }

    // ─── Bulk Delete ──────────────────────────────────────────────────────────

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No customers selected.');

        Customer::whereIn('id', $ids)->delete();

        activity('bulk')
            ->withProperties(['ids' => $ids])
            ->log('Bulk archived ' . count($ids) . ' customers');

        return back()->with('success', 'Selected customers moved to archive.');
    }

    // ─── Bulk Restore ─────────────────────────────────────────────────────────

    public function bulkRestore(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (!is_array($ids) || empty($ids)) return back()->with('error', 'No customers selected.');

        Customer::withoutGlobalScope('customer')
            ->withTrashed()
            ->where('type', 'customer')
            ->whereIn('id', $ids)
            ->restore();

        return back()->with('success', 'Selected customers restored.');
    }

    // ─── Bulk Force Delete ────────────────────────────────────────────────────

    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (!is_array($ids) || empty($ids)) return back()->with('error', 'No customers selected.');

        Customer::withoutGlobalScope('customer')
            ->withTrashed()
            ->where('type', 'customer')
            ->whereIn('id', $ids)
            ->forceDelete();

        return back()->with('success', 'Selected customers permanently deleted.');
    }

    // ─── Bulk Status ──────────────────────────────────────────────────────────

    public function bulkStatus(Request $request)
    {
        $ids    = json_decode($request->ids, true);
        $status = $request->status;

        if (is_array($ids) && count($ids) > 0) {
            Customer::whereIn('id', $ids)->update(['status' => $status]);
        }

        return back()->with('success', 'Selected customers status updated.');
    }

    // ─── Place Order ─────────────────────────────────────────────────────────
    
    public function placeOrder(Request $request, Customer $customer, OrderService $orderService)
    {
        $data = $request->validate([
            'cart'                   => 'required|string',
            'order_discount_amount'  => 'nullable|numeric',
            'coupon_code'            => 'nullable|string',
            'coupon_discount'        => 'nullable|numeric',
            'tax_amount'             => 'required|numeric',
            'subtotal'               => 'required|numeric',
            'grand_total'            => 'required|numeric',
            'warehouse_id'           => 'required|exists:warehouses,id',
            'address_id'             => 'required|exists:party_addresses,id',
            'billing_address_id'     => 'nullable|exists:party_addresses,id',
        ]);

        try {
            $order = $orderService->placeCustomerOrder($customer, $data);
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }
}
