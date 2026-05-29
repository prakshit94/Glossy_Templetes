<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'        => 'WELCOME10',
                'type'        => 'percentage',
                'value'       => 10.00,
                'min_spend'   => 500.00,
                'max_discount'=> 200.00,
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'usage_limit' => 200,
                'used_count'  => 0,
                'status'      => 'active',
                'is_active'   => true,
            ],
            [
                'code'        => 'KHARIF25',
                'type'        => 'percentage',
                'value'       => 25.00,
                'min_spend'   => 2000.00,
                'max_discount'=> 500.00,
                'expiry_date' => now()->addMonths(3)->toDateString(),
                'usage_limit' => 100,
                'used_count'  => 12,
                'status'      => 'active',
                'is_active'   => true,
            ],
            [
                'code'        => 'SEED500',
                'type'        => 'fixed',
                'value'       => 500.00,
                'min_spend'   => 3000.00,
                'max_discount'=> null,
                'expiry_date' => now()->addMonths(4)->toDateString(),
                'usage_limit' => 50,
                'used_count'  => 7,
                'status'      => 'active',
                'is_active'   => true,
            ],
            [
                'code'        => 'RABI2026',
                'type'        => 'percentage',
                'value'       => 15.00,
                'min_spend'   => 1500.00,
                'max_discount'=> 350.00,
                'expiry_date' => now()->addMonths(5)->toDateString(),
                'usage_limit' => 150,
                'used_count'  => 3,
                'status'      => 'active',
                'is_active'   => true,
            ],
            [
                'code'        => 'FLAT200',
                'type'        => 'fixed',
                'value'       => 200.00,
                'min_spend'   => 1000.00,
                'max_discount'=> null,
                'expiry_date' => now()->addMonths(2)->toDateString(),
                'usage_limit' => 300,
                'used_count'  => 45,
                'status'      => 'active',
                'is_active'   => true,
            ],
            [
                'code'        => 'FERT10',
                'type'        => 'percentage',
                'value'       => 10.00,
                'min_spend'   => 800.00,
                'max_discount'=> 150.00,
                'expiry_date' => now()->addMonths(3)->toDateString(),
                'usage_limit' => 500,
                'used_count'  => 88,
                'status'      => 'active',
                'is_active'   => true,
            ],
            [
                'code'        => 'NANO50',
                'type'        => 'fixed',
                'value'       => 50.00,
                'min_spend'   => 240.00,
                'max_discount'=> null,
                'expiry_date' => now()->addMonth()->toDateString(),
                'usage_limit' => 1000,
                'used_count'  => 210,
                'status'      => 'active',
                'is_active'   => true,
            ],
            [
                'code'        => 'MONSOON30',
                'type'        => 'percentage',
                'value'       => 30.00,
                'min_spend'   => 5000.00,
                'max_discount'=> 1000.00,
                'expiry_date' => now()->subDays(10)->toDateString(), // expired
                'usage_limit' => 50,
                'used_count'  => 50,
                'status'      => 'inactive',
                'is_active'   => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            DB::table('coupons')->updateOrInsert(
                ['code' => $coupon['code']],
                array_merge($coupon, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('✅ CouponSeeder: ' . count($coupons) . ' coupons seeded.');
    }
}
