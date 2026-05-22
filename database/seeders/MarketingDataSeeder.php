<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketingDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();
        $products = DB::table('products')->get();
        $primaryProduct = $products->first();

        // 1. Coupons
        DB::table('coupons')->updateOrInsert(
            ['code' => 'WELCOME10'],
            [
                'type' => 'percentage',
                'value' => 10.00,
                'min_spend' => 500,
                'expiry_date' => now()->addMonths(3),
                'usage_limit' => 100,
                'used_count' => 0,
                'status' => 'active',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 2. Backend-managed offers
        DB::table('offers')->updateOrInsert(
            ['name' => 'Auto 10% Off on Orders Above 5000'],
            [
                'type' => 'order_discount',
                'discount_type' => 'percentage',
                'value' => 10.00,
                'min_spend' => 5000.00,
                'max_discount' => 2000.00,
                'product_id' => null,
                'buy_qty' => 1,
                'get_qty' => 1,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
                'priority' => 100,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if ($primaryProduct) {
            DB::table('offers')->updateOrInsert(
                ['name' => 'Buy 1 Get 1 on ' . $primaryProduct->name],
                [
                    'type' => 'bogo',
                    'discount_type' => null,
                    'value' => 0,
                    'min_spend' => 0,
                    'max_discount' => null,
                    'product_id' => $primaryProduct->id,
                    'buy_qty' => 1,
                    'get_qty' => 1,
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(3),
                    'priority' => 90,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // 3. Campaigns
        $campId = DB::table('campaigns')->insertGetId([
            'name' => 'Summer Sale 2026',
            'type' => 'email',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'budget' => 10000.00,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($users->take(5) as $user) {
            DB::table('email_marketing_logs')->insert([
                'campaign_id' => $campId,
                'user_id' => $user->id,
                'sent_at' => now(),
                'opened_at' => now()->addHours(1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Support Tickets
        foreach ($users->take(2) as $user) {
            DB::table('support_tickets')->insert([
                'user_id' => $user->id,
                'subject' => 'Order delay issue',
                'description' => 'My order SO-123 is delayed by 3 days.',
                'priority' => 'high',
                'status' => 'open',
                'assigned_to' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Product Reviews
        foreach ($products->take(5) as $product) {
            DB::table('product_reviews')->insert([
                'product_id' => $product->id,
                'user_id' => $users->first()->id,
                'rating' => rand(4, 5),
                'comment' => 'Excellent product, highly recommend!',
                'is_verified_purchase' => true,
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
