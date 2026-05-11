<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LogisticsDataSeeder extends Seeder
{
    public function run(): void
    {
        $orders = DB::table('orders')->where('type', 'sale')->get();
        $warehouse = DB::table('warehouses')->first();
        $user = DB::table('users')->first();

        // 1. Transports & Drivers
        $transportId = DB::table('transports')->insertGetId([
            'name' => 'Fast Cargo',
            'vehicle_number' => 'MH-01-AB-1234',
            'type' => 'Truck',
            'capacity_weight' => 1000.00,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $driverId = DB::table('drivers')->insertGetId([
            'user_id' => $user->id,
            'license_number' => 'DL-99999999',
            'phone' => '9000000000',
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($orders as $order) {
            // 2. Pick Lists
            $pickId = DB::table('pick_lists')->insertGetId([
                'pick_no' => 'PICK-' . Str::random(6),
                'warehouse_id' => $warehouse->id,
                'assigned_to' => $user->id,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $orderItem = DB::table('order_items')->where('order_id', $order->id)->first();
            DB::table('pick_list_items')->insert([
                'pick_list_id' => $pickId,
                'order_item_id' => $orderItem->id,
                'quantity_to_pick' => $orderItem->quantity,
                'quantity_picked' => $orderItem->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Packages
            $packageId = DB::table('packages')->insertGetId([
                'package_no' => 'PKG-' . Str::random(6),
                'order_id' => $order->id,
                'weight' => 5.5,
                'dimensions' => '10x10x10',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('package_items')->insert([
                'package_id' => $packageId,
                'order_item_id' => $orderItem->id,
                'quantity' => $orderItem->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Shipments
            $shipmentId = DB::table('shipments')->insertGetId([
                'shipment_no' => 'SHP-' . Str::random(6),
                'order_id' => $order->id,
                'carrier_name' => 'Fast Cargo',
                'tracking_no' => 'TRK-' . rand(100000, 999999),
                'status' => 'shipped',
                'shipped_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('shipment_tracking_events')->insert([
                'shipment_id' => $shipmentId,
                'event_name' => 'Dispatched',
                'location' => 'Main Warehouse',
                'description' => 'Shipment left the warehouse',
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Deliveries
            DB::table('deliveries')->insert([
                'shipment_id' => $shipmentId,
                'driver_id' => $driverId,
                'transport_id' => $transportId,
                'status' => 'out_for_delivery',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
