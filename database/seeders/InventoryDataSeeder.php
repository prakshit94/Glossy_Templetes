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
        $warehouse = DB::table('warehouses')->where('is_default', true)->first()
                  ?? DB::table('warehouses')->first();
        $user      = DB::table('users')->where('username', 'admin')->first()
                  ?? DB::table('users')->first();

        if (!$warehouse || !$user) {
            $this->command->warn('⚠️  InventoryDataSeeder: Warehouse or User not found. Skipping.');
            return;
        }

        $products = DB::table('products')->get();

        if ($products->isEmpty()) {
            $this->command->warn('⚠️  InventoryDataSeeder: No products found. Run ProductDataSeeder first.');
            return;
        }

        // Real stock quantities per product SKU
        $stockData = [
            'UPL-CON-200SL'    => ['qty' => 120, 'reserved' => 5],
            'DPT-COR-20SC'     => ['qty' => 80,  'reserved' => 3],
            'BAYER-ROG-30EC'   => ['qty' => 150, 'reserved' => 8],
            'SYN-AMT-325SC'    => ['qty' => 90,  'reserved' => 4],
            'BASF-CAB-60WG'    => ['qty' => 110, 'reserved' => 6],
            'UPL-TAR-5EC'      => ['qty' => 70,  'reserved' => 2],
            'IFFCO-NPK-2020'   => ['qty' => 200, 'reserved' => 10],
            'COR-FER-1345'     => ['qty' => 180, 'reserved' => 7],
            'IFFCO-NANO-UREA'  => ['qty' => 350, 'reserved' => 15],
            'MHC-COT-7918'     => ['qty' => 500, 'reserved' => 20],
            'NUZ-PAD-VNCL112'  => ['qty' => 300, 'reserved' => 12],
            'HW-SPR-NEPT16L'   => ['qty' => 40,  'reserved' => 2],
            'RAL-BIO-JODI'     => ['qty' => 160, 'reserved' => 5],
        ];

        foreach ($products as $product) {
            $stock   = $stockData[$product->sku] ?? ['qty' => rand(50, 200), 'reserved' => rand(2, 10)];
            $qty     = $stock['qty'];
            $resQty  = $stock['reserved'];

            // ── Stock record ─────────────────────────────────────────────────
            DB::table('stocks')->updateOrInsert(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                [
                    'quantity'       => $qty,
                    'reserved_qty'   => $resQty,
                    'committed_qty'  => 0,
                    'in_transit_qty' => 0,
                    'dispatched_qty' => 0,
                    'status'         => 'active',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            );

            // ── Stock batch (batch & expiry tracking) ─────────────────────────
            $mfgDate    = Carbon::now()->subMonths(rand(1, 4));
            $expiryDate = Carbon::now()->addMonths(rand(12, 24));

            DB::table('stock_batches')->insert([
                'product_id'         => $product->id,
                'warehouse_id'       => $warehouse->id,
                'batch_number'       => 'BAT-' . strtoupper(Str::random(4)) . '-' . $mfgDate->format('mY'),
                'quantity'           => $qty,
                'manufacturing_date' => $mfgDate->format('Y-m-d'),
                'expiry_date'        => $expiryDate->format('Y-m-d'),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // ── Initial stock movement (GRN / Opening Stock) ──────────────────
            DB::table('stock_movements')->insert([
                'product_id'     => $product->id,
                'warehouse_id'   => $warehouse->id,
                'reference_type' => 'Opening Stock Entry',
                'reference_id'   => null,
                'quantity'       => $qty,
                'type'           => 'in',
                'created_at'     => now()->subDays(30),
                'updated_at'     => now()->subDays(30),
            ]);
        }

        // ─── Inventory Adjustment (Monthly Audit) ─────────────────────────────
        $firstProduct = DB::table('products')->first();
        if ($firstProduct) {
            $adjustmentId = DB::table('inventory_adjustments')->insertGetId([
                'reference_no' => 'ADJ-' . now()->format('Ym') . '-001',
                'warehouse_id' => $warehouse->id,
                'adjusted_by'  => $user->id,
                'reason'       => 'Monthly Physical Stock Verification – May 2026',
                'status'       => 'approved',
                'created_at'   => now()->subDays(5),
                'updated_at'   => now()->subDays(5),
            ]);

            DB::table('inventory_adjustment_items')->insert([
                'adjustment_id' => $adjustmentId,
                'product_id'    => $firstProduct->id,
                'current_qty'   => 120,
                'new_qty'       => 118,
                'difference'    => -2,
                'created_at'    => now()->subDays(5),
                'updated_at'    => now()->subDays(5),
            ]);
        }

        // ─── Stock Reservation (Pre-booked by a sales order) ──────────────────
        $reserveProduct = DB::table('products')->skip(1)->first() ?? $firstProduct;
        if ($reserveProduct) {
            DB::table('stock_reservations')->insert([
                'product_id'  => $reserveProduct->id,
                'warehouse_id'=> $warehouse->id,
                'order_id'    => null,
                'quantity'    => 5,
                'expires_at'  => now()->addDays(3),
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->command->info('✅ InventoryDataSeeder: Stock, batches, movements & adjustments seeded for ' . $products->count() . ' products.');
    }
}