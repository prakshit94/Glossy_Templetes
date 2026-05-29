<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HRDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = DB::table('users')->where('username', 'admin')->first()
                  ?? DB::table('users')->first();

        // ─── 1. Departments ───────────────────────────────────────────────────
        $departments = [
            ['name' => 'Management',       'description' => 'Senior leadership and strategy'],
            ['name' => 'Sales & Marketing','description' => 'Field sales, tele-sales and promotions'],
            ['name' => 'Warehouse Ops',    'description' => 'Inventory, packing and dispatch'],
            ['name' => 'Logistics',        'description' => 'Delivery fleet and routing'],
            ['name' => 'Finance',          'description' => 'Accounts, billing and compliance'],
            ['name' => 'Customer Support', 'description' => 'Helpdesk and complaint resolution'],
            ['name' => 'IT',               'description' => 'Software, infrastructure and security'],
        ];

        $deptIds = [];
        foreach ($departments as $dept) {
            $deptIds[$dept['name']] = DB::table('departments')->insertGetId([
                'name'       => $dept['name'],
                'manager_id' => $adminUser?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ─── 2. Employee Profiles ─────────────────────────────────────────────
        $employees = [
            ['name' => 'Aakash Verma',     'dept' => 'Sales & Marketing', 'designation' => 'Senior Sales Executive',       'salary' => 35000, 'joining' => now()->subYears(2)],
            ['name' => 'Deepika Sharma',   'dept' => 'Finance',           'designation' => 'Accounts Manager',             'salary' => 45000, 'joining' => now()->subYears(3)],
            ['name' => 'Ravi Bhandari',    'dept' => 'Warehouse Ops',     'designation' => 'Warehouse Supervisor',         'salary' => 28000, 'joining' => now()->subMonths(18)],
            ['name' => 'Priya Nair',       'dept' => 'Customer Support',  'designation' => 'Customer Support Executive',   'salary' => 22000, 'joining' => now()->subMonths(12)],
            ['name' => 'Kunal Joshi',      'dept' => 'Logistics',         'designation' => 'Logistics Coordinator',        'salary' => 30000, 'joining' => now()->subYears(1)],
            ['name' => 'Sunita Yadav',     'dept' => 'Sales & Marketing', 'designation' => 'Field Sales Officer',          'salary' => 25000, 'joining' => now()->subMonths(8)],
            ['name' => 'Manoj Tiwari',     'dept' => 'IT',                'designation' => 'IT Administrator',             'salary' => 40000, 'joining' => now()->subYears(2)],
        ];

        // Use app users or create employee entries against the admin user
        $users = DB::table('users')->get();

        // Track already-used user_ids to avoid unique constraint violation
        $usedUserIds = [];

        foreach ($employees as $i => $emp) {
            // Find next available user not already assigned as an employee
            $user = null;
            foreach ($users as $candidate) {
                if (!in_array($candidate->id, $usedUserIds)) {
                    $user = $candidate;
                    break;
                }
            }

            // If all users are already assigned, skip remaining employees
            if (!$user) {
                $this->command->warn("⚠️  No free user for employee #{$i} ({$emp['designation']}). Skipping.");
                continue;
            }

            $usedUserIds[] = $user->id;
            $deptKey = $emp['dept'];
            $deptId  = $deptIds[$deptKey] ?? array_values($deptIds)[0];
            $empCode = 'EMP-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $empId = DB::table('employee_profiles')->insertGetId([
                'user_id'       => $user->id,
                'department_id' => $deptId,
                'employee_code' => $empCode,
                'designation'   => $emp['designation'],
                'joining_date'  => $emp['joining']->format('Y-m-d'),
                'salary'        => $emp['salary'],
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // ─── 3. Attendance – last 7 working days ─────────────────────────
            for ($day = 0; $day < 7; $day++) {
                $date   = now()->subDays($day);
                $dayOfWeek = $date->dayOfWeek;

                // Skip Sunday (0)
                if ($dayOfWeek === 0) continue;

                $isPresent = rand(0, 9) > 1; // 90% attendance

                DB::table('attendance')->insert([
                    'employee_id' => $empId,
                    'date'        => $date->format('Y-m-d'),
                    'clock_in'    => $isPresent ? '09:' . str_pad(rand(0, 15), 2, '0', STR_PAD_LEFT) . ':00' : null,
                    'clock_out'   => $isPresent ? '18:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00' : null,
                    'status'      => $isPresent ? 'present' : 'absent',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // ─── 4. Payroll – current month ───────────────────────────────────
            $bonus      = $i === 0 ? 3000 : 0; // Sales exec gets incentive
            $deductions = round($emp['salary'] * 0.12); // PF deduction
            $netSalary  = $emp['salary'] + $bonus - $deductions;

            DB::table('payroll')->insert([
                'employee_id'  => $empId,
                'month'        => now()->month,
                'year'         => now()->year,
                'basic_salary' => $emp['salary'],
                'bonus'        => $bonus,
                'deductions'   => $deductions,
                'net_salary'   => $netSalary,
                'status'       => 'paid',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('✅ HRDataSeeder: ' . count($departments) . ' departments, ' . count($employees) . ' employees with attendance & payroll seeded.');
    }
}
