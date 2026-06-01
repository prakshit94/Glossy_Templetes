<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $records = Department::query()
            ->with('manager')
            ->withCount('employees')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate((int) $request->get('perPage', 10))
            ->withQueryString();

        return view('departments.index', [
            'moduleKey' => 'departments',
            'moduleTitle' => 'Departments',
            'moduleIcon' => 'building',
            'records' => $records,
        ]);
    }
}
