<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with('user');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('license_number', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhereHas('user', function($u) use ($s) {
                      $u->where('name', 'like', "%$s%");
                  });
            });
        }

        $perPage = $request->input('perPage', 10);
        $records = $query->latest()->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Driver::count(),
            'available' => Driver::where('status', 'available')->count(),
            'busy' => Driver::where('status', 'busy')->count(),
            'on_leave' => Driver::where('status', 'on_leave')->count(),
        ];

        // Fetch users to populate registration dropdown
        $users = User::all();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('drivers.partials.table', compact('records'))->render(),
                'stats' => $stats
            ]);
        }

        return view('drivers.index', [
            'moduleKey' => 'drivers',
            'moduleTitle' => 'Drivers',
            'moduleIcon' => 'users-2',
            'records' => $records,
            'stats' => $stats,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id|unique:drivers',
            'license_number' => 'required|string|max:255|unique:drivers',
            'phone' => 'nullable|string|max:255',
            'status' => 'required|string|in:available,busy,on_leave,inactive',
        ]);

        Driver::create($data);

        return back()->with('success', 'Driver registered successfully.');
    }

    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id|unique:drivers,user_id,' . $driver->id,
            'license_number' => 'required|string|max:255|unique:drivers,license_number,' . $driver->id,
            'phone' => 'nullable|string|max:255',
            'status' => 'required|string|in:available,busy,on_leave,inactive',
        ]);

        $driver->update($data);

        return back()->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return back()->with('success', 'Driver deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No drivers selected.');

        Driver::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' drivers deleted successfully.');
    }
}
