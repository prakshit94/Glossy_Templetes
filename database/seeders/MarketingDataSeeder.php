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

        // 1. Coupons
        DB::table('coupons')->insert([
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10.00,
            'min_spend' => 500,
            'expiry_date' => now()->addMonths(3),
            'usage_limit' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Campaigns
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

        // 3. Support Tickets
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

        // 4. Product Reviews
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
