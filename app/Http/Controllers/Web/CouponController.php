<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(20);
        return view('coupons.index', compact('coupons'));
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

        return redirect()->route('coupons.index')->with('success', 'Coupon created successfully.');
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

        return redirect()->route('coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    // API endpoint for validating coupon
    public function validateApi(Request $request)
    {
        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float) $request->input('subtotal', 0);

        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid or inactive promo code.']);
        }

        if ($coupon->expiry_date && $coupon->expiry_date < now()->startOfDay()) {
            return response()->json(['valid' => false, 'message' => 'This promo code has expired.']);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['valid' => false, 'message' => 'This promo code usage limit has been reached.']);
        }

        if ($coupon->min_spend > 0 && $subtotal < $coupon->min_spend) {
            return response()->json(['valid' => false, 'message' => 'Minimum spend of ₹' . number_format($coupon->min_spend, 2) . ' required.']);
        }

        $discount = 0;
        if ($coupon->type === 'percentage') {
            $discount = $subtotal * ($coupon->value / 100);
            if ($coupon->max_discount > 0 && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } else {
            $discount = $coupon->value;
        }

        // Cannot discount more than subtotal
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
