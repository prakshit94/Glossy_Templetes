<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Brands ────────────────────────────────────────────────────────
        $brands = [
            'UPL',
            'Bayer',
            'Syngenta',
            'Rallis India',
            'IFFCO',
            'Coromandel',
            'Mahyco',
            'Nuziveedu Seeds',
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->updateOrInsert(
                ['name' => $brand],
                [
                    'slug'       => Str::slug($brand),
                    'logo'       => 'logos/' . Str::slug($brand) . '.png',
                    'is_active'  => true,
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // ─── 2. Categories ────────────────────────────────────────────────────
        $categories = [
            [
                'name' => 'CP',
                'sub'  => [
                    'Insecticides',
                    'Fungicides',
                    'Herbicides',
                    'Pesticides',
                    'Bio Pesticides',
                    'Plant Growth Regulators',
                    'Rodenticides',
                    'Nematicides',
                ],
            ],
            [
                'name' => 'CN',
                'sub'  => [
                    'Fertilizers',
                    'Organic Fertilizers',
                    'Bio Fertilizers',
                    'Micronutrients',
                    'Water Soluble Fertilizers',
                    'Granular Fertilizers',
                    'Liquid Fertilizers',
                    'Soil Conditioners',
                ],
            ],
            [
                'name' => 'Hardware',
                'sub'  => [
                    'Sprayers',
                    'Pumps',
                    'Irrigation Equipment',
                    'Pipes & Fittings',
                    'Drip Accessories',
                    'Garden Tools',
                    'Agricultural Tools',
                    'Shade Nets',
                ],
            ],
            [
                'name' => 'Seed',
                'sub'  => [
                    'Field Crop Seeds',
                    'Vegetable Seeds',
                    'Fruit Seeds',
                    'Hybrid Seeds',
                    'Organic Seeds',
                    'Flower Seeds',
                    'Fodder Seeds',
                    'Oilseed Seeds',
                ],
            ],
        ];

        foreach ($categories as $cat) {
            $parentId = DB::table('categories')
                ->where('name', $cat['name'])
                ->whereNull('parent_id')
                ->value('id');

            if (!$parentId) {
                $parentId = DB::table('categories')->insertGetId([
                    'name'       => $cat['name'],
                    'slug'       => Str::slug($cat['name']),
                    'parent_id'  => null,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($cat['sub'] as $sub) {
                $exists = DB::table('categories')
                    ->where('name', $sub)
                    ->where('parent_id', $parentId)
                    ->exists();

                if (!$exists) {
                    DB::table('categories')->insert([
                        'name'       => $sub,
                        'slug'       => Str::slug($sub),
                        'parent_id'  => $parentId,
                        'is_active'  => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // ─── 3. Tax Rates ─────────────────────────────────────────────────────
        $taxRates = [
            ['name' => 'GST 0%',  'rate' => 0.00],
            ['name' => 'GST 5%',  'rate' => 5.00],
            ['name' => 'GST 12%', 'rate' => 12.00],
            ['name' => 'GST 18%', 'rate' => 18.00],
            ['name' => 'GST 28%', 'rate' => 28.00],
            ['name' => 'Exempt',  'rate' => 0.00],
        ];

        foreach ($taxRates as $tax) {
            DB::table('tax_rates')->updateOrInsert(
                ['name' => $tax['name']],
                array_merge($tax, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ─── 4. HSN Codes ─────────────────────────────────────────────────────
        $hsnCodes = [
            ['code' => '3808', 'description' => 'Insecticides, rodenticides, fungicides, herbicides, plant growth regulators'],
            ['code' => '3101', 'description' => 'Animal or vegetable fertilisers; manure'],
            ['code' => '3105', 'description' => 'Mineral or chemical fertilisers containing NPK'],
            ['code' => '8424', 'description' => 'Mechanical appliances for projecting, dispersing or spraying liquids – sprayers'],
            ['code' => '8432', 'description' => 'Agricultural, horticultural or forestry machinery for soil preparation'],
            ['code' => '1209', 'description' => 'Seeds, fruit and spores, of a kind used for sowing'],
        ];

        foreach ($hsnCodes as $hsn) {
            DB::table('hsn_codes')->updateOrInsert(
                ['code' => $hsn['code']],
                array_merge($hsn, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ─── 5. Units of Measure ─────────────────────────────────────────────
        $uoms = [
            ['name' => 'Kilogram',   'code' => 'KG',  'is_base_unit' => true],
            ['name' => 'Gram',       'code' => 'GM',  'is_base_unit' => false],
            ['name' => 'Liter',      'code' => 'LTR', 'is_base_unit' => true],
            ['name' => 'Milliliter', 'code' => 'ML',  'is_base_unit' => false],
            ['name' => 'Piece',      'code' => 'PCS', 'is_base_unit' => true],
            ['name' => 'Packet',     'code' => 'PKT', 'is_base_unit' => false],
            ['name' => 'Bag',        'code' => 'BAG', 'is_base_unit' => false],
        ];

        foreach ($uoms as $uom) {
            DB::table('units_of_measure')->updateOrInsert(
                ['code' => $uom['code']],
                array_merge($uom, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ─── 6. UOM Conversions ───────────────────────────────────────────────
        $kgId  = DB::table('units_of_measure')->where('code', 'KG')->value('id');
        $gmId  = DB::table('units_of_measure')->where('code', 'GM')->value('id');
        $ltrId = DB::table('units_of_measure')->where('code', 'LTR')->value('id');
        $mlId  = DB::table('units_of_measure')->where('code', 'ML')->value('id');

        if ($kgId && $gmId) {
            DB::table('uom_conversions')->updateOrInsert(
                ['from_uom_id' => $kgId, 'to_uom_id' => $gmId],
                ['conversion_factor' => 1000, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        if ($ltrId && $mlId) {
            DB::table('uom_conversions')->updateOrInsert(
                ['from_uom_id' => $ltrId, 'to_uom_id' => $mlId],
                ['conversion_factor' => 1000, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // ─── 7. Crops ─────────────────────────────────────────────────────────
        $crops = [
            'Wheat', 'Rice', 'Cotton', 'Sugarcane', 'Soybean',
            'Groundnut', 'Maize', 'Bajra', 'Jowar', 'Mustard',
            'Onion', 'Tomato', 'Chilli', 'Potato', 'Gram',
        ];

        foreach ($crops as $crop) {
            DB::table('crops')->updateOrInsert(
                ['name' => $crop],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // ─── 8. Irrigation Types ──────────────────────────────────────────────
        $irrigations = ['Drip', 'Sprinkler', 'Flood', 'Canal', 'Borewell', 'Rainfed', 'River Pump'];

        foreach ($irrigations as $irr) {
            DB::table('irrigation_types')->updateOrInsert(
                ['name' => $irr],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // ─── 9. Land Units ────────────────────────────────────────────────────
        $landUnits = [
            ['name' => 'Acre',    'conversion_to_sq_mt' => 4046.86],
            ['name' => 'Hectare', 'conversion_to_sq_mt' => 10000.00],
            ['name' => 'Bigha',   'conversion_to_sq_mt' => 2500.00],
            ['name' => 'Guntha',  'conversion_to_sq_mt' => 101.17],
            ['name' => 'Kanal',   'conversion_to_sq_mt' => 505.86],
            ['name' => 'Marla',   'conversion_to_sq_mt' => 25.29],
        ];

        foreach ($landUnits as $unit) {
            DB::table('land_units')->updateOrInsert(
                ['name' => $unit['name']],
                array_merge($unit, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ─── 10. Account Types ────────────────────────────────────────────────
        $accTypes = [
            'Cash', 'Bank', 'Sales', 'Purchase',
            'Inventory', 'Expense', 'Customer', 'Supplier',
        ];

        foreach ($accTypes as $acc) {
            DB::table('account_types')->updateOrInsert(
                ['slug' => Str::slug($acc)],
                [
                    'name'       => $acc,
                    'slug'       => Str::slug($acc),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ MasterDataSeeder: Brands, Categories, Tax, HSN, UOM, Crops, Irrigations, Land Units & Account Types seeded.');
    }
}