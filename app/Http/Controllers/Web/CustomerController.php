<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Crop;
use App\Models\IrrigationType;
use App\Models\LandUnit;
use App\Services\OrderService;
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
        $crops = Crop::where('status', 'active')->orderBy('name')->get();
        $irrigationTypes = IrrigationType::where('status', 'active')->orderBy('name')->get();
        $landUnits = LandUnit::where('status', 'active')->orderBy('name')->get();

        return view('customers.create', compact('crops', 'irrigationTypes', 'landUnits'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            // Basic Identity
            'party_code'       => 'nullable|string|max:50|unique:parties,party_code',
            'firstname'        => 'required|string|max:100',
            'middlename'       => 'nullable|string|max:100',
            'lastname'         => 'required|string|max:100',

            // Contact
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'alternatemobile'  => 'nullable|string|max:20',
            'relative_mobile'  => 'nullable|string|max:20',
            'phone_number_2'   => 'nullable|string|max:20',
            'relative_phone'   => 'nullable|string|max:20',

            // Classification
            'source'           => 'nullable|array',
            'category'         => 'nullable|string|max:50',

            // Business
            'company_name'     => 'nullable|string|max:255',
            'gst_no'           => 'nullable|string|max:20',
            'pan_no'           => 'nullable|string|max:10',
            'tax_no'           => 'nullable|string|max:30',

            // Agriculture
            'land_area'        => 'nullable|numeric|min:0',
            'land_unit'        => 'nullable|string|max:20',
            'crops'            => 'nullable|array',
            'irrigation_type'  => 'nullable|array',

            // Financial
            'credit_limit'         => 'nullable|numeric|min:0',
            'credit_days'          => 'nullable|integer|min:0',
            'outstanding_balance'  => 'nullable|numeric',
            'credit_valid_till'    => 'nullable|date',

            // KYC
            'aadhaar_last4'    => 'nullable|digits:4',
            'kyc_completed'    => 'nullable|boolean',

            // Status & Control
            'status'           => 'required|in:active,inactive,suspended',
            'is_active'        => 'nullable|boolean',
            'is_blacklisted'   => 'nullable|boolean',
            'internal_notes'   => 'nullable|string',
            'tags'             => 'nullable|array',

            // Accounting
            'account_type_id'  => 'nullable|integer|exists:account_types,id',
        ]);

        $data['type'] = 'customer';
        $customer = Customer::create($data);

        activity('customers')
            ->performedOn($customer)
            ->log("Registered new customer: {$customer->name}");

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Customer $customer)
    {
        $customer->load(['addresses.village', 'orders' => function($q) {
            $q->latest()->limit(5);
        }]);

        $categories = \App\Models\Category::whereNull('parent_id')->get();
        $warehouses = \App\Models\Warehouse::where('status', 'active')->get();

        return view('customers.show', compact('customer', 'categories', 'warehouses'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(Customer $customer)
    {
        $customer->load('addresses.village');
        
        $crops = Crop::where('status', 'active')->orderBy('name')->get();
        $irrigationTypes = IrrigationType::where('status', 'active')->orderBy('name')->get();
        $landUnits = LandUnit::where('status', 'active')->orderBy('name')->get();

        return view('customers.edit', compact('customer', 'crops', 'irrigationTypes', 'landUnits'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            // Basic Identity
            'party_code'       => 'nullable|string|max:50|unique:parties,party_code,' . $customer->id,
            'firstname'        => 'required|string|max:100',
            'middlename'       => 'nullable|string|max:100',
            'lastname'         => 'required|string|max:100',

            // Contact
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'alternatemobile'  => 'nullable|string|max:20',
            'relative_mobile'  => 'nullable|string|max:20',
            'phone_number_2'   => 'nullable|string|max:20',
            'relative_phone'   => 'nullable|string|max:20',

            // Classification
            'source'           => 'nullable|array',
            'category'         => 'nullable|string|max:50',

            // Business
            'company_name'     => 'nullable|string|max:255',
            'gst_no'           => 'nullable|string|max:20',
            'pan_no'           => 'nullable|string|max:10',
            'tax_no'           => 'nullable|string|max:30',

            // Agriculture
            'land_area'        => 'nullable|numeric|min:0',
            'land_unit'        => 'nullable|string|max:20',
            'crops'            => 'nullable|array',
            'irrigation_type'  => 'nullable|array',

            // Financial
            'credit_limit'         => 'nullable|numeric|min:0',
            'credit_days'          => 'nullable|integer|min:0',
            'outstanding_balance'  => 'nullable|numeric',
            'credit_valid_till'    => 'nullable|date',

            // KYC
            'aadhaar_last4'    => 'nullable|digits:4',
            'kyc_completed'    => 'nullable|boolean',

            // Status & Control
            'status'           => 'required|in:active,inactive,suspended',
            'is_active'        => 'nullable|boolean',
            'is_blacklisted'   => 'nullable|boolean',
            'internal_notes'   => 'nullable|string',
            'tags'             => 'nullable|array',

            // Accounting
            'account_type_id'  => 'nullable|integer|exists:account_types,id',
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
            'order_id'               => 'nullable|exists:orders,id',
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
            if (!empty($data['order_id'])) {
                $order = Order::findOrFail($data['order_id']);
                $orderService->updateCustomerOrder($order, $data);
                $msg = 'Order updated successfully!';
            } else {
                $orderService->placeCustomerOrder($customer, $data);
                $msg = 'Order placed successfully!';
            }
            
            return redirect()->route('customers.show', $customer)
                ->with('success', $msg)
                ->with('active_tab', 'history');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }
}
