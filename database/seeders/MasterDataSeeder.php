<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Brands
        $brands = [
            'UPL',
            'Bayer',
            'Syngenta',
            'Rallis India',
            'IFFCO',
            'Coromandel',
            'Mahyco',
            'Nuziveedu Seeds'
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert([
                'name' => $brand,
                'slug' => Str::slug($brand),
                'logo' => 'logos/' . Str::slug($brand) . '.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Categories
        $categories = [
            [
                'name' => 'CP',
                'sub' => [
                    'Insecticides',
                    'Fungicides',
                    'Herbicides',
                    'Pesticides',
                    'Bio Pesticides',
                    'Plant Growth Regulators',
                    'Rodenticides',
                    'Nematicides'
                ]
            ],
            [
                'name' => 'CN',
                'sub' => [
                    'Fertilizers',
                    'Organic Fertilizers',
                    'Bio Fertilizers',
                    'Micronutrients',
                    'Water Soluble Fertilizers',
                    'Granular Fertilizers',
                    'Liquid Fertilizers',
                    'Soil Conditioners'
                ]
            ],
            [
                'name' => 'Hardware',
                'sub' => [
                    'Sprayers',
                    'Pumps',
                    'Irrigation Equipment',
                    'Pipes & Fittings',
                    'Drip Accessories',
                    'Garden Tools',
                    'Agricultural Tools',
                    'Shade Nets'
                ]
            ],
            [
                'name' => 'Seed',
                'sub' => [
                    'Field Crop Seeds',
                    'Vegetable Seeds',
                    'Fruit Seeds',
                    'Hybrid Seeds',
                    'Organic Seeds',
                    'Flower Seeds',
                    'Fodder Seeds',
                    'Oilseed Seeds'
                ]
            ],
        ];

        foreach ($categories as $cat) {
            $parentId = DB::table('categories')->insertGetId([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'parent_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cat['sub'] as $sub) {
                DB::table('categories')->insert([
                    'name' => $sub,
                    'slug' => Str::slug($sub),
                    'parent_id' => $parentId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Tax Rates
        $taxRates = [
            ['name' => 'GST 5%', 'rate' => 5.00],
            ['name' => 'GST 12%', 'rate' => 12.00],
            ['name' => 'GST 18%', 'rate' => 18.00],
            ['name' => 'GST 28%', 'rate' => 28.00],
            ['name' => 'Exempt', 'rate' => 0.00],
        ];

        foreach ($taxRates as $tax) {
            DB::table('tax_rates')->insert(array_merge($tax, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // 4. HSN Codes
        $hsnCodes = [
            '3808',
            '3101',
            '3105',
            '8424',
            '8432',
            '1209'
        ];

        foreach ($hsnCodes as $code) {
            DB::table('hsn_codes')->insert([
                'code' => $code,
                'description' => 'HSN Code for ' . $code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Units of Measure
        $uoms = [
            ['name' => 'Kilogram', 'code' => 'KG', 'is_base_unit' => true],
            ['name' => 'Gram', 'code' => 'GM', 'is_base_unit' => false],
            ['name' => 'Liter', 'code' => 'LTR', 'is_base_unit' => true],
            ['name' => 'Milliliter', 'code' => 'ML', 'is_base_unit' => false],
            ['name' => 'Piece', 'code' => 'PCS', 'is_base_unit' => true],
            ['name' => 'Packet', 'code' => 'PKT', 'is_base_unit' => false],
            ['name' => 'Bag', 'code' => 'BAG', 'is_base_unit' => false],
        ];

        foreach ($uoms as $uom) {
            DB::table('units_of_measure')->insert(array_merge($uom, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // 6. UOM Conversions
        $kgId = DB::table('units_of_measure')->where('code', 'KG')->first()->id;
        $gmId = DB::table('units_of_measure')->where('code', 'GM')->first()->id;

        DB::table('uom_conversions')->insert([
            'from_uom_id' => $kgId,
            'to_uom_id' => $gmId,
            'conversion_factor' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ltrId = DB::table('units_of_measure')->where('code', 'LTR')->first()->id;
        $mlId = DB::table('units_of_measure')->where('code', 'ML')->first()->id;

        DB::table('uom_conversions')->insert([
            'from_uom_id' => $ltrId,
            'to_uom_id' => $mlId,
            'conversion_factor' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Crops & Agricultural
        $crops = [
            'Wheat',
            'Rice',
            'Cotton',
            'Sugarcane',
            'Soybean',
            'Groundnut',
            'Maize',
            'Bajra'
        ];

        foreach ($crops as $crop) {
            DB::table('crops')->insert([
                'name' => $crop,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $irrigations = [
            'Drip',
            'Sprinkler',
            'Flood',
            'Canal',
            'Borewell'
        ];

        foreach ($irrigations as $irr) {
            DB::table('irrigation_types')->insert([
                'name' => $irr,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $landUnits = [
            ['name' => 'Acre', 'conversion_to_sq_mt' => 4046.86],
            ['name' => 'Hectare', 'conversion_to_sq_mt' => 10000.00],
            ['name' => 'Bigha', 'conversion_to_sq_mt' => 2500.00],
            ['name' => 'Guntha', 'conversion_to_sq_mt' => 101.17],
        ];

        foreach ($landUnits as $unit) {
            DB::table('land_units')->insert(array_merge($unit, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // 8. Account Types
        $accTypes = [
            'Cash',
            'Bank',
            'Sales',
            'Purchase',
            'Inventory',
            'Expense',
            'Customer',
            'Supplier'
        ];

        foreach ($accTypes as $acc) {
            DB::table('account_types')->insert([
                'name' => $acc,
                'slug' => Str::slug($acc),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}