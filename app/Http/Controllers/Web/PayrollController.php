<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $records = Payroll::query()
            ->with('employee.user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('employee.user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            })
            ->latest('year')
            ->latest('month')
            ->paginate((int) $request->get('perPage', 10))
            ->withQueryString();

        return view('payroll.index', [
            'moduleKey' => 'payroll',
            'moduleTitle' => 'Payroll',
            'moduleIcon' => 'finance',
            'records' => $records,
        ]);
    }
}
