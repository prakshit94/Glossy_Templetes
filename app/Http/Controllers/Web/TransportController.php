<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transport;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transport::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('vehicle_number', 'like', "%$s%")
                  ->orWhere('type', 'like', "%$s%");
            });
        }

        $perPage = $request->input('perPage', 10);
        $records = $query->latest()->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Transport::count(),
            'available' => Transport::where('status', 'available')->count(),
            'on_delivery' => Transport::where('status', 'on_delivery')->count(),
            'maintenance' => Transport::where('status', 'maintenance')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('transport.partials.table', compact('records'))->render(),
                'stats' => $stats
            ]);
        }

        return view('transport.index', [
            'moduleKey' => 'transport',
            'moduleTitle' => 'Transport',
            'moduleIcon' => 'truck',
            'records' => $records,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'vehicle_number' => 'required|string|max:255|unique:transports',
            'type' => 'nullable|string|max:255',
            'capacity_weight' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:available,on_delivery,maintenance,inactive',
        ]);

        Transport::create($data);

        return back()->with('success', 'Transport vehicle registered successfully.');
    }

    public function update(Request $request, Transport $transport)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'vehicle_number' => 'required|string|max:255|unique:transports,vehicle_number,' . $transport->id,
            'type' => 'nullable|string|max:255',
            'capacity_weight' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:available,on_delivery,maintenance,inactive',
        ]);

        $transport->update($data);

        return back()->with('success', 'Transport vehicle updated successfully.');
    }

    public function destroy(Transport $transport)
    {
        $transport->delete();
        return back()->with('success', 'Transport vehicle deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No transports selected.');

        Transport::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' transports deleted successfully.');
    }
}
