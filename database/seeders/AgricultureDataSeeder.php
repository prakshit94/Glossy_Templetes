<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\TaxRate;
use App\Models\HsnCode;
use Illuminate\Support\Str;

class AgricultureDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Agriculture Brands (additional – MasterDataSeeder covers core ones) ──
        $brands = [
            'Bayer Crop Science',
            'BASF India',
            'UPL Limited',
            'Mahindra Agri Solutions',
            'Sumitomo Chemical India',
            'FMC India',
            'Dhanuka Agritech',
            'Excel Crop Care',
        ];

        foreach ($brands as $brandName) {
            Brand::updateOrCreate(
                ['name' => $brandName],
                [
                    'slug'      => Str::slug($brandName),
                    'status'    => 'active',
                    'is_active' => true,
                ]
            );
        }

        // ─── 2. Agriculture Categories & Subcategories ────────────────────────
        $categories = [
            'Seeds'         => ['Vegetable Seeds', 'Fruit Seeds', 'Cereal Seeds', 'Oilseeds', 'Pulses'],
            'Fertilizers'   => ['NPK Fertilizers', 'Organic Manure', 'Micronutrients', 'Water Soluble Fertilizers'],
            'Pesticides'    => ['Insecticides', 'Herbicides', 'Fungicides', 'Rodenticides'],
            'Farm Equipment'=> ['Hand Tools', 'Irrigation Systems', 'Spray Machines', 'Tractor Attachments'],
            'Livestock Feed'=> ['Cattle Feed', 'Poultry Feed', 'Aqua Feed'],
        ];

        foreach ($categories as $parentName => $subCategories) {
            // Use slug as the unique match to avoid conflicts with MasterDataSeeder parents
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name'      => $parentName,
                    'parent_id' => null,
                    'status'    => 'active',
                    'is_active' => true,
                ]
            );

            foreach ($subCategories as $subName) {
                // Use slug as unique key — prevents duplicate slug violation if already
                // inserted by MasterDataSeeder under a different parent
                Category::updateOrCreate(
                    ['slug' => Str::slug($subName)],
                    [
                        'name'      => $subName,
                        'parent_id' => $parent->id,
                        'status'    => 'active',
                        'is_active' => true,
                    ]
                );
            }
        }

        // ─── 3. Agriculture Product Attributes ───────────────────────────────
        $attributes = [
            ['name' => 'Pack Size',  'type' => 'select'],
            ['name' => 'Crop Type',  'type' => 'select'],
            ['name' => 'Season',     'type' => 'select'],
            ['name' => 'NPK Ratio',  'type' => 'text'],
        ];

        foreach ($attributes as $attr) {
            $attribute = ProductAttribute::updateOrCreate(
                ['name' => $attr['name']],
                [
                    'type'          => $attr['type'],
                    'status'        => 'active',
                    'is_filterable' => true,
                ]
            );

            if ($attr['name'] === 'Pack Size') {
                $values = ['100g', '250g', '500g', '1kg', '5kg', '10kg', '25kg', '100ml', '250ml', '500ml', '1L', '5L'];
            } elseif ($attr['name'] === 'Crop Type') {
                $values = ['Cotton', 'Rice/Paddy', 'Wheat', 'Maize', 'Tomato', 'Chilli', 'Onion', 'Potato', 'Sugarcane', 'Soybean', 'Groundnut'];
            } elseif ($attr['name'] === 'Season') {
                $values = ['Kharif', 'Rabi', 'Zaid', 'All Season'];
            } else {
                $values = [];
            }

            foreach ($values as $val) {
                ProductAttributeValue::updateOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $val],
                    ['status' => 'active']
                );
            }
        }

        // ─── 4. Tax Rates for Agriculture ────────────────────────────────────
        $taxRates = [
            ['name' => 'GST Exempt',    'rate' => 0],
            ['name' => 'GST Agri 5%',   'rate' => 5],
            ['name' => 'GST Agri 12%',  'rate' => 12],
            ['name' => 'GST Agri 18%',  'rate' => 18],
        ];

        foreach ($taxRates as $rate) {
            TaxRate::updateOrCreate(
                ['name' => $rate['name']],
                ['rate' => $rate['rate'], 'status' => 'active']
            );
        }

        // ─── 5. HSN Codes ─────────────────────────────────────────────────────
        $hsnCodes = [
            ['code' => '1209', 'description' => 'Seeds, fruit and spores of a kind used for sowing'],
            ['code' => '3101', 'description' => 'Animal or vegetable fertilisers; manure'],
            ['code' => '3105', 'description' => 'Mineral or chemical fertilisers containing nitrogen, phosphorus, potassium'],
            ['code' => '3808', 'description' => 'Insecticides, rodenticides, fungicides, herbicides, plant growth regulators'],
            ['code' => '8424', 'description' => 'Mechanical appliances for projecting, dispersing or spraying liquids (sprayers)'],
            ['code' => '8432', 'description' => 'Agricultural, horticultural or forestry machinery for soil preparation or cultivation'],
        ];

        foreach ($hsnCodes as $hsn) {
            HsnCode::updateOrCreate(
                ['code' => $hsn['code']],
                ['description' => $hsn['description'], 'status' => 'active']
            );
        }

        $this->command->info('✅ AgricultureDataSeeder: Brands, categories, attributes, tax rates & HSN codes seeded.');
    }
}
