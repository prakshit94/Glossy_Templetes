<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%$s%");
        }

        $brands = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => Brand::count(),
            'active' => Brand::where('status', 'active')->count(),
            'inactive' => Brand::where('status', 'inactive')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('brands.partials.table', compact('brands'))->render(),
                'stats' => $stats
            ]);
        }

        return view('brands.index', compact('brands', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'status' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data['slug'] = Str::slug($data['name']);
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        Brand::create($data);

        return back()->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'status' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($data);

        return back()->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Brand deleted successfully.');
    }
}
