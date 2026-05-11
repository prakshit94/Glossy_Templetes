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
        $user = DB::table('users')->first();

        // 1. Ledgers
        $ledgers = [
            ['name' => 'Cash in Hand', 'code' => 'CASH-001', 'type' => 'asset'],
            ['name' => 'Bank Account', 'code' => 'BANK-001', 'type' => 'asset'],
            ['name' => 'Sales Revenue', 'code' => 'SALES-001', 'type' => 'income'],
            ['name' => 'Purchase Expense', 'code' => 'PURCH-001', 'type' => 'expense'],
            ['name' => 'TDS Payable', 'code' => 'TDS-001', 'type' => 'liability'],
        ];
        foreach ($ledgers as $l) {
            DB::table('ledgers')->insert(array_merge($l, ['opening_balance' => 0, 'created_at' => now(), 'updated_at' => now()]));
        }

        $cashLedger = DB::table('ledgers')->where('code', 'CASH-001')->first();
        $salesLedger = DB::table('ledgers')->where('code', 'SALES-001')->first();

        foreach ($orders as $order) {
            // 2. Invoices
            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_no' => 'INV-' . Str::random(6),
                'order_id' => $order->id,
                'invoice_date' => now(),
                'total_amount' => $order->total_amount,
                'tax_amount' => $order->tax_amount,
                'net_amount' => $order->net_amount,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $orderItem = DB::table('order_items')->where('order_id', $order->id)->first();
            DB::table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'order_item_id' => $orderItem->id,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'tax_amount' => $orderItem->tax_amount,
                'total_amount' => $orderItem->total_amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Payments
            $paymentId = DB::table('payments')->insertGetId([
                'payment_no' => 'PAY-' . Str::random(6),
                'invoice_id' => $invoiceId,
                'order_id' => $order->id,
                'amount' => $order->net_amount,
                'payment_method' => 'Cash',
                'transaction_id' => 'TXN-' . Str::random(8),
                'payment_date' => now(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Ledger Entries & Transactions
            $txnId = DB::table('accounting_transactions')->insertGetId([
                'transaction_no' => 'ACC-' . Str::random(6),
                'reference_no' => $paymentId,
                'transaction_date' => now(),
                'description' => "Payment received for INV-$invoiceId",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('accounting_entries')->insert([
                ['transaction_id' => $txnId, 'ledger_id' => $cashLedger->id, 'debit' => $order->net_amount, 'credit' => 0, 'created_at' => now(), 'updated_at' => now()],
                ['transaction_id' => $txnId, 'ledger_id' => $salesLedger->id, 'debit' => 0, 'credit' => $order->net_amount, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 5. Expenses
        DB::table('expenses')->insert([
            'category' => 'Office Rent',
            'amount' => 5000.00,
            'date' => now(),
            'user_id' => $user->id,
            'description' => 'Monthly office rent payment',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
