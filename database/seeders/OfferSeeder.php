<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch product IDs for BOGO offers
        $nanosUrea  = DB::table('products')->where('sku', 'IFFCO-NANO-UREA')->first();
        $cottonSeed = DB::table('products')->where('sku', 'MHC-COT-7918')->first();
        $sprayer    = DB::table('products')->where('sku', 'HW-SPR-NEPT16L')->first();

        $offers = [
            // ── Order-level discount offers ────────────────────────────────────
            [
                'name'          => 'Kharif Season: 10% Off on Orders Above ₹5,000',
                'type'          => 'order_discount',
                'discount_type' => 'percentage',
                'value'         => 10.00,
                'min_spend'     => 5000.00,
                'max_discount'  => 2000.00,
                'product_id'    => null,
                'buy_qty'       => 1,
                'get_qty'       => 1,
                'starts_at'     => now()->subDays(2),
                'ends_at'       => now()->addMonths(3),
                'priority'      => 100,
                'is_active'     => true,
            ],
            [
                'name'          => 'Rabi Special: Flat ₹500 Off on Orders Above ₹3,000',
                'type'          => 'order_discount',
                'discount_type' => 'fixed',
                'value'         => 500.00,
                'min_spend'     => 3000.00,
                'max_discount'  => 500.00,
                'product_id'    => null,
                'buy_qty'       => 1,
                'get_qty'       => 1,
                'starts_at'     => now()->subDay(),
                'ends_at'       => now()->addMonths(5),
                'priority'      => 90,
                'is_active'     => true,
            ],
            [
                'name'          => 'Mega Purchase: 15% Off on Orders Above ₹10,000',
                'type'          => 'order_discount',
                'discount_type' => 'percentage',
                'value'         => 15.00,
                'min_spend'     => 10000.00,
                'max_discount'  => 3000.00,
                'product_id'    => null,
                'buy_qty'       => 1,
                'get_qty'       => 1,
                'starts_at'     => now()->subDays(5),
                'ends_at'       => now()->addMonths(6),
                'priority'      => 80,
                'is_active'     => true,
            ],
            [
                'name'          => 'New Farmer Welcome: Flat ₹200 Off on First Order Above ₹1,000',
                'type'          => 'order_discount',
                'discount_type' => 'fixed',
                'value'         => 200.00,
                'min_spend'     => 1000.00,
                'max_discount'  => 200.00,
                'product_id'    => null,
                'buy_qty'       => 1,
                'get_qty'       => 1,
                'starts_at'     => now()->subDays(30),
                'ends_at'       => now()->addYear(),
                'priority'      => 70,
                'is_active'     => true,
            ],

            // ── BOGO offers (product-specific) ────────────────────────────────
            [
                'name'          => 'Buy 2 Get 1 Free – IFFCO Nano Urea 500ml',
                'type'          => 'bogo',
                'discount_type' => null,
                'value'         => 0.00,
                'min_spend'     => 0.00,
                'max_discount'  => null,
                'product_id'    => $nanosUrea?->id,
                'buy_qty'       => 2,
                'get_qty'       => 1,
                'starts_at'     => now()->subDays(3),
                'ends_at'       => now()->addMonths(2),
                'priority'      => 110,
                'is_active'     => true,
            ],
            [
                'name'          => 'Buy 1 Get 1 Free – Mahyco Cotton BG-II Seed Packet',
                'type'          => 'bogo',
                'discount_type' => null,
                'value'         => 0.00,
                'min_spend'     => 0.00,
                'max_discount'  => null,
                'product_id'    => $cottonSeed?->id,
                'buy_qty'       => 1,
                'get_qty'       => 1,
                'starts_at'     => now()->subDays(1),
                'ends_at'       => now()->addMonths(1),
                'priority'      => 105,
                'is_active'     => true,
            ],
            [
                'name'          => 'Buy 2 Sprayers Get 1 Free – Neptune 16L Battery Sprayer',
                'type'          => 'bogo',
                'discount_type' => null,
                'value'         => 0.00,
                'min_spend'     => 0.00,
                'max_discount'  => null,
                'product_id'    => $sprayer?->id,
                'buy_qty'       => 2,
                'get_qty'       => 1,
                'starts_at'     => now()->subDays(7),
                'ends_at'       => now()->addMonths(2),
                'priority'      => 95,
                'is_active'     => true,
            ],

            // ── Inactive / expired offers for history ──────────────────────────
            [
                'name'          => 'Zaid Sale 2025: 20% Off Above ₹4,000 [Expired]',
                'type'          => 'order_discount',
                'discount_type' => 'percentage',
                'value'         => 20.00,
                'min_spend'     => 4000.00,
                'max_discount'  => 1500.00,
                'product_id'    => null,
                'buy_qty'       => 1,
                'get_qty'       => 1,
                'starts_at'     => now()->subMonths(4),
                'ends_at'       => now()->subMonths(1),
                'priority'      => 60,
                'is_active'     => false,
            ],
        ];

        foreach ($offers as $offer) {
            DB::table('offers')->updateOrInsert(
                ['name' => $offer['name']],
                array_merge($offer, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('✅ OfferSeeder: ' . count($offers) . ' offers seeded (4 order discounts, 3 BOGO, 1 expired).');
    }
}
