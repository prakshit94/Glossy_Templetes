<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Prerequisites ────────────────────────────────────────────────────
        $accTypeId = DB::table('account_types')->where('slug', 'customer')->value('id')
                   ?? DB::table('account_types')->first()?->id;
        $villId    = DB::table('villages')->first()?->id;

        // ─── 1. Customer Groups ───────────────────────────────────────────────
        $groups = [
            ['name' => 'General Farmer',  'discount_percentage' => 0.00],
            ['name' => 'VIP Farmer',      'discount_percentage' => 5.00],
            ['name' => 'Dealer',          'discount_percentage' => 15.00],
            ['name' => 'Wholesale',       'discount_percentage' => 20.00],
            ['name' => 'Retailer',        'discount_percentage' => 10.00],
        ];
        foreach ($groups as $group) {
            DB::table('customer_groups')->updateOrInsert(
                ['name' => $group['name']],
                array_merge($group, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ─── 2. Customers (Farmers) ───────────────────────────────────────────
        $customers = [
            [
                'firstname'    => 'Ramesh',
                'middlename'   => 'Kumar',
                'lastname'     => 'Patil',
                'phone'        => '9876501001',
                'email'        => 'ramesh.patil@example.com',
                'gst_no'       => null,
                'pan_no'       => 'BZZPA1234C',
                'credit_limit' => 25000.00,
                'credit_days'  => 30,
                'land_area'    => 5.50,
                'land_unit'    => 'acre',
                'crops'        => ['Cotton', 'Wheat'],
                'irrigation_type' => ['Drip', 'Canal'],
                'category'     => 'individual',
                'city'         => 'Akola',
                'state'        => 'Maharashtra',
                'pincode'      => '444001',
                'address'      => 'Plot 45, Near Shiv Mandir, Akola',
            ],
            [
                'firstname'    => 'Suresh',
                'middlename'   => null,
                'lastname'     => 'Sharma',
                'phone'        => '9876501002',
                'email'        => 'suresh.sharma@example.com',
                'gst_no'       => null,
                'pan_no'       => 'CZZPB5678D',
                'credit_limit' => 15000.00,
                'credit_days'  => 15,
                'land_area'    => 3.00,
                'land_unit'    => 'acre',
                'crops'        => ['Rice/Paddy', 'Maize'],
                'irrigation_type' => ['Tube Well', 'Sprinkler'],
                'category'     => 'individual',
                'city'         => 'Nagpur',
                'state'        => 'Maharashtra',
                'pincode'      => '440001',
                'address'      => 'Survey No. 88, Village Kawatha, Nagpur',
            ],
            [
                'firstname'    => 'Pratibha',
                'middlename'   => 'Ashok',
                'lastname'     => 'Deshmukh',
                'phone'        => '9876501003',
                'email'        => 'pratibha.deshmukh@example.com',
                'gst_no'       => null,
                'pan_no'       => 'DZZPC9012E',
                'credit_limit' => 50000.00,
                'credit_days'  => 45,
                'land_area'    => 12.00,
                'land_unit'    => 'acre',
                'crops'        => ['Sugarcane', 'Onion'],
                'irrigation_type' => ['Drip'],
                'category'     => 'individual',
                'city'         => 'Pune',
                'state'        => 'Maharashtra',
                'pincode'      => '411001',
                'address'      => 'Gat No. 102, Village Nhavi, Haveli, Pune',
            ],
            [
                'firstname'    => 'Mohan',
                'middlename'   => null,
                'lastname'     => 'Reddy',
                'phone'        => '9876501004',
                'email'        => 'mohan.reddy@example.com',
                'gst_no'       => '36AABCM0000A1Z5',
                'pan_no'       => 'EZZPD3456F',
                'credit_limit' => 100000.00,
                'credit_days'  => 60,
                'land_area'    => 25.00,
                'land_unit'    => 'acre',
                'crops'        => ['Cotton', 'Chilli', 'Tomato'],
                'irrigation_type' => ['Drip', 'Sprinkler'],
                'category'     => 'business',
                'company_name' => 'Reddy Agro Farms',
                'city'         => 'Hyderabad',
                'state'        => 'Telangana',
                'pincode'      => '500001',
                'address'      => 'Farm Survey 45/1, Shadnagar, Hyderabad',
            ],
            [
                'firstname'    => 'Gurpreet',
                'middlename'   => 'Singh',
                'lastname'     => 'Sandhu',
                'phone'        => '9876501005',
                'email'        => 'gurpreet.sandhu@example.com',
                'gst_no'       => null,
                'pan_no'       => 'FZZPE7890G',
                'credit_limit' => 30000.00,
                'credit_days'  => 30,
                'land_area'    => 8.00,
                'land_unit'    => 'acre',
                'crops'        => ['Wheat', 'Rice/Paddy'],
                'irrigation_type' => ['Canal', 'Tube Well'],
                'category'     => 'individual',
                'city'         => 'Ludhiana',
                'state'        => 'Punjab',
                'pincode'      => '141001',
                'address'      => 'Village Gill, Tehsil Ludhiana West, Ludhiana',
            ],
            [
                'firstname'    => 'Vijay',
                'middlename'   => null,
                'lastname'     => 'Patel',
                'phone'        => '9876501006',
                'email'        => 'vijay.patel@example.com',
                'gst_no'       => '24AABCV0000A1Z7',
                'pan_no'       => 'GZZPF2345H',
                'credit_limit' => 75000.00,
                'credit_days'  => 45,
                'land_area'    => 18.00,
                'land_unit'    => 'acre',
                'crops'        => ['Cotton', 'Groundnut', 'Castor'],
                'irrigation_type' => ['Drip', 'Sprinkler'],
                'category'     => 'business',
                'company_name' => 'Patel Agro Traders',
                'city'         => 'Surat',
                'state'        => 'Gujarat',
                'pincode'      => '395001',
                'address'      => 'GIDC Phase 2, Sachin, Surat, Gujarat',
            ],
            [
                'firstname'    => 'Anita',
                'middlename'   => 'Bai',
                'lastname'     => 'Yadav',
                'phone'        => '9876501007',
                'email'        => 'anita.yadav@example.com',
                'gst_no'       => null,
                'pan_no'       => 'HZZPG6789I',
                'credit_limit' => 10000.00,
                'credit_days'  => 15,
                'land_area'    => 2.50,
                'land_unit'    => 'acre',
                'crops'        => ['Mustard', 'Wheat'],
                'irrigation_type' => ['Rainfed'],
                'category'     => 'individual',
                'city'         => 'Jaipur',
                'state'        => 'Rajasthan',
                'pincode'      => '302001',
                'address'      => 'Village Sanganer, Tehsil Sanganer, Jaipur',
            ],
            [
                'firstname'    => 'Babu',
                'middlename'   => null,
                'lastname'     => 'Krishnan',
                'phone'        => '9876501008',
                'email'        => 'babu.krishnan@example.com',
                'gst_no'       => '32AABCB0000A1Z8',
                'pan_no'       => 'IZZPH0123J',
                'credit_limit' => 60000.00,
                'credit_days'  => 45,
                'land_area'    => 10.00,
                'land_unit'    => 'acre',
                'crops'        => ['Rice/Paddy', 'Banana', 'Coconut'],
                'irrigation_type' => ['Canal', 'Drip'],
                'category'     => 'business',
                'company_name' => 'Krishnan Plantations Pvt. Ltd.',
                'city'         => 'Kochi',
                'state'        => 'Kerala',
                'pincode'      => '682001',
                'address'      => 'Mulavukad Island, Ernakulam, Kochi, Kerala',
            ],
        ];

        foreach ($customers as $c) {
            $partyCode = 'CUST-' . strtoupper(Str::random(6));
            $partyId = DB::table('parties')->insertGetId([
                'uuid'             => Str::uuid(),
                'party_code'       => $partyCode,
                'type'             => 'customer',
                'firstname'        => $c['firstname'],
                'middlename'       => $c['middlename'] ?? null,
                'lastname'         => $c['lastname'],
                'phone'            => $c['phone'],
                'email'            => $c['email'],
                'gst_no'           => $c['gst_no'] ?? null,
                'pan_no'           => $c['pan_no'],
                'credit_limit'     => $c['credit_limit'],
                'credit_days'      => $c['credit_days'],
                'land_area'        => $c['land_area'],
                'land_unit'        => $c['land_unit'],
                'crops'            => json_encode($c['crops']),
                'irrigation_type'  => json_encode($c['irrigation_type']),
                'category'         => $c['category'],
                'company_name'     => $c['company_name'] ?? null,
                'outstanding_balance' => 0,
                'orders_count'     => 0,
                'is_active'        => true,
                'status'           => 'active',
                'account_type_id'  => $accTypeId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::table('party_addresses')->insert([
                'party_id'       => $partyId,
                'label'          => 'Primary',
                'address_line_1' => $c['address'],
                'address_line_2' => null,
                'village_id'     => $villId,
                'city'           => $c['city'],
                'state'          => $c['state'],
                'pincode'        => $c['pincode'],
                'is_default'     => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // ─── 3. Suppliers ─────────────────────────────────────────────────────
        $supplierAccTypeId = DB::table('account_types')->where('slug', 'supplier')->value('id')
                           ?? $accTypeId;

        $suppliers = [
            [
                'firstname'    => 'Rajendra',
                'lastname'     => 'Agrawal',
                'company_name' => 'UPL Limited - Authorized Distributor',
                'phone'        => '9876502001',
                'email'        => 'rajendra.agrawal@upldistrib.com',
                'gst_no'       => '27AABCU1234A1Z5',
                'pan_no'       => 'AABCU1234F',
                'credit_limit' => 500000.00,
                'credit_days'  => 60,
                'city'         => 'Mumbai',
                'state'        => 'Maharashtra',
                'pincode'      => '400001',
                'address'      => 'UPL House, 610 Maker Chambers V, Nariman Point, Mumbai',
            ],
            [
                'firstname'    => 'Sanjay',
                'lastname'     => 'Mehta',
                'company_name' => 'Syngenta India Limited',
                'phone'        => '9876502002',
                'email'        => 'sanjay.mehta@syngenta-agri.com',
                'gst_no'       => '27AABCS5678A1Z2',
                'pan_no'       => 'AABCS5678G',
                'credit_limit' => 750000.00,
                'credit_days'  => 45,
                'city'         => 'Pune',
                'state'        => 'Maharashtra',
                'pincode'      => '411001',
                'address'      => 'Syngenta India Pvt. Ltd., Survey No. 3203, Aman Court, Baner, Pune',
            ],
            [
                'firstname'    => 'Deepak',
                'lastname'     => 'Verma',
                'company_name' => 'IFFCO Kisan – Wholesale Depot',
                'phone'        => '9876502003',
                'email'        => 'deepak.verma@iffco-depot.com',
                'gst_no'       => '07AABCI9012A1Z1',
                'pan_no'       => 'AABCI9012H',
                'credit_limit' => 1000000.00,
                'credit_days'  => 30,
                'city'         => 'New Delhi',
                'state'        => 'Delhi',
                'pincode'      => '110001',
                'address'      => 'IFFCO House, 34 Nehru Place, New Delhi',
            ],
            [
                'firstname'    => 'Kishore',
                'lastname'     => 'Nair',
                'company_name' => 'Coromandel International Ltd.',
                'phone'        => '9876502004',
                'email'        => 'kishore.nair@coromandel.com',
                'gst_no'       => '36AABCC3456A1Z4',
                'pan_no'       => 'AABCC3456I',
                'credit_limit' => 600000.00,
                'credit_days'  => 45,
                'city'         => 'Hyderabad',
                'state'        => 'Telangana',
                'pincode'      => '500001',
                'address'      => 'Coromandel House, 1-2-10 Sardar Patel Road, Secunderabad',
            ],
        ];

        foreach ($suppliers as $s) {
            $partyCode = 'SUPP-' . strtoupper(Str::random(6));
            $partyId = DB::table('parties')->insertGetId([
                'uuid'             => Str::uuid(),
                'party_code'       => $partyCode,
                'type'             => 'supplier',
                'firstname'        => $s['firstname'],
                'lastname'         => $s['lastname'],
                'phone'            => $s['phone'],
                'email'            => $s['email'],
                'gst_no'           => $s['gst_no'],
                'pan_no'           => $s['pan_no'],
                'company_name'     => $s['company_name'],
                'credit_limit'     => $s['credit_limit'],
                'credit_days'      => $s['credit_days'],
                'category'         => 'business',
                'outstanding_balance' => 0,
                'orders_count'     => 0,
                'is_active'        => true,
                'status'           => 'active',
                'account_type_id'  => $supplierAccTypeId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::table('party_addresses')->insert([
                'party_id'       => $partyId,
                'label'          => 'Registered Office',
                'address_line_1' => $s['address'],
                'village_id'     => null,
                'city'           => $s['city'],
                'state'          => $s['state'],
                'pincode'        => $s['pincode'],
                'is_default'     => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        $this->command->info('✅ PartyDataSeeder: ' . count($customers) . ' customers & ' . count($suppliers) . ' suppliers seeded.');
    }
}
