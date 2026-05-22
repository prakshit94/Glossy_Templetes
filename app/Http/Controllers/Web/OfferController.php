<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::with('product')->latest()->paginate(20);

        return view('offers.index', compact('offers'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']);

        return view('offers.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateOffer($request);
        Offer::create($data);

        return redirect()->route('offers.index')->with('success', 'Offer created successfully.');
    }

    public function edit(Offer $offer)
    {
        $products = Product::where('status', 'active')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']);

        return view('offers.edit', compact('offer', 'products'));
    }

    public function update(Request $request, Offer $offer)
    {
        $data = $this->validateOffer($request);
        $offer->update($data);

        return redirect()->route('offers.index')->with('success', 'Offer updated successfully.');
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()->route('offers.index')->with('success', 'Offer deleted successfully.');
    }

    private function validateOffer(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['order_discount', 'bogo'])],
            'discount_type' => ['nullable', Rule::in(['fixed', 'percentage'])],
            'value' => 'nullable|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'product_id' => 'nullable|exists:products,id',
            'buy_qty' => 'nullable|integer|min:1',
            'get_qty' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['priority'] = (int) ($data['priority'] ?? 0);
        $data['min_spend'] = $data['min_spend'] ?? 0;

        if (($data['type'] ?? null) === 'order_discount') {
            $data['product_id'] = null;
            $data['buy_qty'] = 1;
            $data['get_qty'] = 1;
            $data['discount_type'] = $data['discount_type'] ?? 'fixed';
            $data['value'] = $data['value'] ?? 0;
        } else {
            $data['discount_type'] = null;
            $data['value'] = 0;
            $data['min_spend'] = 0;
            $data['max_discount'] = null;
            $data['buy_qty'] = (int) ($data['buy_qty'] ?? 1);
            $data['get_qty'] = (int) ($data['get_qty'] ?? 1);
        }

        return $data;
    }
}
