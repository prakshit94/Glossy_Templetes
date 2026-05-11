<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Warehouses
        $warehouses = [
            ['name' => 'Main Warehouse', 'code' => 'WH-MAIN', 'is_default' => true],
            ['name' => 'North Distribution Center', 'code' => 'WH-NORTH', 'is_default' => false],
            ['name' => 'South Distribution Center', 'code' => 'WH-SOUTH', 'is_default' => false],
        ];
        foreach ($warehouses as $wh) {
            DB::table('warehouses')->insert(array_merge($wh, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 2. Attributes
        $attributes = [
            ['name' => 'Color', 'type' => 'color'],
            ['name' => 'Size', 'type' => 'select'],
        ];
        foreach ($attributes as $attr) {
            $attrId = DB::table('product_attributes')->insertGetId(array_merge($attr, ['created_at' => now(), 'updated_at' => now()]));
            
            if ($attr['name'] == 'Color') {
                $values = [
                    ['value' => 'Red', 'color_code' => '#FF0000'],
                    ['value' => 'Blue', 'color_code' => '#0000FF'],
                    ['value' => 'Black', 'color_code' => '#000000'],
                ];
            } else {
                $values = [
                    ['value' => 'S', 'color_code' => null],
                    ['value' => 'M', 'color_code' => null],
                    ['value' => 'L', 'color_code' => null],
                    ['value' => 'XL', 'color_code' => null],
                ];
            }

            foreach ($values as $val) {
                DB::table('product_attribute_values')->insert(array_merge($val, [
                    'attribute_id' => $attrId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }

        // 3. Products
        $brandId = DB::table('brands')->first()->id;
        $catId = DB::table('categories')->whereNotNull('parent_id')->first()->id;
        $taxId = DB::table('tax_rates')->first()->id;
        $hsnId = DB::table('hsn_codes')->first()->id;
        $uomId = DB::table('units_of_measure')->first()->id;
        $whId = DB::table('warehouses')->first()->id;

        for ($i = 1; $i <= 20; $i++) {
            $name = "Premium Product $i";
            $sku = "SKU-" . Str::random(8);
            $productId = DB::table('products')->insertGetId([
                'name' => $name,
                'sku' => $sku,
                'slug' => Str::slug($name) . '-' . $i,
                'brand_id' => $brandId,
                'category_id' => $catId,
                'tax_rate_id' => $taxId,
                'hsn_code_id' => $hsnId,
                'uom_id' => $uomId,
                'barcode' => 'BC-' . rand(1000000, 9999999),
                'purchase_price' => rand(100, 500),
                'mrp' => rand(600, 1000),
                'selling_price' => rand(550, 950),
                'min_stock_level' => 10,
                'batch_tracking' => true,
                'expiry_tracking' => true,
                'is_active' => true,
                'description' => "Detailed description for product $i",
                'default_warehouse_id' => $whId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Variants
            for ($j = 1; $j <= 2; $j++) {
                $vSku = $sku . "-V$j";
                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'sku' => $vSku,
                    'name' => "Variant $j for $name",
                    'attribute_values' => json_encode(['Color' => 'Red', 'Size' => 'L']),
                    'additional_price' => rand(0, 50),
                    'barcode' => 'BCV-' . rand(1000000, 9999999),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Advanced Pricing
                DB::table('product_prices')->insert([
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'label' => 'Wholesale',
                    'min_qty' => 10,
                    'price' => rand(400, 500),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Images
            DB::table('product_images')->insert([
                'product_id' => $productId,
                'image_path' => "products/p$i.jpg",
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Barcodes
            DB::table('product_barcodes')->insert([
                'product_id' => $productId,
                'barcode' => 'ALT-BC-' . rand(1000000, 9999999),
                'type' => 'EAN-13',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
