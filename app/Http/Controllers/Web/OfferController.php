<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::with('product');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('discount_type', 'like', '%' . $search . '%');

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

                    $query->where('is_active', true);

                    break;

                case 'inactive':

                    $query->where('is_active', false);

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Type Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('type')) {

            switch ($request->type) {

                case 'order_discount':

                    $query->where('type', 'order_discount');

                    break;

                case 'bogo':

                    $query->where('type', 'bogo');

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $perPage = (int) $request->get('perPage', 10);

        if (!in_array($perPage, [5, 10, 15, 20, 50])) {

            $perPage = 10;
        }

        $offers = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | AJAX Response
        |--------------------------------------------------------------------------
        */
        if ($request->ajax()) {

            return view('offers.partials.table', [
                'records' => $offers
            ])->render();
        }

        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */
        $stats = [

            'total' => Offer::count(),

            'active' => Offer::where('is_active', true)
                ->count(),

            'inactive' => Offer::where('is_active', false)
                ->count(),

            'bogo' => Offer::where('type', 'bogo')
                ->count(),

            'discounts' => Offer::where('type', 'order_discount')
                ->count(),
        ];

        return view('offers.index', compact(
            'offers',
            'stats'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $products = Product::where('status', 'active')
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku'
            ]);

        return view('offers.create', compact('products'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $this->validateOffer($request);

        Offer::create($data);

        return redirect()
            ->route('offers.index')
            ->with('success', 'Offer created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
    public function edit(Offer $offer)
    {
        $products = Product::where('status', 'active')
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku'
            ]);

        return view('offers.edit', compact(
            'offer',
            'products'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Offer $offer)
    {
        $data = $this->validateOffer($request);

        $offer->update($data);

        return redirect()
            ->route('offers.index')
            ->with('success', 'Offer updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */
    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()
            ->route('offers.index')
            ->with('success', 'Offer deleted successfully.');
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

        if (!is_array($ids) || empty($ids)) {

            return redirect()
                ->route('offers.index')
                ->with('error', 'No offers selected.');
        }

        Offer::whereIn('id', $ids)->update([
            'is_active' => $request->status === 'active'
        ]);

        return redirect()
            ->route('offers.index')
            ->with('success', 'Offer statuses updated successfully.');
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

        if (!is_array($ids) || empty($ids)) {

            return redirect()
                ->route('offers.index')
                ->with('error', 'No offers selected.');
        }

        Offer::whereIn('id', $ids)->delete();

        return redirect()
            ->route('offers.index')
            ->with('success', 'Selected offers deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Offer Validation API
    |--------------------------------------------------------------------------
    */
    public function validateApi(Request $request)
    {
        $request->validate([

            'offer_id' => 'required|exists:offers,id',

            'subtotal' => 'nullable|numeric|min:0',

            'quantity' => 'nullable|integer|min:1',
        ]);

        $offer = Offer::with('product')
            ->where('id', $request->offer_id)
            ->where('is_active', true)
            ->first();

        if (!$offer) {

            return response()->json([

                'valid' => false,

                'message' => 'Offer is invalid or inactive.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Date Validation
        |--------------------------------------------------------------------------
        */
        if (
            $offer->starts_at &&
            $offer->starts_at > now()
        ) {

            return response()->json([

                'valid' => false,

                'message' => 'This offer has not started yet.'
            ]);
        }

        if (
            $offer->ends_at &&
            $offer->ends_at < now()
        ) {

            return response()->json([

                'valid' => false,

                'message' => 'This offer has expired.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Order Discount Validation
        |--------------------------------------------------------------------------
        */
        if ($offer->type === 'order_discount') {

            $subtotal = (float) $request->subtotal;

            if (
                $offer->min_spend > 0 &&
                $subtotal < $offer->min_spend
            ) {

                return response()->json([

                    'valid' => false,

                    'message' => 'Minimum spend of ₹' .
                        number_format($offer->min_spend, 2) .
                        ' required.'
                ]);
            }

            $discount = 0;

            if ($offer->discount_type === 'percentage') {

                $discount =
                    $subtotal * ($offer->value / 100);

                if (
                    $offer->max_discount > 0 &&
                    $discount > $offer->max_discount
                ) {

                    $discount = $offer->max_discount;
                }

            } else {

                $discount = $offer->value;
            }

            $discount = min($discount, $subtotal);

            return response()->json([

                'valid' => true,

                'type' => 'order_discount',

                'message' => 'Offer applied successfully.',

                'discount' => round($discount, 2),

                'offer_id' => $offer->id,

                'offer_name' => $offer->name
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BOGO Validation
        |--------------------------------------------------------------------------
        */
        $quantity = (int) $request->quantity;

        if ($quantity < $offer->buy_qty) {

            return response()->json([

                'valid' => false,

                'message' => 'Minimum quantity of ' .
                    $offer->buy_qty .
                    ' required for this BOGO offer.'
            ]);
        }

        return response()->json([

            'valid' => true,

            'type' => 'bogo',

            'message' => 'BOGO offer applied successfully.',

            'buy_qty' => $offer->buy_qty,

            'get_qty' => $offer->get_qty,

            'product_id' => $offer->product_id,

            'product_name' => $offer->product?->name,

            'offer_id' => $offer->id,

            'offer_name' => $offer->name
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Logic
    |--------------------------------------------------------------------------
    */
    private function validateOffer(Request $request): array
    {
        $data = $request->validate([

            'name' => 'required|string|max:255',

            'type' => [
                'required',
                Rule::in([
                    'order_discount',
                    'bogo'
                ])
            ],

            'discount_type' => [
                'nullable',
                Rule::in([
                    'fixed',
                    'percentage'
                ])
            ],

            'value' => 'nullable|numeric|min:0',

            'min_spend' => 'nullable|numeric|min:0',

            'max_discount' => 'nullable|numeric|min:0',

            'product_id' => 'nullable|exists:products,id',

            'buy_qty' => 'nullable|integer|min:1',

            'get_qty' => 'nullable|integer|min:1',

            'starts_at' => 'nullable|date',

            'ends_at' => 'nullable|date|after_or_equal:starts_at',

            'priority' => 'nullable|integer|min:0',

            'is_active' => 'nullable|boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Defaults
        |--------------------------------------------------------------------------
        */
        $data['is_active'] = $request->has('is_active');

        $data['priority'] =
            (int) ($data['priority'] ?? 0);

        $data['min_spend'] =
            $data['min_spend'] ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Order Discount Rules
        |--------------------------------------------------------------------------
        */
        if (($data['type'] ?? null) === 'order_discount') {

            $data['product_id'] = null;

            $data['buy_qty'] = 1;

            $data['get_qty'] = 1;

            $data['discount_type'] =
                $data['discount_type'] ?? 'fixed';

            $data['value'] =
                $data['value'] ?? 0;
        }

        /*
        |--------------------------------------------------------------------------
        | BOGO Rules
        |--------------------------------------------------------------------------
        */
        else {

            $data['discount_type'] = null;

            $data['value'] = 0;

            $data['min_spend'] = 0;

            $data['max_discount'] = null;

            $data['buy_qty'] =
                (int) ($data['buy_qty'] ?? 1);

            $data['get_qty'] =
                (int) ($data['get_qty'] ?? 1);
        }

        return $data;
    }
}