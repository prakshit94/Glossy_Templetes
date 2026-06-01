<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $records = EmployeeProfile::query()
            ->with(['user', 'department'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('department', fn ($department) => $department->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate((int) $request->get('perPage', 10))
            ->withQueryString();

        return view('employees.index', [
            'moduleKey' => 'employees',
            'moduleTitle' => 'Employees',
            'moduleIcon' => 'employees',
            'records' => $records,
        ]);
    }
}
