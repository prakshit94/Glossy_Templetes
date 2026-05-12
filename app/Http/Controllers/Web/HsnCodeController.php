<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HsnCode;
use Illuminate\Http\Request;

class HsnCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = HsnCode::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('code', 'like', "%$s%")->orWhere('description', 'like', "%$s%");
        }

        $hsnCodes = $query->latest()->paginate(15)->withQueryString();
        
        $stats = [
            'total' => HsnCode::count(),
            'active' => HsnCode::where('status', 'active')->count(),
        ];

        return view('hsn-codes.index', compact('hsnCodes', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:hsn_codes',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);

        HsnCode::create($data);

        return back()->with('success', 'HSN Code created successfully.');
    }

    public function update(Request $request, HsnCode $hsnCode)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:hsn_codes,code,' . $hsnCode->id,
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $hsnCode->update($data);

        return back()->with('success', 'HSN Code updated successfully.');
    }

    public function destroy(HsnCode $hsnCode)
    {
        $hsnCode->delete();
        return back()->with('success', 'HSN Code deleted successfully.');
    }
}
