<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;

class UnitOfMeasureController extends Controller
{
    public function index(Request $request)
    {
        $uoms = UnitOfMeasure::latest()->get();
        
        $stats = [
            'total' => UnitOfMeasure::count(),
            'active' => UnitOfMeasure::where('status', 'active')->count(),
            'base' => UnitOfMeasure::where('is_base_unit', true)->count(),
        ];

        return view('uom.index', compact('uoms', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:units_of_measure',
            'code' => 'required|string|max:10|unique:units_of_measure',
            'status' => 'required|string',
            'is_base_unit' => 'boolean',
        ]);

        $data['is_base_unit'] = $request->has('is_base_unit');

        UnitOfMeasure::create($data);

        return back()->with('success', 'UOM created successfully.');
    }

    public function update(Request $request, UnitOfMeasure $uom)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:units_of_measure,name,' . $uom->id,
            'code' => 'required|string|max:10|unique:units_of_measure,code,' . $uom->id,
            'status' => 'required|string',
            'is_base_unit' => 'boolean',
        ]);

        $data['is_base_unit'] = $request->has('is_base_unit');

        $uom->update($data);

        return back()->with('success', 'UOM updated successfully.');
    }

    public function destroy(UnitOfMeasure $uom)
    {
        $uom->delete();
        return back()->with('success', 'UOM deleted successfully.');
    }
}
