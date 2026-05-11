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
        // 1. Agriculture Brands
        $brands = [
            ['name' => 'Bayer Crop Science', 'website' => 'https://www.bayer.com'],
            ['name' => 'Syngenta', 'website' => 'https://www.syngenta.com'],
            ['name' => 'BASF', 'website' => 'https://www.basf.com'],
            ['name' => 'UPL Limited', 'website' => 'https://www.upl-ltd.com'],
            ['name' => 'IFFCO', 'website' => 'https://www.iffco.in'],
            ['name' => 'Mahindra Agri', 'website' => 'https://www.mahindraagri.com'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['name' => $brand['name']], [
                'slug' => Str::slug($brand['name']),
                'website' => $brand['website'],
                'status' => 'active',
                'is_active' => true
            ]);
        }

        // 2. Agriculture Categories & Subcategories
        $categories = [
            'Seeds' => ['Vegetable Seeds', 'Fruit Seeds', 'Cereal Seeds', 'Oilseeds', 'Pulses'],
            'Fertilizers' => ['NPK Fertilizers', 'Organic Manure', 'Micronutrients', 'Water Soluble Fertilizers'],
            'Pesticides' => ['Insecticides', 'Herbicides', 'Fungicides', 'Rodenticides'],
            'Farm Equipment' => ['Hand Tools', 'Irrigation Systems', 'Spray Machines', 'Tractor Attachments'],
            'Livestock Feed' => ['Cattle Feed', 'Poultry Feed', 'Aqua Feed'],
        ];

        foreach ($categories as $parentName => $subCategories) {
            $parent = Category::updateOrCreate(['name' => $parentName], [
                'slug' => Str::slug($parentName),
                'status' => 'active',
                'is_active' => true
            ]);

            foreach ($subCategories as $subName) {
                Category::updateOrCreate(['name' => $subName, 'parent_id' => $parent->id], [
                    'slug' => Str::slug($subName),
                    'status' => 'active',
                    'is_active' => true
                ]);
            }
        }

        // 3. Agriculture Attributes
        $attributes = [
            ['name' => 'Pack Size', 'type' => 'select'],
            ['name' => 'Crop Type', 'type' => 'select'],
            ['name' => 'Season', 'type' => 'select'],
            ['name' => 'NPK Ratio', 'type' => 'text'],
        ];

        foreach ($attributes as $attr) {
            $attribute = ProductAttribute::updateOrCreate(['name' => $attr['name']], [
                'type' => $attr['type'],
                'status' => 'active',
                'is_filterable' => true
            ]);

            // Add values based on attribute
            if ($attr['name'] === 'Pack Size') {
                $values = ['100g', '250g', '500g', '1kg', '5kg', '10kg', '25kg', '100ml', '250ml', '500ml', '1L', '5L'];
                foreach ($values as $val) {
                    ProductAttributeValue::updateOrCreate([
                        'attribute_id' => $attribute->id,
                        'value' => $val
                    ], ['status' => 'active']);
                }
            } elseif ($attr['name'] === 'Crop Type') {
                $values = ['Cotton', 'Rice/Paddy', 'Wheat', 'Maize', 'Tomato', 'Chilli', 'Onion', 'Potato', 'Sugarcane'];
                foreach ($values as $val) {
                    ProductAttributeValue::updateOrCreate([
                        'attribute_id' => $attribute->id,
                        'value' => $val
                    ], ['status' => 'active']);
                }
            } elseif ($attr['name'] === 'Season') {
                $values = ['Kharif', 'Rabi', 'Zaid', 'All Season'];
                foreach ($values as $val) {
                    ProductAttributeValue::updateOrCreate([
                        'attribute_id' => $attribute->id,
                        'value' => $val
                    ], ['status' => 'active']);
                }
            }
        }

        // 4. Tax Rates (Typical for Agri in India)
        $taxRates = [
            ['name' => 'GST Exempt', 'rate' => 0],
            ['name' => 'GST Agri 5%', 'rate' => 5],
            ['name' => 'GST Agri 12%', 'rate' => 12],
            ['name' => 'GST Agri 18%', 'rate' => 18],
        ];

        foreach ($taxRates as $rate) {
            TaxRate::updateOrCreate(['name' => $rate['name']], [
                'rate' => $rate['rate'],
                'status' => 'active'
            ]);
        }

        // 5. HSN Codes
        $hsnCodes = [
            ['code' => '1209', 'description' => 'Seeds, fruit and spores, of a kind used for sowing'],
            ['code' => '3101', 'description' => 'Animal or vegetable fertilisers'],
            ['code' => '3808', 'description' => 'Insecticides, rodenticides, fungicides, herbicides'],
            ['code' => '8424', 'description' => 'Mechanical appliances for projecting, dispersing or spraying liquids'],
        ];

        foreach ($hsnCodes as $hsn) {
            HsnCode::updateOrCreate(['code' => $hsn['code']], [
                'description' => $hsn['description'],
                'status' => 'active'
            ]);
        }
    }
}
