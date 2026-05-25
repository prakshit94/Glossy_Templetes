<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%');

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {

            switch ($request->status) {

                case 'active':

                    $query->where('is_active', true)
                        ->where(function ($q) {

                            $q->whereNull('expiry_date')
                                ->orWhereDate('expiry_date', '>=', now());

                        });

                    break;

                case 'inactive':

                    $query->where('is_active', false);

                    break;

                case 'expired':

                    $query->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<', now());

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $perPage = $request->get('perPage', 10);

        $coupons = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | AJAX Response
        |--------------------------------------------------------------------------
        */
        if ($request->ajax()) {

            return view('coupons.partials.table', [
                'records' => $coupons
            ])->render();
        }

        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */
        $stats = [

            'total' => Coupon::count(),

            'active' => Coupon::where('is_active', true)
                ->where(function ($q) {

                    $q->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', now());

                })
                ->count(),

            'expired' => Coupon::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', now())
                ->count(),

            'used' => Coupon::sum('used_count'),
        ];

        return view('coupons.index', compact(
            'coupons',
            'stats'
        ));
    }

    public function create()
    {
        return view('coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $data['is_active'] = $request->has('is_active');

        $data['code'] = strtoupper($data['code']);

        Coupon::create($data);

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon)
    {
        return view('coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $data['is_active'] = $request->has('is_active');

        $data['code'] = strtoupper($data['code']);

        $coupon->update($data);

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Coupon deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Status Update
    |--------------------------------------------------------------------------
    */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        $ids = json_decode($request->ids, true);

        Coupon::whereIn('id', $ids)->update([
            'is_active' => $request->status === 'active'
        ]);

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Coupon statuses updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required'
        ]);

        $ids = json_decode($request->ids, true);

        Coupon::whereIn('id', $ids)->delete();

        return redirect()
            ->route('coupons.index')
            ->with('success', 'Selected coupons deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Coupon Validation API
    |--------------------------------------------------------------------------
    */
    public function validateApi(Request $request)
    {
        $code = strtoupper(trim($request->input('code')));

        $subtotal = (float) $request->input('subtotal', 0);

        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {

            return response()->json([
                'valid' => false,
                'message' => 'Invalid or inactive promo code.'
            ]);
        }

        if (
            $coupon->expiry_date &&
            $coupon->expiry_date < now()->startOfDay()
        ) {

            return response()->json([
                'valid' => false,
                'message' => 'This promo code has expired.'
            ]);
        }

        if (
            $coupon->usage_limit &&
            $coupon->used_count >= $coupon->usage_limit
        ) {

            return response()->json([
                'valid' => false,
                'message' => 'This promo code usage limit has been reached.'
            ]);
        }

        if (
            $coupon->min_spend > 0 &&
            $subtotal < $coupon->min_spend
        ) {

            return response()->json([
                'valid' => false,
                'message' => 'Minimum spend of ₹' .
                    number_format($coupon->min_spend, 2) .
                    ' required.'
            ]);
        }

        $discount = 0;

        if ($coupon->type === 'percentage') {

            $discount = $subtotal * ($coupon->value / 100);

            if (
                $coupon->max_discount > 0 &&
                $discount > $coupon->max_discount
            ) {

                $discount = $coupon->max_discount;
            }

        } else {

            $discount = $coupon->value;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Over Discount
        |--------------------------------------------------------------------------
        */
        $discount = min($discount, $subtotal);

        return response()->json([

            'valid' => true,

            'message' => 'Coupon applied successfully!',

            'discount' => round($discount, 2),

            'code' => $coupon->code,

            'coupon_id' => $coupon->id

        ]);
    }
}