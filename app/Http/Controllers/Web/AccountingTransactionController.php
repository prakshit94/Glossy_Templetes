<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccountingTransaction;
use Illuminate\Http\Request;

class AccountingTransactionController extends Controller
{
    public function index(Request $request)
    {
        $records = AccountingTransaction::query()
            ->with('entries.ledger')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($inner) use ($search) {
                    $inner->where('transaction_no', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('transaction_date')
            ->paginate((int) $request->get('perPage', 10))
            ->withQueryString();

        return view('transactions.index', [
            'moduleKey' => 'transactions',
            'moduleTitle' => 'Transactions',
            'moduleIcon' => 'credit-card',
            'records' => $records,
        ]);
    }
}
