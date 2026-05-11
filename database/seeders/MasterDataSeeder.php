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
        $brands = ['Apple', 'Samsung', 'Sony', 'Nike', 'Adidas', 'Toyota', 'Nestle', 'Unilever'];
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
            ['name' => 'Electronics', 'sub' => ['Mobiles', 'Laptops', 'Cameras']],
            ['name' => 'Fashion', 'sub' => ['Men', 'Women', 'Kids']],
            ['name' => 'Automotive', 'sub' => ['Cars', 'Bikes', 'Trucks']],
            ['name' => 'FMCG', 'sub' => ['Food', 'Beverages', 'Home Care']],
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
            DB::table('tax_rates')->insert(array_merge($tax, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 4. HSN Codes
        $hsnCodes = ['8517', '8471', '6203', '8703', '1901', '3401'];
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
            ['name' => 'Piece', 'code' => 'PCS', 'is_base_unit' => true],
            ['name' => 'Liter', 'code' => 'LTR', 'is_base_unit' => true],
            ['name' => 'Box', 'code' => 'BOX', 'is_base_unit' => false],
        ];
        foreach ($uoms as $uom) {
            DB::table('units_of_measure')->insert(array_merge($uom, ['created_at' => now(), 'updated_at' => now()]));
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

        // 7. Crops & Agricultural
        $crops = ['Wheat', 'Rice', 'Cotton', 'Sugarcane', 'Soybean'];
        foreach ($crops as $crop) {
            DB::table('crops')->insert(['name' => $crop, 'created_at' => now(), 'updated_at' => now()]);
        }

        $irrigations = ['Drip', 'Sprinkler', 'Borewell', 'Canal'];
        foreach ($irrigations as $irr) {
            DB::table('irrigation_types')->insert(['name' => $irr, 'created_at' => now(), 'updated_at' => now()]);
        }

        $landUnits = [
            ['name' => 'Acre', 'conversion_to_sq_mt' => 4046.86],
            ['name' => 'Hectare', 'conversion_to_sq_mt' => 10000.00],
            ['name' => 'Bigha', 'conversion_to_sq_mt' => 2500.00],
        ];
        foreach ($landUnits as $unit) {
            DB::table('land_units')->insert(array_merge($unit, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 8. Account Types
        $accTypes = ['Savings', 'Current', 'Credit', 'Cash', 'Inventory'];
        foreach ($accTypes as $acc) {
            DB::table('account_types')->insert(['name' => $acc, 'slug' => Str::slug($acc), 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
