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

        $permissions = array_unique(array_merge(
            [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                'permissions.view', 'permissions.manage',
                'reports.view', 'reports.export',
                'billing.view', 'billing.manage',
                'audit.view',
                'teams.view', 'teams.manage',
                'settings.view', 'settings.edit',

                'orders.view', 'view_all_order', 'orders.create', 'orders.edit', 'orders.delete',
                'orders.confirm', 'orders.processing', 'orders.ship', 'orders.dispatch', 'orders.deliver', 'orders.cancel','orders.revert_status',
                'orders.generate_invoice', 'orders.invoice_pdf', 'orders.cod', 'orders.receipt',
                'orders.bulk_status', 'orders.bulk_print',
                'orders.filter_product', 'orders.filter_fulfillment', 'orders.filter_status', 'orders.filter_state',
                'orders.filter_district', 'orders.filter_taluka', 'orders.filter_carrier', 'orders.filter_sort', 'orders.filter_date',

                'villages.view', 'villages.create', 'villages.edit', 'villages.delete', 'villages.import',
                'services.view', 'services.create', 'services.edit', 'services.delete',
            ],
            $this->modulePermissions()
        ));

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

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
     * All CRUD permissions per sidebar module.
     *
     * @return list<string>
     */
    private function modulePermissions(): array
    {
        $modules = [
            'customers',
            'customer-groups',
            'reviews',
            'support-tickets',

            'products',
            'categories',
            'brands',
            'attributes',
            'uoms',
            'tax-rates',
            'hsn-codes',

            'inventory',
            'warehouses',
            'stock-transfers',
            'stock-adjustments',

            'orders',
            'invoices',
            'payments',
            'order-tracking',
            'returns',
            'refunds',
            'replacement',

            'purchase-orders',
            'suppliers',
            'vendors',

            'transport',
            'delivery',
            'shipment-tracking',
            'drivers',

            'accounts',
            'expenses',
            'transactions',
            'financial-reports',
            'sales-reports',
            'inventory-reports',
            'customer-analytics',
            'performance-reports',

            'employees',
            'attendance',
            'payroll',
            'departments',

            'campaigns',
            'coupons',
            'offers',
            'email-marketing',
        ];

        $permissions = [];
        foreach ($modules as $module) {
            $permissions[] = "{$module}.view";
            $permissions[] = "{$module}.create";
            $permissions[] = "{$module}.edit";
            $permissions[] = "{$module}.delete";
        }

        return $permissions;
    }
}
