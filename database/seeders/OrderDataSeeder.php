<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = DB::table('parties')->where('type', 'customer')->get();
        $suppliers = DB::table('parties')->where('type', 'supplier')->get();
        $products = DB::table('products')->get();
        $warehouse = DB::table('warehouses')->first();

        // 1. Sale Orders
        foreach ($customers->take(5) as $customer) {
            $orderId = DB::table('orders')->insertGetId([
                'order_no' => 'SO-' . Str::random(6),
                'type' => 'sale',
                'party_id' => $customer->id,
                'order_date' => now(),
                'total_amount' => 0, // Calculated below
                'tax_amount' => 0,
                'discount_amount' => 0,
                'net_amount' => 0,
                'status' => 'confirmed',
                'warehouse_id' => $warehouse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total = 0;
            $items = $products->random(rand(1, 3));
            foreach ($items as $product) {
                $qty = rand(1, 5);
                $price = $product->selling_price;
                $lineTotal = $qty * $price;
                $total += $lineTotal;

                $itemId = DB::table('order_items')->insertGetId([
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => 18,
                    'tax_amount' => $lineTotal * 0.18,
                    'discount_amount' => 0,
                    'total_amount' => $lineTotal * 1.18,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Allocations
                DB::table('order_allocations')->insert([
                    'order_item_id' => $itemId,
                    'warehouse_id' => $warehouse->id,
                    'allocated_qty' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('orders')->where('id', $orderId)->update([
                'total_amount' => $total,
                'tax_amount' => $total * 0.18,
                'net_amount' => $total * 1.18,
            ]);
        }

        // 2. Purchase Orders
        foreach ($suppliers->take(2) as $supplier) {
            DB::table('orders')->insert([
                'order_no' => 'PO-' . Str::random(6),
                'type' => 'purchase',
                'party_id' => $supplier->id,
                'order_date' => now(),
                'total_amount' => 5000,
                'tax_amount' => 900,
                'net_amount' => 5900,
                'status' => 'pending',
                'warehouse_id' => $warehouse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
