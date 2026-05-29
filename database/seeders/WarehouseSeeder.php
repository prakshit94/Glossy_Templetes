<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name'         => 'Head Office Warehouse',
                'code'         => 'WH-HO-001',
                'company_name' => 'Flipkart Pvt. Ltd.',
                'gstin'        => '27AABCA1234A1Z5',
                'phone'        => '9876543210',
                'address'      => 'Plot No. 12, MIDC Industrial Area, Pune',
                'address_line_1' => 'Plot No. 12, MIDC Industrial Area',
                'address_line_2' => 'Near Talegaon Chowk',
                'city'         => 'Pune',
                'state'        => 'Maharashtra',
                'pincode'      => '410507',
                'is_default'   => true,
                'is_active'    => true,
                'status'       => 'active',
            ],
            [
                'name'         => 'North Zone Distribution Center',
                'code'         => 'WH-NZ-002',
                'company_name' => 'Flipkart Pvt. Ltd.',
                'gstin'        => '07AABCA1234A1Z2',
                'phone'        => '9876543211',
                'address'      => 'Sector 63, Noida, Uttar Pradesh',
                'address_line_1' => 'Sector 63, Industrial Area',
                'address_line_2' => 'Near Metro Station Gate 5',
                'city'         => 'Noida',
                'state'        => 'Uttar Pradesh',
                'pincode'      => '201307',
                'is_default'   => false,
                'is_active'    => true,
                'status'       => 'active',
            ],
            [
                'name'         => 'South Zone Distribution Center',
                'code'         => 'WH-SZ-003',
                'company_name' => 'Flipkart Pvt. Ltd.',
                'gstin'        => '29AABCA1234A1Z1',
                'phone'        => '9876543212',
                'address'      => 'Electronic City Phase 2, Bengaluru, Karnataka',
                'address_line_1' => 'Electronic City Phase 2',
                'address_line_2' => 'Hosur Road',
                'city'         => 'Bengaluru',
                'state'        => 'Karnataka',
                'pincode'      => '560100',
                'is_default'   => false,
                'is_active'    => true,
                'status'       => 'active',
            ],
            [
                'name'         => 'West Zone Distribution Center',
                'code'         => 'WH-WZ-004',
                'company_name' => 'Flipkart Pvt. Ltd.',
                'gstin'        => '24AABCA1234A1Z3',
                'phone'        => '9876543213',
                'address'      => 'GIDC Vatva, Ahmedabad, Gujarat',
                'address_line_1' => 'GIDC Vatva Industrial Estate',
                'address_line_2' => 'Opp. Vatva Railway Station',
                'city'         => 'Ahmedabad',
                'state'        => 'Gujarat',
                'pincode'      => '382445',
                'is_default'   => false,
                'is_active'    => true,
                'status'       => 'active',
            ],
            [
                'name'         => 'East Zone Distribution Center',
                'code'         => 'WH-EZ-005',
                'company_name' => 'Flipkart Pvt. Ltd.',
                'gstin'        => '19AABCA1234A1Z6',
                'phone'        => '9876543214',
                'address'      => 'Dankuni Industrial Complex, Hooghly, West Bengal',
                'address_line_1' => 'Dankuni Industrial Complex',
                'address_line_2' => 'GT Road, Dankuni',
                'city'         => 'Hooghly',
                'state'        => 'West Bengal',
                'pincode'      => '712311',
                'is_default'   => false,
                'is_active'    => true,
                'status'       => 'active',
            ],
        ];

        foreach ($warehouses as $wh) {
            DB::table('warehouses')->updateOrInsert(
                ['code' => $wh['code']],
                array_merge($wh, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('✅ WarehouseSeeder: 5 warehouses seeded.');
    }
}
