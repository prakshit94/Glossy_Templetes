<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InventoryDataSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = DB::table('warehouses')->first();
        $user = DB::table('users')->first();

        if (!$warehouse || !$user) {
            $this->command->warn('Warehouse or User not found.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Real Product Catalog
        |--------------------------------------------------------------------------
        */

        $products = [
            [
                'name' => 'Apple iPhone 15 Pro',
                'sku' => 'IPH15PRO',
                'price' => 129999,
                'stock' => 40,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'sku' => 'SGS24ULT',
                'price' => 119999,
                'stock' => 35,
                'category' => 'Electronics',
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'sku' => 'SONYX5',
                'price' => 29999,
                'stock' => 60,
                'category' => 'Accessories',
            ],
            [
                'name' => 'Dell XPS 15 Laptop',
                'sku' => 'DELLXPS15',
                'price' => 189999,
                'stock' => 20,
                'category' => 'Computers',
            ],
            [
                'name' => 'Apple Watch Series 9',
                'sku' => 'AWS9',
                'price' => 45999,
                'stock' => 50,
                'category' => 'Wearables',
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'sku' => 'LOGIMX3S',
                'price' => 9999,
                'stock' => 80,
                'category' => 'Accessories',
            ],
            [
                'name' => 'Nike Air Max Running Shoes',
                'sku' => 'NIKEAIRMAX',
                'price' => 7999,
                'stock' => 120,
                'category' => 'Footwear',
            ],
            [
                'name' => 'Boat Rockerz 450 Headphones',
                'sku' => 'BOAT450',
                'price' => 1999,
                'stock' => 150,
                'category' => 'Audio',
            ],
            [
                'name' => 'HP LaserJet Pro Printer',
                'sku' => 'HPLJPRO',
                'price' => 17999,
                'stock' => 25,
                'category' => 'Office',
            ],
            [
                'name' => 'Canon EOS R50 Camera',
                'sku' => 'CANONR50',
                'price' => 74999,
                'stock' => 15,
                'category' => 'Cameras',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Insert Products + Inventory Data
        |--------------------------------------------------------------------------
        */

        foreach ($products as $item) {

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $productId = DB::table('products')->insertGetId([
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'sku' => $item['sku'],
                'selling_price' => $item['price'],
                'mrp' => $item['price'] + rand(1000, 5000),
                'manage_stock' => true,
                'allow_overselling' => false,
                'status' => 'active',
                'weight' => rand(1, 5) . ' Kg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Stock
            |--------------------------------------------------------------------------
            */

            DB::table('stocks')->insert([
                'product_id' => $productId,
                'warehouse_id' => $warehouse->id,
                'quantity' => $item['stock'],
                'reserved_qty' => rand(0, 5),
                'committed_qty' => rand(0, 3),
                'in_transit_qty' => rand(0, 2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Stock Batch
            |--------------------------------------------------------------------------
            */

            DB::table('stock_batches')->insert([
                'product_id' => $productId,
                'warehouse_id' => $warehouse->id,
                'batch_number' => 'BAT-' . strtoupper(Str::random(6)),
                'quantity' => $item['stock'],
                'manufacturing_date' => Carbon::now()->subMonths(rand(1, 6)),
                'expiry_date' => Carbon::now()->addYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Stock Movement
            |--------------------------------------------------------------------------
            */

            DB::table('stock_movements')->insert([
                'product_id' => $productId,
                'warehouse_id' => $warehouse->id,
                'reference_type' => 'Initial Stock Entry',
                'reference_id' => null,
                'quantity' => $item['stock'],
                'type' => 'in',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Inventory Adjustment
        |--------------------------------------------------------------------------
        */

        $firstProduct = DB::table('products')->first();

        $adjustmentId = DB::table('inventory_adjustments')->insertGetId([
            'reference_no' => 'ADJ-' . strtoupper(Str::random(6)),
            'warehouse_id' => $warehouse->id,
            'adjusted_by' => $user->id,
            'reason' => 'Monthly Stock Audit',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('inventory_adjustment_items')->insert([
            'adjustment_id' => $adjustmentId,
            'product_id' => $firstProduct->id,
            'current_qty' => 40,
            'new_qty' => 45,
            'difference' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Stock Reservation
        |--------------------------------------------------------------------------
        */

        DB::table('stock_reservations')->insert([
            'product_id' => $firstProduct->id,
            'warehouse_id' => $warehouse->id,
            'order_id' => null,
            'quantity' => 3,
            'expires_at' => now()->addDays(2),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Inventory demo data seeded successfully.');
    }
}