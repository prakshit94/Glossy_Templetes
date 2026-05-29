<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountingDataSeeder extends Seeder
{
    public function run(): void
    {
        $orders = DB::table('orders')->where('type', 'sale')->get();
        $user   = DB::table('users')->where('username', 'admin')->first()
                ?? DB::table('users')->first();

        // ─── 1. Ledgers ───────────────────────────────────────────────────────
        $ledgers = [
            ['name' => 'Cash in Hand',        'code' => 'CASH-001', 'type' => 'asset',     'opening_balance' => 50000.00],
            ['name' => 'HDFC Bank – Current',  'code' => 'BANK-001', 'type' => 'asset',     'opening_balance' => 250000.00],
            ['name' => 'SBI Bank – OD',        'code' => 'BANK-002', 'type' => 'liability', 'opening_balance' => 0.00],
            ['name' => 'Sales Revenue',        'code' => 'SALES-001','type' => 'income',    'opening_balance' => 0.00],
            ['name' => 'Purchase Expense',     'code' => 'PURCH-001','type' => 'expense',   'opening_balance' => 0.00],
            ['name' => 'GST Payable',          'code' => 'GST-001',  'type' => 'liability', 'opening_balance' => 0.00],
            ['name' => 'TDS Payable',          'code' => 'TDS-001',  'type' => 'liability', 'opening_balance' => 0.00],
            ['name' => 'Freight & Logistics',  'code' => 'FRGT-001', 'type' => 'expense',   'opening_balance' => 0.00],
            ['name' => 'Office Rent',          'code' => 'RENT-001', 'type' => 'expense',   'opening_balance' => 0.00],
            ['name' => 'Salaries & Wages',     'code' => 'SAL-001',  'type' => 'expense',   'opening_balance' => 0.00],
        ];

        foreach ($ledgers as $l) {
            DB::table('ledgers')->updateOrInsert(
                ['code' => $l['code']],
                array_merge($l, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $cashLedger  = DB::table('ledgers')->where('code', 'CASH-001')->first();
        $bankLedger  = DB::table('ledgers')->where('code', 'BANK-001')->first();
        $salesLedger = DB::table('ledgers')->where('code', 'SALES-001')->first();
        $gstLedger   = DB::table('ledgers')->where('code', 'GST-001')->first();

        // ─── 2. Invoices, Payments, Ledger Entries per Sale Order ────────────
        $paymentMethods = ['Cash', 'UPI', 'NEFT', 'RTGS', 'Cheque'];

        foreach ($orders as $order) {
            // Invoice
            $invoiceNo = 'INV-' . now()->format('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_no'   => $invoiceNo,
                'order_id'     => $order->id,
                'invoice_date' => now()->subDays(rand(1, 5)),
                'total_amount' => $order->total_amount,
                'tax_amount'   => $order->tax_amount,
                'net_amount'   => $order->net_amount,
                'status'       => in_array($order->status, ['delivered', 'shipped']) ? 'paid' : 'unpaid',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Invoice items from order items
            $orderItems = DB::table('order_items')->where('order_id', $order->id)->get();
            foreach ($orderItems as $oi) {
                DB::table('invoice_items')->insert([
                    'invoice_id'   => $invoiceId,
                    'order_item_id'=> $oi->id,
                    'quantity'     => $oi->quantity,
                    'unit_price'   => $oi->unit_price,
                    'tax_amount'   => $oi->tax_amount,
                    'total_amount' => $oi->total_amount,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            // Payment (only for delivered/shipped orders)
            if (in_array($order->status, ['delivered', 'shipped'])) {
                $method    = $paymentMethods[array_rand($paymentMethods)];
                $paymentId = DB::table('payments')->insertGetId([
                    'payment_no'     => 'PAY-' . strtoupper(Str::random(8)),
                    'invoice_id'     => $invoiceId,
                    'order_id'       => $order->id,
                    'amount'         => $order->net_amount,
                    'payment_method' => $method,
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
                    'payment_date'   => now()->subDays(rand(0, 3)),
                    'status'         => 'completed',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Double-entry booking
                $txnId = DB::table('accounting_transactions')->insertGetId([
                    'transaction_no'   => 'ACC-' . strtoupper(Str::random(6)),
                    'reference_no'     => (string) $paymentId,
                    'transaction_date' => now(),
                    'description'      => "Payment received for {$invoiceNo} via {$method}",
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                // Debit Cash/Bank, Credit Sales
                $debitLedger = $method === 'Cash' ? $cashLedger : $bankLedger;
                DB::table('accounting_entries')->insert([
                    ['transaction_id' => $txnId, 'ledger_id' => $debitLedger->id,  'debit' => $order->net_amount,  'credit' => 0,                     'created_at' => now(), 'updated_at' => now()],
                    ['transaction_id' => $txnId, 'ledger_id' => $salesLedger->id,  'debit' => 0,                   'credit' => $order->total_amount,   'created_at' => now(), 'updated_at' => now()],
                    ['transaction_id' => $txnId, 'ledger_id' => $gstLedger->id,    'debit' => 0,                   'credit' => $order->tax_amount,     'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        }

        // ─── 3. Expenses ──────────────────────────────────────────────────────
        $expenses = [
            ['category' => 'Office Rent',          'amount' => 25000.00, 'description' => 'Monthly warehouse office rent for May 2026',           'status' => 'approved'],
            ['category' => 'Electricity',          'amount' => 8500.00,  'description' => 'Warehouse electricity bill – May 2026',                'status' => 'approved'],
            ['category' => 'Freight & Logistics',  'amount' => 12000.00, 'description' => 'Third-party transport charges – bulk dispatch May 2026','status' => 'approved'],
            ['category' => 'Marketing & Promotion','amount' => 15000.00, 'description' => 'Kharif season campaign – SMS & email blast',            'status' => 'approved'],
            ['category' => 'Packaging Material',   'amount' => 4200.00,  'description' => 'Cartons, bubble wrap and labels for dispatch',          'status' => 'approved'],
            ['category' => 'Staff Conveyance',     'amount' => 3000.00,  'description' => 'Field executive travel allowance – May 2026',           'status' => 'pending'],
        ];

        foreach ($expenses as $expense) {
            DB::table('expenses')->insert(array_merge($expense, [
                'date'       => now()->subDays(rand(1, 15)),
                'user_id'    => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ AccountingDataSeeder: Ledgers, invoices, payments, transactions & expenses seeded.');
    }
}
