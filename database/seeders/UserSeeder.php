<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first department
        $department = Department::first();

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'position' => 'Super Admin',
            'department_id' => $department->id,
        ]);
        $superAdmin->assignRole('Super Admin');

        // Create Admin Divisi
        $adminDivisi = User::create([
            'name' => 'Admin Divisi',
            'email' => 'admindivisi@example.com',
            'password' => Hash::make('password'),
            'position' => 'Manager',
            'department_id' => $department->id,
        ]);
        $adminDivisi->assignRole('Admin Divisi');

        // Create Employees
        $employees = [
            [
                'name' => 'Employee 1',
                'email' => 'employee1@example.com',
                'password' => Hash::make('password'),
                'position' => 'Staff',
                'department_id' => $department->id,
            ],
            [
                'name' => 'Employee 2',
                'email' => 'employee2@example.com',
                'password' => Hash::make('password'),
                'position' => 'Staff',
                'department_id' => $department->id,
            ],
        ];

        foreach ($employees as $employee) {
            $user = User::create($employee);
            $user->assignRole('Employee');
        }
    }
} 