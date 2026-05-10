<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Master Admin',
                'username' => 'admin',
                'password' => Hash::make('Admin@123456'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        $admin->assignRole('Super Admin');
    }
}
