<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            MasterAdminSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'status' => 'active',
        ]);

        // Create 25 dummy users to test pagination and filters
        User::factory(25)->create();

        // New Advanced Module Seeders
        $this->call([
            VillageSeeder::class,
            ServiceSeeder::class,
            MasterDataSeeder::class,
            AgricultureLookupSeeder::class,
            ProductDataSeeder::class,
            PartyDataSeeder::class,
            InventoryDataSeeder::class,
            OrderDataSeeder::class,
            LogisticsDataSeeder::class,
            AccountingDataSeeder::class,
            HRDataSeeder::class,
            MarketingDataSeeder::class,
        ]);
    }
}
