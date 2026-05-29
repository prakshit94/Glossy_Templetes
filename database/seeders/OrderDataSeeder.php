<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = DB::table('parties')->where('type', 'customer')->get();
        $suppliers = DB::table('parties')->where('type', 'supplier')->get();
        $products  = DB::table('products')->get();
        $warehouse = DB::table('warehouses')->where('is_default', true)->first()
                  ?? DB::table('warehouses')->first();
        $adminUser = DB::table('users')->where('username', 'admin')->first()
                  ?? DB::table('users')->first();

        if ($customers->isEmpty() || $products->isEmpty() || !$warehouse) {
            $this->command->warn('⚠️  OrderDataSeeder: Missing customers, products, or warehouse. Skipping.');
            return;
        }

        // ─── Sample Sales Orders with realistic data ──────────────────────────
        $saleOrders = [
            [
                'customer_index' => 0,
                'status'         => 'delivered',
                'order_date'     => now()->subDays(25),
                'products'       => [
                    ['sku' => 'UPL-CON-200SL',  'qty' => 5,  'discount' => 0],
                    ['sku' => 'IFFCO-NANO-UREA', 'qty' => 10, 'discount' => 50],
                ],
            ],
            [
                'customer_index' => 1,
                'status'         => 'shipped',
                'order_date'     => now()->subDays(10),
                'products'       => [
                    ['sku' => 'MHC-COT-7918',   'qty' => 3,  'discount' => 0],
                    ['sku' => 'SYN-AMT-325SC',   'qty' => 4,  'discount' => 25],
                ],
            ],
            [
                'customer_index' => 2,
                'status'         => 'confirmed',
                'order_date'     => now()->subDays(5),
                'products'       => [
                    ['sku' => 'NUZ-PAD-VNCL112', 'qty' => 2,  'discount' => 0],
                    ['sku' => 'IFFCO-NPK-2020',  'qty' => 5,  'discount' => 100],
                    ['sku' => 'RAL-BIO-JODI',    'qty' => 3,  'discount' => 0],
                ],
            ],
            [
                'customer_index' => 3,
                'status'         => 'processing',
                'order_date'     => now()->subDays(3),
                'products'       => [
                    ['sku' => 'BAYER-ROG-30EC',  'qty' => 6,  'discount' => 0],
                    ['sku' => 'BASF-CAB-60WG',   'qty' => 4,  'discount' => 0],
                ],
            ],
            [
                'customer_index' => 4,
                'status'         => 'pending',
                'order_date'     => now()->subDay(),
                'products'       => [
                    ['sku' => 'DPT-COR-20SC',    'qty' => 2,  'discount' => 0],
                    ['sku' => 'UPL-TAR-5EC',     'qty' => 3,  'discount' => 0],
                    ['sku' => 'HW-SPR-NEPT16L',  'qty' => 1,  'discount' => 200],
                ],
            ],
            [
                'customer_index' => 5,
                'status'         => 'cancelled',
                'order_date'     => now()->subDays(15),
                'products'       => [
                    ['sku' => 'COR-FER-1345',    'qty' => 4,  'discount' => 0],
                ],
            ],
            [
                'customer_index' => 6,
                'status'         => 'delivered',
                'order_date'     => now()->subDays(20),
                'products'       => [
                    ['sku' => 'IFFCO-NANO-UREA', 'qty' => 20, 'discount' => 100],
                    ['sku' => 'MHC-COT-7918',    'qty' => 5,  'discount' => 0],
                ],
            ],
            [
                'customer_index' => 7,
                'status'         => 'ready_to_ship',
                'order_date'     => now()->subDays(2),
                'products'       => [
                    ['sku' => 'NUZ-PAD-VNCL112', 'qty' => 10, 'discount' => 0],
                    ['sku' => 'IFFCO-NPK-2020',  'qty' => 8,  'discount' => 50],
                ],
            ],
        ];

        $productMap = $products->keyBy('sku');

        foreach ($saleOrders as $index => $orderDef) {
            $customer = $customers->get($orderDef['customer_index'] % $customers->count());
            if (!$customer) continue;

            $addr = DB::table('party_addresses')
                ->where('party_id', $customer->id)
                ->where('is_default', true)
                ->first();

            $orderTotal   = 0;
            $orderTax     = 0;
            $orderDiscount = 0;

            $orderNo = 'SO-' . now()->format('Ym') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $orderId = DB::table('orders')->insertGetId([
                'order_no'           => $orderNo,
                'type'               => 'sale',
                'party_id'           => $customer->id,
                'order_date'         => $orderDef['order_date'],
                'total_amount'       => 0,
                'tax_amount'         => 0,
                'discount_amount'    => 0,
                'net_amount'         => 0,
                'status'             => $orderDef['status'],
                'warehouse_id'       => $warehouse->id,
                'shipping_address_id'=> $addr?->id,
                'billing_address_id' => $addr?->id,
                'shipping_address'   => $addr ? "{$addr->address_line_1}, {$addr->city}, {$addr->state} - {$addr->pincode}" : null,
                'billing_address'    => $addr ? "{$addr->address_line_1}, {$addr->city}, {$addr->state} - {$addr->pincode}" : null,
                'created_by'         => $adminUser?->id,
                'created_at'         => $orderDef['order_date'],
                'updated_at'         => now(),
            ]);

            foreach ($orderDef['products'] as $pd) {
                $product = $productMap[$pd['sku']] ?? null;
                if (!$product) continue;

                $qty       = $pd['qty'];
                $unitPrice = $product->selling_price;
                $discount  = $pd['discount'];
                $taxRate   = 18;
                $lineBase  = ($qty * $unitPrice) - $discount;
                $taxAmt    = round($lineBase * ($taxRate / 100), 2);
                $lineTotal = round($lineBase + $taxAmt, 2);

                $orderTotal    += $qty * $unitPrice;
                $orderTax      += $taxAmt;
                $orderDiscount += $discount;

                $itemId = DB::table('order_items')->insertGetId([
                    'order_id'          => $orderId,
                    'product_id'        => $product->id,
                    'product_variant_id'=> null,
                    'quantity'          => $qty,
                    'unit_price'        => $unitPrice,
                    'tax_rate'          => $taxRate,
                    'tax_amount'        => $taxAmt,
                    'discount_amount'   => $discount,
                    'total_amount'      => $lineTotal,
                    'created_at'        => $orderDef['order_date'],
                    'updated_at'        => now(),
                ]);

                DB::table('order_allocations')->insert([
                    'order_item_id' => $itemId,
                    'warehouse_id'  => $warehouse->id,
                    'allocated_qty' => $qty,
                    'created_at'    => $orderDef['order_date'],
                    'updated_at'    => now(),
                ]);
            }

            $netAmount = round(($orderTotal - $orderDiscount) + $orderTax, 2);

            DB::table('orders')->where('id', $orderId)->update([
                'total_amount'    => round($orderTotal, 2),
                'tax_amount'      => round($orderTax, 2),
                'discount_amount' => round($orderDiscount, 2),
                'net_amount'      => $netAmount,
            ]);
        }

        // ─── Purchase Orders ──────────────────────────────────────────────────
        if ($suppliers->isNotEmpty()) {
            foreach ($suppliers->take(3) as $si => $supplier) {
                $poProducts = $products->random(min(3, $products->count()));
                $poTotal    = 0;
                $poTax      = 0;

                $poNo    = 'PO-' . now()->format('Ym') . '-' . str_pad($si + 1, 3, '0', STR_PAD_LEFT);
                $poDate  = now()->subDays(rand(5, 20));

                $supplierAddr = DB::table('party_addresses')
                    ->where('party_id', $supplier->id)
                    ->first();

                $poId = DB::table('orders')->insertGetId([
                    'order_no'        => $poNo,
                    'type'            => 'purchase',
                    'party_id'        => $supplier->id,
                    'order_date'      => $poDate,
                    'total_amount'    => 0,
                    'tax_amount'      => 0,
                    'discount_amount' => 0,
                    'net_amount'      => 0,
                    'status'          => 'confirmed',
                    'warehouse_id'    => $warehouse->id,
                    'shipping_address_id' => $supplierAddr?->id,
                    'created_by'      => $adminUser?->id,
                    'created_at'      => $poDate,
                    'updated_at'      => now(),
                ]);

                foreach ($poProducts as $product) {
                    $qty       = rand(20, 100);
                    $unitPrice = $product->purchase_price;
                    $taxRate   = 18;
                    $taxAmt    = round($qty * $unitPrice * ($taxRate / 100), 2);
                    $lineTotal = round(($qty * $unitPrice) + $taxAmt, 2);

                    $poTotal += $qty * $unitPrice;
                    $poTax   += $taxAmt;

                    DB::table('order_items')->insert([
                        'order_id'          => $poId,
                        'product_id'        => $product->id,
                        'product_variant_id'=> null,
                        'quantity'          => $qty,
                        'unit_price'        => $unitPrice,
                        'tax_rate'          => $taxRate,
                        'tax_amount'        => $taxAmt,
                        'discount_amount'   => 0,
                        'total_amount'      => $lineTotal,
                        'created_at'        => $poDate,
                        'updated_at'        => now(),
                    ]);
                }

                DB::table('orders')->where('id', $poId)->update([
                    'total_amount' => round($poTotal, 2),
                    'tax_amount'   => round($poTax, 2),
                    'net_amount'   => round($poTotal + $poTax, 2),
                ]);
            }
        }

        $this->command->info('✅ OrderDataSeeder: ' . count($saleOrders) . ' sale orders & 3 purchase orders seeded.');
    }
}
