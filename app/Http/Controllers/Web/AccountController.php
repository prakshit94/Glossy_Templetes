<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ledger;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $records = Ledger::query()
            ->withSum('entries', 'debit')
            ->withSum('entries', 'credit')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) $request->get('perPage', 10))
            ->withQueryString();

        return view('accounts.index', [
            'moduleKey' => 'accounts',
            'moduleTitle' => 'Accounts',
            'moduleIcon' => 'finance',
            'records' => $records,
        ]);
    }
}
