<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductAttribute::with('values');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%$s%");
        }

        $attributes = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => ProductAttribute::count(),
            'filterable' => ProductAttribute::where('is_filterable', true)->count(),
            'total_values' => ProductAttributeValue::count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('attributes.partials.table', compact('attributes'))->render(),
                'stats' => $stats
            ]);
        }

        return view('attributes.index', compact('attributes', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:product_attributes',
            'type' => 'required|string', // text, color, select
            'status' => 'required|string',
            'is_filterable' => 'boolean',
        ]);

        $data['is_filterable'] = $request->has('is_filterable');

        ProductAttribute::create($data);

        return back()->with('success', 'Attribute created successfully.');
    }

    public function update(Request $request, ProductAttribute $attribute)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:product_attributes,name,' . $attribute->id,
            'type' => 'required|string',
            'status' => 'required|string',
            'is_filterable' => 'boolean',
        ]);

        $data['is_filterable'] = $request->has('is_filterable');

        $attribute->update($data);

        return back()->with('success', 'Attribute updated successfully.');
    }

    public function destroy(ProductAttribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'Attribute deleted successfully.');
    }

    // Value Management
    public function storeValue(Request $request, ProductAttribute $attribute)
    {
        $data = $request->validate([
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $attribute->values()->create($data);

        return back()->with('success', 'Value added successfully.');
    }

    public function updateValue(Request $request, ProductAttributeValue $value)
    {
        $data = $request->validate([
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $value->update($data);

        return back()->with('success', 'Value updated successfully.');
    }

    public function destroyValue(ProductAttributeValue $value)
    {
        $value->delete();
        return back()->with('success', 'Value deleted successfully.');
    }
}
