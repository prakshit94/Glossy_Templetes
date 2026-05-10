<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.manage',
            'reports.view', 'reports.export',
            'billing.view', 'billing.manage',
            'audit.view',
            'teams.view', 'teams.manage',
            'settings.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions via Gate::before in AuthServiceProvider

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo(['users.view', 'users.create', 'users.edit', 'reports.view', 'audit.view', 'settings.view']);

        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo(['users.view', 'reports.view']);

        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $employee->givePermissionTo(['reports.view']);

        $customer = Role::firstOrCreate(['name' => 'Customer']);
    }
}
