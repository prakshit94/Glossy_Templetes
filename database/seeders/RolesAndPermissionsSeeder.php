<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = array_merge(
            [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                'permissions.view', 'permissions.manage',
                'reports.view', 'reports.export',
                'billing.view', 'billing.manage',
                'audit.view',
                'teams.view', 'teams.manage',
                'settings.view', 'settings.edit',

                'villages.view', 'villages.create', 'villages.edit', 'villages.delete', 'villages.import',
                'services.view', 'services.create', 'services.edit', 'services.delete',
            ],
            $this->moduleViewPermissions()
        );

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(array_merge(
            [
                'users.view', 'users.create', 'users.edit',
                'roles.view', 'teams.view', 'teams.manage',
                'permissions.view',
                'reports.view', 'reports.export',
                'audit.view', 'settings.view', 'settings.edit',
                'villages.view', 'villages.create', 'villages.edit', 'villages.import',
                'services.view', 'services.create', 'services.edit',
            ],
            $this->moduleViewPermissions()
        ));

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'users.view',
            'reports.view', 'reports.export',
            'villages.view', 'services.view',
            'customers.view', 'customer-groups.view', 'reviews.view', 'support-tickets.view',
            'products.view', 'categories.view', 'brands.view', 'attributes.view', 'uoms.view', 'tax-rates.view', 'hsn-codes.view',
            'inventory.view', 'warehouses.view', 'stock-transfers.view', 'stock-adjustments.view',
            'orders.view', 'invoices.view', 'payments.view', 'order-tracking.view', 'returns.view', 'refunds.view', 'replacement.view',
            'purchase-orders.view', 'suppliers.view', 'vendors.view',
            'transport.view', 'delivery.view', 'shipment-tracking.view', 'drivers.view',
            'accounts.view', 'expenses.view', 'transactions.view',
            'financial-reports.view', 'sales-reports.view', 'inventory-reports.view', 'customer-analytics.view', 'performance-reports.view',
            'employees.view', 'attendance.view', 'payroll.view', 'departments.view',
            'campaigns.view', 'coupons.view', 'email-marketing.view',
        ]);

        $employee = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'reports.view',
            'villages.view',
            'customers.view',
            'orders.view',
        ]);

        Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    }

    /**
     * One "view" permission per sidebar module (index / read access).
     *
     * @return list<string>
     */
    private function moduleViewPermissions(): array
    {
        return [
            'customers.view',
            'customer-groups.view',
            'reviews.view',
            'support-tickets.view',

            'products.view',
            'categories.view',
            'brands.view',
            'attributes.view',
            'uoms.view',
            'tax-rates.view',
            'hsn-codes.view',

            'inventory.view',
            'warehouses.view',
            'stock-transfers.view',
            'stock-adjustments.view',

            'orders.view',
            'invoices.view',
            'payments.view',
            'order-tracking.view',
            'returns.view',
            'refunds.view',
            'replacement.view',

            'purchase-orders.view',
            'suppliers.view',
            'vendors.view',

            'transport.view',
            'delivery.view',
            'shipment-tracking.view',
            'drivers.view',

            'accounts.view',
            'expenses.view',
            'transactions.view',
            'financial-reports.view',
            'sales-reports.view',
            'inventory-reports.view',
            'customer-analytics.view',
            'performance-reports.view',

            'employees.view',
            'attendance.view',
            'payroll.view',
            'departments.view',

            'campaigns.view',
            'coupons.view',
            'email-marketing.view',
        ];
    }
}
