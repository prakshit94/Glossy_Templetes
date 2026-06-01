<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccountingTransaction;
use App\Models\Expense;
use App\Models\Ledger;
use Illuminate\Support\Collection;

class FinancialReportController extends Controller
{
    public function index()
    {
        $records = new Collection([
            [
                'name' => 'Chart of Accounts',
                'period' => 'Current',
                'generated_at' => now(),
                'format' => Ledger::count() . ' ledgers',
            ],
            [
                'name' => 'Transaction Register',
                'period' => now()->format('M Y'),
                'generated_at' => now(),
                'format' => AccountingTransaction::count() . ' entries',
            ],
            [
                'name' => 'Expense Summary',
                'period' => now()->format('M Y'),
                'generated_at' => now(),
                'format' => '₹' . number_format((float) Expense::sum('amount'), 2),
            ],
        ]);

        return view('financial-reports.index', [
            'moduleKey' => 'financial-reports',
            'moduleTitle' => 'Financial Reports',
            'moduleIcon' => 'bar-chart',
            'records' => $records,
        ]);
    }
}
