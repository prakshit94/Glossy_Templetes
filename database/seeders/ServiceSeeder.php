<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'code' => 'LMD_POST',
                'name' => 'LMD Post',
                'description' => 'Last Mile Delivery via Postal Service',
                'is_active' => true,
            ],
            [
                'code' => 'COD',
                'name' => 'Cash On Delivery',
                'description' => 'Support for Cash on Delivery payments',
                'is_active' => true,
            ],
            [
                'code' => 'PICKUP',
                'name' => 'Pickup Service',
                'description' => 'Support for parcel pickup from village',
                'is_active' => true,
            ],
            [
                'code' => 'EXPRESS',
                'name' => 'Express Delivery',
                'description' => 'Guaranteed fast delivery service',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['code' => $service['code']], $service);
        }
    }
}
