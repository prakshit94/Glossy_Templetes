<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products')->with('parent')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('perPage', 10);
        $categories = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('categories.partials.table', compact('categories'))->render();
        }

        $parentCategories = Category::all();

        $totalCount = Category::count();
        $activeCount = Category::where('status', 'active')->count();
        $newThisMonth = Category::whereMonth('created_at', now()->month)->count();
        $parentCount = Category::whereNull('parent_id')->count();

        $stats = [
            'total' => $totalCount,
            'active' => $activeCount,
            'newThisMonth' => $newThisMonth,
            'parentCategories' => $parentCount,
        ];

        return view('categories.index', compact('categories', 'parentCategories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data['slug'] = Str::slug($data['name']);
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No categories selected.');

        Category::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' categories deleted successfully.');
    }
}
