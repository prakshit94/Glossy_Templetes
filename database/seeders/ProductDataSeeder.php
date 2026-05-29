<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Fetch required FK IDs ───────────────────────────────────────────────
        $brandMap    = DB::table('brands')->pluck('id', 'name');
        $catMap      = DB::table('categories')->whereNotNull('parent_id')->pluck('id', 'name');
        $taxMap      = DB::table('tax_rates')->pluck('id', 'name');
        $hsnMap      = DB::table('hsn_codes')->pluck('id', 'code');
        $uomMap      = DB::table('units_of_measure')->pluck('id', 'code');
        $warehouseId = DB::table('warehouses')->where('is_default', true)->value('id')
                     ?? DB::table('warehouses')->value('id');

        // ─── Attributes ──────────────────────────────────────────────────────────
        $attrPackId = DB::table('product_attributes')
            ->where('name', 'Pack Size')->value('id');
        $attrCropId = DB::table('product_attributes')
            ->where('name', 'Crop Type')->value('id');

        // ─── Real Agricultural Products ─────────────────────────────────────────
        $products = [
            // ── Insecticides ──────────────────────────────────────────────────
            [
                'name'         => 'Confidor 200 SL - Imidacloprid Insecticide',
                'sku'          => 'UPL-CON-200SL',
                'brand'        => 'UPL',
                'category'     => 'Insecticides',
                'tax'          => 'GST 18%',
                'hsn'          => '3808',
                'uom'          => 'LTR',
                'barcode'      => '8901234567001',
                'purchase_price' => 520.00,
                'mrp'          => 750.00,
                'selling_price'=> 680.00,
                'weight'       => '1 Liter',
                'description'  => 'Confidor 200 SL is a systemic insecticide with active ingredient Imidacloprid 200 g/L SL. Highly effective against sucking pests like aphids, jassids, whitefly, and thrips on cotton, paddy, wheat, and vegetables. Apply 0.5 ml/L water as foliar spray.',
                'application_instructions' => 'Mix 1 ml in 2 liters of water and spray on foliage in early morning or evening. Repeat after 15 days if infestation persists.',
                'pack_sizes'   => ['250ml', '500ml', '1L'],
                'crop_types'   => ['Cotton', 'Rice/Paddy', 'Wheat'],
                'min_stock'    => 20,
            ],
            [
                'name'         => 'Coragen 20 SC - Chlorantraniliprole Insecticide',
                'sku'          => 'DPT-COR-20SC',
                'brand'        => 'Syngenta',
                'category'     => 'Insecticides',
                'tax'          => 'GST 18%',
                'hsn'          => '3808',
                'uom'          => 'ML',
                'barcode'      => '8901234567002',
                'purchase_price' => 1800.00,
                'mrp'          => 2600.00,
                'selling_price'=> 2350.00,
                'weight'       => '150 ml',
                'description'  => 'Coragen 20 SC (Chlorantraniliprole 18.5% w/w SC) is a next-generation diamide insecticide effective against lepidopteran pests. Controls stem borer, pink borer, and pod borer in rice, cotton, and vegetables. Long residual activity up to 21 days.',
                'application_instructions' => 'Use 0.4 ml/L water for foliar spray. Apply at first sign of pest infestation. PHI: 7 days before harvest.',
                'pack_sizes'   => ['30ml', '60ml', '150ml'],
                'crop_types'   => ['Cotton', 'Rice/Paddy', 'Maize'],
                'min_stock'    => 15,
            ],
            [
                'name'         => 'Rogor 30 EC - Dimethoate Insecticide',
                'sku'          => 'BAYER-ROG-30EC',
                'brand'        => 'Bayer',
                'category'     => 'Insecticides',
                'tax'          => 'GST 18%',
                'hsn'          => '3808',
                'uom'          => 'LTR',
                'barcode'      => '8901234567003',
                'purchase_price' => 280.00,
                'mrp'          => 420.00,
                'selling_price'=> 380.00,
                'weight'       => '1 Liter',
                'description'  => 'Rogor 30 EC contains Dimethoate 30% EC. Systemic and contact insecticide effective against aphids, mites, mealybugs, and jassids. Suitable for use on a wide range of crops including wheat, mustard, and vegetables.',
                'application_instructions' => 'Mix 2 ml per liter of water and spray uniformly. Avoid spray during flowering stage to protect pollinators.',
                'pack_sizes'   => ['250ml', '500ml', '1L'],
                'crop_types'   => ['Wheat', 'Maize', 'Chilli'],
                'min_stock'    => 25,
            ],
            // ── Fungicides ────────────────────────────────────────────────────
            [
                'name'         => 'Amistar Top 325 SC - Azoxystrobin & Difenoconazole',
                'sku'          => 'SYN-AMT-325SC',
                'brand'        => 'Syngenta',
                'category'     => 'Fungicides',
                'tax'          => 'GST 18%',
                'hsn'          => '3808',
                'uom'          => 'ML',
                'barcode'      => '8901234567004',
                'purchase_price' => 480.00,
                'mrp'          => 720.00,
                'selling_price'=> 650.00,
                'weight'       => '200 ml',
                'description'  => 'Amistar Top 325 SC is a broad-spectrum fungicide combining Azoxystrobin 18.2% + Difenoconazole 11.4% SC. Controls blast, sheath blight, brown spot in paddy; powdery mildew and rust in wheat; and early/late blight in tomato.',
                'application_instructions' => 'Mix 1 ml per liter of water. Spray at first appearance of disease symptoms. Repeat at 10-14 day intervals. Maximum 3 sprays per crop season.',
                'pack_sizes'   => ['100ml', '200ml', '500ml'],
                'crop_types'   => ['Rice/Paddy', 'Wheat', 'Tomato'],
                'min_stock'    => 18,
            ],
            [
                'name'         => 'Cabrio Top 60 WG - Pyraclostrobin & Metiram',
                'sku'          => 'BASF-CAB-60WG',
                'brand'        => 'Bayer',
                'category'     => 'Fungicides',
                'tax'          => 'GST 18%',
                'hsn'          => '3808',
                'uom'          => 'GM',
                'barcode'      => '8901234567005',
                'purchase_price' => 320.00,
                'mrp'          => 490.00,
                'selling_price'=> 440.00,
                'weight'       => '200 g',
                'description'  => 'Cabrio Top 60 WG contains Pyraclostrobin 5% + Metiram 55% WG. Effective against a wide spectrum of fungal diseases including downy mildew, powdery mildew, and leaf spots in grapes, potato, and vegetables.',
                'application_instructions' => 'Dissolve 2-3 g per liter of water. Apply as preventive spray before disease appearance. PHI: 14 days.',
                'pack_sizes'   => ['100g', '250g', '500g'],
                'crop_types'   => ['Potato', 'Onion', 'Chilli'],
                'min_stock'    => 20,
            ],
            // ── Herbicides ────────────────────────────────────────────────────
            [
                'name'         => 'Targa Super 5 EC - Quizalofop-p-ethyl Herbicide',
                'sku'          => 'UPL-TAR-5EC',
                'brand'        => 'UPL',
                'category'     => 'Herbicides',
                'tax'          => 'GST 18%',
                'hsn'          => '3808',
                'uom'          => 'LTR',
                'barcode'      => '8901234567006',
                'purchase_price' => 600.00,
                'mrp'          => 850.00,
                'selling_price'=> 780.00,
                'weight'       => '1 Liter',
                'description'  => 'Targa Super 5 EC contains Quizalofop-p-ethyl 5% EC. Post-emergence selective herbicide for control of narrow-leaf grassy weeds in soybean, groundnut, and broad-leaf crops. Translocates to growing points of weeds for complete kill.',
                'application_instructions' => 'Apply 1.5 L/ha in 500 L water when weeds are 3-5 leaf stage. Do not use on cereal crops.',
                'pack_sizes'   => ['250ml', '500ml', '1L'],
                'crop_types'   => ['Sugarcane', 'Cotton', 'Maize'],
                'min_stock'    => 15,
            ],
            // ── Fertilizers ──────────────────────────────────────────────────
            [
                'name'         => 'IFFCO NPK 20:20:00 Water Soluble Fertilizer',
                'sku'          => 'IFFCO-NPK-2020',
                'brand'        => 'IFFCO',
                'category'     => 'Water Soluble Fertilizers',
                'tax'          => 'GST 5%',
                'hsn'          => '3105',
                'uom'          => 'KG',
                'barcode'      => '8901234567007',
                'purchase_price' => 920.00,
                'mrp'          => 1200.00,
                'selling_price'=> 1100.00,
                'weight'       => '5 Kg',
                'description'  => 'IFFCO NPK 20:20:00 is a 100% water-soluble fertilizer for drip and foliar application. Contains Nitrogen 20% and Phosphorus 20%. Promotes vigorous root development and early vegetative growth. Suitable for all crops during initial growth stages.',
                'application_instructions' => 'Dissolve 2-3 kg per 200 L water for drip application. For foliar spray, use 5-10 g per liter of water.',
                'pack_sizes'   => ['1kg', '5kg', '25kg'],
                'crop_types'   => ['Cotton', 'Sugarcane', 'Tomato'],
                'min_stock'    => 50,
            ],
            [
                'name'         => 'Coromandel Ferticoat 13:00:45 Water Soluble Fertilizer',
                'sku'          => 'COR-FER-1345',
                'brand'        => 'Coromandel',
                'category'     => 'Water Soluble Fertilizers',
                'tax'          => 'GST 5%',
                'hsn'          => '3105',
                'uom'          => 'KG',
                'barcode'      => '8901234567008',
                'purchase_price' => 1100.00,
                'mrp'          => 1500.00,
                'selling_price'=> 1350.00,
                'weight'       => '5 Kg',
                'description'  => 'Ferticoat 13:00:45 (Potassium Nitrate) is a premium water-soluble fertilizer with high Potassium content for fruit quality improvement. Enhances color, size, sugar content, and shelf life of fruits and vegetables. Ideal during fruiting and maturation stage.',
                'application_instructions' => 'Apply 3-5 kg/acre via drip at 10-14 day intervals during fruiting stage. For foliar spray use 5 g/L water.',
                'pack_sizes'   => ['1kg', '5kg', '25kg'],
                'crop_types'   => ['Cotton', 'Tomato', 'Chilli'],
                'min_stock'    => 40,
            ],
            [
                'name'         => 'IFFCO Nano Urea Liquid Fertilizer',
                'sku'          => 'IFFCO-NANO-UREA',
                'brand'        => 'IFFCO',
                'category'     => 'Liquid Fertilizers',
                'tax'          => 'GST 5%',
                'hsn'          => '3101',
                'uom'          => 'ML',
                'barcode'      => '8901234567009',
                'purchase_price' => 200.00,
                'mrp'          => 275.00,
                'selling_price'=> 240.00,
                'weight'       => '500 ml',
                'description'  => 'IFFCO Nano Urea is a revolutionary liquid fertilizer containing nano-scale urea particles (40,000 ppm Nitrogen). One bottle of 500 ml replaces one 45 kg bag of conventional urea. Reduces nitrogen loss, improves nitrogen-use efficiency, and boosts crop yield by 8%.',
                'application_instructions' => 'Mix 2-4 ml per liter of water for foliar spray. Apply 2 sprays – first at active vegetative stage, second at pre-flowering stage.',
                'pack_sizes'   => ['500ml', '1L'],
                'crop_types'   => ['Wheat', 'Rice/Paddy', 'Maize'],
                'min_stock'    => 100,
            ],
            // ── Seeds ─────────────────────────────────────────────────────────
            [
                'name'         => 'Mahyco MRC-7918 BG-II Cotton Hybrid Seed',
                'sku'          => 'MHC-COT-7918',
                'brand'        => 'Mahyco',
                'category'     => 'Hybrid Seeds',
                'tax'          => 'GST 5%',
                'hsn'          => '1209',
                'uom'          => 'PKT',
                'barcode'      => '8901234567010',
                'purchase_price' => 700.00,
                'mrp'          => 930.00,
                'selling_price'=> 850.00,
                'weight'       => '450 g',
                'description'  => 'Mahyco MRC 7918 Bt Cotton Hybrid (BG-II) – 450g packet covering 1 acre. Medium-tall plant with robust boll setting. Resistant to Bollworm pests (Helicoverpa, Spodoptera). Suitable for irrigated and rain-fed conditions. Matures in 160-175 days. Average yield: 25-30 quintals/acre.',
                'application_instructions' => 'Sow one packet per acre at spacing of 90 x 60 cm. Treat with thiram fungicide before sowing. Optimum sowing time: June-July.',
                'pack_sizes'   => ['450g'],
                'crop_types'   => ['Cotton'],
                'min_stock'    => 200,
            ],
            [
                'name'         => 'Nuziveedu VNCL-112 Paddy Hybrid Seed',
                'sku'          => 'NUZ-PAD-VNCL112',
                'brand'        => 'Nuziveedu Seeds',
                'category'     => 'Field Crop Seeds',
                'tax'          => 'GST 5%',
                'hsn'          => '1209',
                'uom'          => 'KG',
                'barcode'      => '8901234567011',
                'purchase_price' => 380.00,
                'mrp'          => 520.00,
                'selling_price'=> 470.00,
                'weight'       => '5 Kg',
                'description'  => 'Nuziveedu VNCL-112 is a high-yielding medium-duration paddy hybrid. Maturity: 120-125 days. Average yield: 65-75 quintals/hectare. Slender long grain preferred in export markets. Moderate tolerance to drought. Suitable for Kharif season in Andhra Pradesh, Telangana, and Karnataka.',
                'application_instructions' => 'Seed rate: 15-20 kg/ha for transplanting. Nursery sowing: 25-30 days before transplanting. Maintain 2-3 cm water level after transplanting.',
                'pack_sizes'   => ['5kg', '10kg'],
                'crop_types'   => ['Rice/Paddy'],
                'min_stock'    => 150,
            ],
            // ── Hardware / Equipment ──────────────────────────────────────────
            [
                'name'         => 'Neptune Battery-Operated Knapsack Sprayer 16L',
                'sku'          => 'HW-SPR-NEPT16L',
                'brand'        => 'UPL',
                'category'     => 'Sprayers',
                'tax'          => 'GST 18%',
                'hsn'          => '8424',
                'uom'          => 'PCS',
                'barcode'      => '8901234567012',
                'purchase_price' => 2800.00,
                'mrp'          => 3999.00,
                'selling_price'=> 3500.00,
                'weight'       => '4.5 Kg',
                'description'  => 'Neptune 16-Liter Battery-Operated Knapsack Sprayer with 12V lithium battery. Flow rate: 1.2 L/min. Pressure: 2.0-3.0 bar. Includes flat fan nozzle, hollow cone nozzle, and extension pipe. Continuous spray for 6-8 hours on single charge. Ergonomic padded shoulder straps for comfort.',
                'application_instructions' => 'Fill tank up to 14L maximum. Charge battery fully before first use. Clean after every use with clean water.',
                'pack_sizes'   => ['1 Unit'],
                'crop_types'   => ['Cotton', 'Rice/Paddy', 'Wheat'],
                'min_stock'    => 10,
            ],
            // ── Organic / Bio Products ────────────────────────────────────────
            [
                'name'         => 'Multiplex Bio-Jodi Trichoderma + Pseudomonas Bio-fungicide',
                'sku'          => 'RAL-BIO-JODI',
                'brand'        => 'Rallis India',
                'category'     => 'Bio Pesticides',
                'tax'          => 'GST 5%',
                'hsn'          => '3808',
                'uom'          => 'KG',
                'barcode'      => '8901234567013',
                'purchase_price' => 180.00,
                'mrp'          => 280.00,
                'selling_price'=> 250.00,
                'weight'       => '1 Kg',
                'description'  => 'Bio-Jodi is a consortium of Trichoderma viride (1×10^6 CFU/g) and Pseudomonas fluorescens (1×10^7 CFU/ml). Controls soil-borne diseases like root rot, damping off, and wilt. Also promotes plant growth through phosphate solubilization and IAA production. Safe for humans and environment.',
                'application_instructions' => 'Seed treatment: 5g/kg seed. Soil application: 2-3 kg/acre mixed with 100 kg FYM. Drench at base of plants: 5g/L water.',
                'pack_sizes'   => ['1kg', '5kg'],
                'crop_types'   => ['Cotton', 'Sugarcane', 'Potato'],
                'min_stock'    => 60,
            ],
        ];

        foreach ($products as $item) {
            $brandId    = $brandMap[$item['brand']] ?? null;
            $catId      = $catMap[$item['category']] ?? null;
            $taxId      = $taxMap[$item['tax']] ?? null;
            $hsnId      = $hsnMap[$item['hsn']] ?? null;
            $uomId      = $uomMap[$item['uom']] ?? null;

            // Insert product
            $productId = DB::table('products')->insertGetId([
                'name'                    => $item['name'],
                'sku'                     => $item['sku'],
                'slug'                    => Str::slug($item['name']),
                'brand_id'                => $brandId,
                'category_id'             => $catId,
                'tax_rate_id'             => $taxId,
                'hsn_code_id'             => $hsnId,
                'uom_id'                  => $uomId,
                'barcode'                 => $item['barcode'],
                'weight'                  => $item['weight'],
                'purchase_price'          => $item['purchase_price'],
                'mrp'                     => $item['mrp'],
                'selling_price'           => $item['selling_price'],
                'min_stock_level'         => $item['min_stock'],
                'description'             => $item['description'],
                'application_instructions'=> $item['application_instructions'],
                'batch_tracking'          => true,
                'expiry_tracking'         => true,
                'manage_stock'            => true,
                'is_active'               => true,
                'status'                  => 'active',
                'default_warehouse_id'    => $warehouseId,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            // Product variants (one per pack size)
            foreach ($item['pack_sizes'] as $packSize) {
                $vSku = $item['sku'] . '-' . strtoupper(str_replace(['/', ' '], '-', $packSize));
                DB::table('product_variants')->insert([
                    'product_id'       => $productId,
                    'sku'              => $vSku,
                    'name'             => $item['name'] . ' - ' . $packSize,
                    'attribute_values' => json_encode(['Pack Size' => $packSize]),
                    'additional_price' => 0,
                    'barcode'          => $item['barcode'] . '-' . rand(10, 99),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            // Wholesale pricing tier
            DB::table('product_prices')->insert([
                'product_id'         => $productId,
                'product_variant_id' => null,
                'label'              => 'Wholesale',
                'min_qty'            => 10,
                'price'              => round($item['selling_price'] * 0.90, 2),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // Primary image placeholder
            DB::table('product_images')->insert([
                'product_id'  => $productId,
                'image_path'  => 'products/' . Str::slug($item['name']) . '.jpg',
                'is_primary'  => true,
                'sort_order'  => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Barcode record
            DB::table('product_barcodes')->insert([
                'product_id' => $productId,
                'barcode'    => $item['barcode'],
                'type'       => 'EAN-13',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ ProductDataSeeder: ' . count($products) . ' real-world agricultural products seeded.');
    }
}
