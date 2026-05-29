<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LogisticsDataSeeder extends Seeder
{
    public function run(): void
    {
        $orders    = DB::table('orders')->where('type', 'sale')
                       ->whereIn('status', ['shipped', 'delivered', 'ready_to_ship', 'dispatched'])
                       ->get();
        $warehouse = DB::table('warehouses')->where('is_default', true)->first()
                  ?? DB::table('warehouses')->first();
        $user      = DB::table('users')->where('username', 'admin')->first()
                  ?? DB::table('users')->first();

        if (!$warehouse || !$user) {
            $this->command->warn('⚠️  LogisticsDataSeeder: Missing warehouse or user. Skipping.');
            return;
        }

        // ─── 1. Transport Vehicles ────────────────────────────────────────────
        $transports = [
            [
                'name'            => 'Tata Ace – Flipkart Express',
                'vehicle_number'  => 'MH-12-AB-3456',
                'type'            => 'Mini Truck',
                'capacity_weight' => 750.00,
                'status'          => 'available',
            ],
            [
                'name'            => 'Mahindra Bolero Pickup',
                'vehicle_number'  => 'MH-09-CD-7890',
                'type'            => 'Pickup Truck',
                'capacity_weight' => 1200.00,
                'status'          => 'available',
            ],
            [
                'name'            => 'Ashok Leyland Dost',
                'vehicle_number'  => 'GJ-01-EF-2345',
                'type'            => 'Light Commercial',
                'capacity_weight' => 1000.00,
                'status'          => 'on_delivery',  // valid: available|on_delivery|maintenance|inactive
            ],
        ];

        $transportIds = [];
        foreach ($transports as $t) {
            $transportIds[] = DB::table('transports')->insertGetId(array_merge($t, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ─── 2. Drivers ───────────────────────────────────────────────────────
        // drivers.user_id is UNIQUE — use one user per driver; grab up to 3 users
        $allUsers  = DB::table('users')->take(3)->get();
        $driverData = [
            ['license' => 'MH0420250012345', 'phone' => '9011002201', 'status' => 'available'],
            ['license' => 'GJ0120240056789', 'phone' => '9011002202', 'status' => 'available'],
            ['license' => 'MH1220230098765', 'phone' => '9011002203', 'status' => 'busy'],  // valid: available|busy|on_leave|inactive
        ];

        $driverIds = [];
        foreach ($driverData as $idx => $d) {
            $driverUser = $allUsers->get($idx) ?? $user;
            // Skip if user already has a driver record
            $existing = DB::table('drivers')->where('user_id', $driverUser->id)->value('id');
            if ($existing) {
                $driverIds[] = $existing;
                continue;
            }
            $driverIds[] = DB::table('drivers')->insertGetId([
                'user_id'        => $driverUser->id,
                'license_number' => $d['license'],
                'phone'          => $d['phone'],
                'status'         => $d['status'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // ─── 3. Per-Order: PickList → Package → Shipment → Delivery ──────────
        $carriers = ['Delhivery', 'BlueDart', 'DTDC', 'Ecom Express', 'Xpressbees'];

        foreach ($orders as $idx => $order) {
            $orderItem = DB::table('order_items')->where('order_id', $order->id)->first();
            if (!$orderItem) continue;

            $driverId    = $driverIds[$idx % count($driverIds)];
            $transportId = $transportIds[$idx % count($transportIds)];
            $carrier     = $carriers[$idx % count($carriers)];

            // Pick List
            $pickId = DB::table('pick_lists')->insertGetId([
                'pick_no'      => 'PICK-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'warehouse_id' => $warehouse->id,
                'assigned_to'  => $user->id,
                'status'       => 'completed',
                'created_at'   => now()->subDays(3),
                'updated_at'   => now(),
            ]);

            DB::table('pick_list_items')->insert([
                'pick_list_id'    => $pickId,
                'order_item_id'   => $orderItem->id,
                'quantity_to_pick'=> $orderItem->quantity,
                'quantity_picked' => $orderItem->quantity,
                'created_at'      => now()->subDays(3),
                'updated_at'      => now(),
            ]);

            // Package
            $packageId = DB::table('packages')->insertGetId([
                'package_no' => 'PKG-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'order_id'   => $order->id,
                'weight'     => round(rand(5, 30) / 10, 1),
                'dimensions' => rand(20, 40) . 'x' . rand(15, 30) . 'x' . rand(10, 20) . ' cm',
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ]);

            DB::table('package_items')->insert([
                'package_id'   => $packageId,
                'order_item_id'=> $orderItem->id,
                'quantity'     => $orderItem->quantity,
                'created_at'   => now()->subDays(2),
                'updated_at'   => now(),
            ]);

            // Shipment
            $trackingNo = strtoupper(Str::random(3)) . rand(100000000, 999999999) . 'IN';
            $shipmentId = DB::table('shipments')->insertGetId([
                'shipment_no'  => 'SHP-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'order_id'     => $order->id,
                'carrier_name' => $carrier,
                'tracking_no'  => $trackingNo,
                'status'       => $order->status === 'delivered' ? 'delivered' : 'shipped',
                'shipped_at'   => now()->subDays(1),
                'created_at'   => now()->subDays(1),
                'updated_at'   => now(),
            ]);

            // Shipment tracking events
            $events = [
                ['event_name' => 'Order Picked',  'location' => $warehouse->city ?? 'Pune',    'description' => 'Package picked from warehouse',              'days' => 3],
                ['event_name' => 'In Transit',    'location' => 'Sorting Hub – Mumbai',        'description' => 'Package received at sorting hub',            'days' => 2],
                ['event_name' => 'Out for Delivery','location' => 'Local Delivery Hub',        'description' => 'Package out for last-mile delivery',         'days' => 1],
            ];

            if ($order->status === 'delivered') {
                $events[] = ['event_name' => 'Delivered', 'location' => 'Customer Address', 'description' => 'Package successfully delivered to customer', 'days' => 0];
            }

            foreach ($events as $event) {
                DB::table('shipment_tracking_events')->insert([
                    'shipment_id' => $shipmentId,
                    'event_name'  => $event['event_name'],
                    'location'    => $event['location'],
                    'description' => $event['description'],
                    'occurred_at' => now()->subDays($event['days']),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // Delivery
            DB::table('deliveries')->insert([
                'shipment_id'  => $shipmentId,
                'driver_id'    => $driverId,
                'transport_id' => $transportId,
                'status'       => $order->status === 'delivered' ? 'delivered' : 'out_for_delivery',
                'created_at'   => now()->subDays(1),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('✅ LogisticsDataSeeder: ' . $orders->count() . ' orders processed with pick lists, packages, shipments, tracking & deliveries.');
    }
}
