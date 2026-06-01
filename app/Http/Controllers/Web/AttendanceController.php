<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $records = Attendance::query()
            ->with('employee.user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('employee.user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            })
            ->latest('date')
            ->paginate((int) $request->get('perPage', 10))
            ->withQueryString();

        return view('attendance.index', [
            'moduleKey' => 'attendance',
            'moduleTitle' => 'Attendance',
            'moduleIcon' => 'calendar',
            'records' => $records,
        ]);
    }
}
