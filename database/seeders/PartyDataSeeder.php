<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Customer Groups
        $groups = [
            ['name' => 'General', 'discount_percentage' => 0.00],
            ['name' => 'VIP', 'discount_percentage' => 10.00],
            ['name' => 'Wholesale', 'discount_percentage' => 20.00],
        ];
        foreach ($groups as $group) {
            DB::table('customer_groups')->insert(array_merge($group, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 2. Parties (Customers & Suppliers)
        $types = ['customer', 'supplier'];
        $accId = DB::table('account_types')->first()->id;
        $villId = DB::table('villages')->first()->id;

        foreach ($types as $type) {
            for ($i = 1; $i <= 10; $i++) {
                $name = ucfirst($type) . " $i";
                $partyId = DB::table('parties')->insertGetId([
                    'name' => $name,
                    'type' => $type,
                    'email' => strtolower($type) . "$i@example.com",
                    'phone' => '98765432' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'gst_no' => '27AAAAA0000A1Z' . $i % 9,
                    'pan_no' => 'ABCDE000' . str_pad($i, 2, '0', STR_PAD_LEFT) . 'F',
                    'credit_limit' => $type == 'customer' ? 50000 : 0,
                    'credit_days' => $type == 'customer' ? 30 : 0,
                    'is_active' => true,
                    'account_type_id' => $accId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Addresses
                DB::table('party_addresses')->insert([
                    'party_id' => $partyId,
                    'label' => 'Primary',
                    'address_line_1' => "Address line 1 for $name",
                    'address_line_2' => "Address line 2",
                    'village_id' => $villId,
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'pincode' => '400001',
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
