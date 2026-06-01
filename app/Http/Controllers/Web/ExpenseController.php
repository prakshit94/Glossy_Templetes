<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $records = Expense::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest('date')
            ->paginate((int) $request->get('perPage', 10))
            ->withQueryString();

        return view('expenses.index', [
            'moduleKey' => 'expenses',
            'moduleTitle' => 'Expenses',
            'moduleIcon' => 'activity',
            'records' => $records,
        ]);
    }
}
