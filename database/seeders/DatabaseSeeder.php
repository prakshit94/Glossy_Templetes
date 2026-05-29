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
     *
     * Execution order matters — each seeder depends on data from the previous ones.
     *
     * Layer 1 – Auth & Permissions  : RolesAndPermissionsSeeder, MasterAdminSeeder
     * Layer 2 – Reference/Lookup    : VillageSeeder, ServiceSeeder, AgricultureLookupSeeder
     * Layer 3 – Master Data         : MasterDataSeeder (brands, categories, tax, HSN, UOM, account types)
     * Layer 4 – Agriculture Catalog : AgricultureDataSeeder (extra agri brands, categories, attributes)
     * Layer 5 – Warehouses          : WarehouseSeeder
     * Layer 6 – Products            : ProductDataSeeder
     * Layer 7 – Parties             : PartyDataSeeder (customers, suppliers, groups)
     * Layer 8 – Inventory           : InventoryDataSeeder (stock, batches, movements)
     * Layer 9 – Orders              : OrderDataSeeder (sale & purchase orders)
     * Layer 10 – Logistics          : LogisticsDataSeeder (transport, drivers, shipments)
     * Layer 11 – Accounting         : AccountingDataSeeder (ledgers, invoices, payments)
     * Layer 12 – HR                 : HRDataSeeder (departments, employees, payroll)
     * Layer 13 – Marketing          : CouponSeeder, OfferSeeder, MarketingDataSeeder
     */
    public function run(): void
    {
        // ── Layer 1: Auth & Roles ────────────────────────────────────────────
        $this->call([
            RolesAndPermissionsSeeder::class,
            MasterAdminSeeder::class,
        ]);

        // Create 10 additional users so HR, Logistics & other modules
        // have enough distinct users to assign without unique-key collisions
        \App\Models\User::factory(10)->create();

        // ── Layer 2: Lookup / Reference tables ───────────────────────────────
        $this->call([
            VillageSeeder::class,
            ServiceSeeder::class,
            AgricultureLookupSeeder::class,
        ]);

        // ── Layer 3: Master catalog data ──────────────────────────────────────
        $this->call([
            MasterDataSeeder::class,       // brands, categories, tax rates, HSN, UOM, account types
            AgricultureDataSeeder::class,  // additional agri brands, categories, attributes, HSN
        ]);

        // ── Layer 4: Warehouses ───────────────────────────────────────────────
        $this->call([
            WarehouseSeeder::class,
        ]);

        // ── Layer 5: Products ─────────────────────────────────────────────────
        $this->call([
            ProductDataSeeder::class,
        ]);

        // ── Layer 6: Parties (Customers & Suppliers) ──────────────────────────
        $this->call([
            PartyDataSeeder::class,
        ]);

        // ── Layer 7: Inventory ────────────────────────────────────────────────
        $this->call([
            InventoryDataSeeder::class,
        ]);

        // ── Layer 8: Orders (Sale & Purchase) ────────────────────────────────
        $this->call([
            OrderDataSeeder::class,
        ]);

        // ── Layer 9: Logistics ────────────────────────────────────────────────
        $this->call([
            LogisticsDataSeeder::class,
        ]);

        // ── Layer 10: Accounting ──────────────────────────────────────────────
        $this->call([
            AccountingDataSeeder::class,
        ]);

        // ── Layer 11: HR ──────────────────────────────────────────────────────
        $this->call([
            HRDataSeeder::class,
        ]);

        // ── Layer 12: Marketing, Coupons & Offers ────────────────────────────
        $this->call([
            CouponSeeder::class,
            OfferSeeder::class,
            MarketingDataSeeder::class,
        ]);
    }
}
