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
        // Get departments
        $departments = Department::all();
        $mainDepartment = $departments->first();
        
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'position' => 'Super Admin',
            'department_id' => $mainDepartment->id,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        // Create a Vice President for each department
        foreach ($departments as $department) {
            $vicePresident = User::create([
                'name' => 'VP ' . $department->name,
                'email' => 'vp.' . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                'password' => Hash::make('password'),
                'position' => 'Vice President',
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            $vicePresident->assignRole('Vice President');
            
            // Create an Admin Divisi for each department
            $adminDivisi = User::create([
                'name' => 'Admin ' . $department->name,
                'email' => 'admin.' . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                'password' => Hash::make('password'),
                'position' => 'Manager',
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            $adminDivisi->assignRole('Admin Divisi');
            
            // Create a Verifikator for each department
            $verifikator = User::create([
                'name' => 'Verifikator ' . $department->name,
                'email' => 'verifikator.' . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                'password' => Hash::make('password'),
                'position' => 'Supervisor',
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            $verifikator->assignRole('Verifikator');
            
            // Create 2 employees for each department
            for ($i = 1; $i <= 2; $i++) {
                $employee = User::create([
                    'name' => "Employee $i - " . $department->name,
                    'email' => "employee$i." . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                    'password' => Hash::make('password'),
                    'position' => 'Staff',
                    'department_id' => $department->id,
                    'is_active' => true,
                ]);
                $employee->assignRole('Employee');
            }
        }
    }
} 