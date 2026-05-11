<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryDataSeeder extends Seeder
{
    public function run(): void
    {
        $products = DB::table('products')->get();
        $warehouse = DB::table('warehouses')->first();
        $user = DB::table('users')->first();

        foreach ($products as $product) {
            // Initial Stocks
            DB::table('stocks')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 100,
                'reserved_qty' => 0,
                'committed_qty' => 0,
                'in_transit_qty' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Batches
            DB::table('stock_batches')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'batch_number' => 'BAT-' . rand(1000, 9999),
                'quantity' => 100,
                'manufacturing_date' => now()->subMonths(2),
                'expiry_date' => now()->addYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Movements
            DB::table('stock_movements')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'reference_type' => 'Initial Stock',
                'reference_id' => null,
                'quantity' => 100,
                'type' => 'in',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Adjustments
        $adjId = DB::table('inventory_adjustments')->insertGetId([
            'reference_no' => 'ADJ-' . Str::random(6),
            'warehouse_id' => $warehouse->id,
            'adjusted_by' => $user->id,
            'reason' => 'Cycle Count',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstProd = $products->first();
        DB::table('inventory_adjustment_items')->insert([
            'adjustment_id' => $adjId,
            'product_id' => $firstProd->id,
            'current_qty' => 100,
            'new_qty' => 105,
            'difference' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Reservations
        DB::table('stock_reservations')->insert([
            'product_id' => $firstProd->id,
            'warehouse_id' => $warehouse->id,
            'order_id' => null,
            'quantity' => 10,
            'expires_at' => now()->addDays(2),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
