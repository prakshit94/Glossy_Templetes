<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketingDataSeeder extends Seeder
{
    public function run(): void
    {
        $users    = DB::table('users')->get();
        $products = DB::table('products')->get();

        if ($users->isEmpty()) {
            $this->command->warn('⚠️  MarketingDataSeeder: No users found. Skipping.');
            return;
        }

        // ─── 1. Email Campaigns ───────────────────────────────────────────────
        $campaigns = [
            [
                'name'       => 'Kharif Season Launch 2026',
                'type'       => 'email',
                'start_date' => now()->subDays(10),
                'end_date'   => now()->addMonths(2),
                'budget'     => 25000.00,
                'status'     => 'active',
            ],
            [
                'name'       => 'Nano Urea Awareness Drive – May 2026',
                'type'       => 'email',
                'start_date' => now()->subDays(5),
                'end_date'   => now()->addMonths(1),
                'budget'     => 15000.00,
                'status'     => 'active',
            ],
            [
                'name'       => 'Cotton Seed Pre-Booking Campaign',
                'type'       => 'sms',
                'start_date' => now()->subDays(20),
                'end_date'   => now()->subDays(5),
                'budget'     => 10000.00,
                'status'     => 'completed',
            ],
        ];

        foreach ($campaigns as $campaign) {
            $campId = DB::table('campaigns')->insertGetId(array_merge($campaign, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Log emails for first campaign
            if ($campaign['status'] === 'active') {
                foreach ($users->take(5) as $user) {
                    DB::table('email_marketing_logs')->insert([
                        'campaign_id' => $campId,
                        'user_id'     => $user->id,
                        'sent_at'     => now()->subDays(rand(1, 5)),
                        'opened_at'   => rand(0, 1) ? now()->subDays(rand(0, 3)) : null,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }

        // ─── 2. Support Tickets ───────────────────────────────────────────────
        $tickets = [
            [
                'subject'     => 'Order SO-202605-0001 not delivered yet',
                'description' => 'I placed an order 15 days back but have not received it. Please check and expedite delivery.',
                'priority'    => 'high',
                'status'      => 'open',
            ],
            [
                'subject'     => 'Wrong product dispatched – received Rogor instead of Confidor',
                'description' => 'I ordered Confidor 200 SL but received Rogor 30 EC. Please arrange replacement.',
                'priority'    => 'high',
                'status'      => 'in_progress',
            ],
            [
                'subject'     => 'Coupon code KHARIF25 not working at checkout',
                'description' => 'I am trying to apply coupon KHARIF25 but system shows invalid coupon error.',
                'priority'    => 'medium',
                'status'      => 'open',
            ],
            [
                'subject'     => 'Request for product application guidance – Amistar Top',
                'description' => 'Can you provide dosage recommendations for Amistar Top 325 SC on tomato crop?',
                'priority'    => 'low',
                'status'      => 'resolved',
            ],
        ];

        foreach ($tickets as $i => $ticket) {
            $user = $users->get($i % $users->count());
            DB::table('support_tickets')->insert(array_merge($ticket, [
                'user_id'     => $user->id,
                'assigned_to' => null,
                'created_at'  => now()->subDays(rand(1, 10)),
                'updated_at'  => now(),
            ]));
        }

        // ─── 3. Product Reviews ───────────────────────────────────────────────
        $reviews = [
            [
                'product_sku' => 'UPL-CON-200SL',
                'rating'      => 5,
                'comment'     => 'Excellent result on cotton crop. Aphid population completely controlled within 3 days of spraying. Highly recommended.',
            ],
            [
                'product_sku' => 'DPT-COR-20SC',
                'rating'      => 5,
                'comment'     => 'Coragen is outstanding for stem borer control in paddy. One spray gave protection for 20+ days. Worth every rupee.',
            ],
            [
                'product_sku' => 'IFFCO-NANO-UREA',
                'rating'      => 4,
                'comment'     => 'Good results on wheat. Noticed greener leaves and better tillering after foliar spray. Convenient to carry compared to urea bags.',
            ],
            [
                'product_sku' => 'MHC-COT-7918',
                'rating'      => 4,
                'comment'     => 'Good germination rate. Boll setting is excellent and the plant is vigorous. Yield was around 28 quintals/acre.',
            ],
            [
                'product_sku' => 'SYN-AMT-325SC',
                'rating'      => 5,
                'comment'     => 'Amistar Top controlled blast and brown spot simultaneously in paddy. Excellent dual action fungicide.',
            ],
            [
                'product_sku' => 'HW-SPR-NEPT16L',
                'rating'      => 4,
                'comment'     => 'Battery backup is good, lasts entire day for a 2-acre field. Motor is powerful. Build quality could be better.',
            ],
            [
                'product_sku' => 'IFFCO-NPK-2020',
                'rating'      => 5,
                'comment'     => 'Very good water-soluble fertilizer. Dissolved completely without any residue. Crop responded well within a week.',
            ],
        ];

        foreach ($reviews as $review) {
            $product = DB::table('products')->where('sku', $review['product_sku'])->first();
            if (!$product) continue;

            $user = $users->random();
            DB::table('product_reviews')->insert([
                'product_id'            => $product->id,
                'user_id'               => $user->id,
                'rating'                => $review['rating'],
                'comment'               => $review['comment'],
                'is_verified_purchase'  => true,
                'status'                => 'approved',
                'created_at'            => now()->subDays(rand(2, 20)),
                'updated_at'            => now(),
            ]);
        }

        $this->command->info('✅ MarketingDataSeeder: ' . count($campaigns) . ' campaigns, ' . count($tickets) . ' tickets, ' . count($reviews) . ' reviews seeded.');
    }
}
