<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HRDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();

        // 1. Departments
        $deptIds = [];
        $depts = ['Management', 'Sales', 'Logistics', 'Warehouse', 'Finance'];
        foreach ($depts as $dept) {
            $deptIds[] = DB::table('departments')->insertGetId([
                'name' => $dept,
                'manager_id' => $users->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Employee Profiles
        foreach ($users as $user) {
            $empId = DB::table('employee_profiles')->insertGetId([
                'user_id' => $user->id,
                'department_id' => $deptIds[array_rand($deptIds)],
                'employee_code' => 'EMP-' . Str::random(5),
                'designation' => 'Staff',
                'joining_date' => now()->subYears(1),
                'salary' => rand(20000, 50000),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Attendance (Last 7 days)
            for ($i = 0; $i < 7; $i++) {
                DB::table('attendance')->insert([
                    'employee_id' => $empId,
                    'date' => now()->subDays($i)->format('Y-m-d'),
                    'clock_in' => '09:00:00',
                    'clock_out' => '18:00:00',
                    'status' => 'present',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Payroll
            DB::table('payroll')->insert([
                'employee_id' => $empId,
                'month' => now()->month,
                'year' => now()->year,
                'basic_salary' => 30000,
                'bonus' => 2000,
                'deductions' => 500,
                'net_salary' => 31500,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
