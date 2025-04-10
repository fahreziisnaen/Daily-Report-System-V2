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
            'homebase' => 'Head Office',
            'department_id' => $mainDepartment->id,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        // Find the HR Department for special HR role assignment
        $hrDepartment = $departments->where('name', 'Human Resources')->first();
        
        // Create the HR Manager (only for HR Department)
        if ($hrDepartment) {
            $hrManager = User::create([
                'name' => 'HR Manager',
                'email' => 'hr.manager@example.com',
                'password' => Hash::make('password'),
                'position' => 'HR Manager',
                'homebase' => 'Head Office',
                'department_id' => $hrDepartment->id,
                'is_active' => true,
            ]);
            $hrManager->assignRole('Human Resource');
        }

        // Create a Vice President for each department
        foreach ($departments as $department) {
            // Create a Vice President
            $vicePresident = User::create([
                'name' => 'VP ' . $department->name,
                'email' => 'vp.' . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                'password' => Hash::make('password'),
                'position' => 'Vice President',
                'homebase' => 'Head Office',
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            $vicePresident->assignRole('Vice President');
            
            // Create an Admin Divisi
            $adminDivisi = User::create([
                'name' => 'Admin ' . $department->name,
                'email' => 'admin.' . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                'password' => Hash::make('password'),
                'position' => 'Department Manager',
                'homebase' => 'Department Office',
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            $adminDivisi->assignRole('Admin Divisi');
            
            // Create a Verifikator
            $verifikator = User::create([
                'name' => 'Verifikator ' . $department->name,
                'email' => 'verifikator.' . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                'password' => Hash::make('password'),
                'position' => 'Quality Assurance Supervisor',
                'homebase' => 'Department Office',
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            $verifikator->assignRole('Verifikator');
            
            // Create employee titles and roles based on department
            $employeeTitles = $this->getEmployeeTitles($department->name);
            
            // Create 2 employees for each department with specific titles
            for ($i = 1; $i <= 2; $i++) {
                $title = $employeeTitles[$i-1] ?? 'Staff';
                $employee = User::create([
                    'name' => "{$title} {$i} - {$department->name}",
                    'email' => "employee{$i}." . strtolower(str_replace(' ', '', $department->name)) . '@example.com',
                    'password' => Hash::make('password'),
                    'position' => $title,
                    'homebase' => 'Department Office',
                    'department_id' => $department->id,
                    'is_active' => true,
                ]);
                $employee->assignRole('Employee');
            }
        }
    }
    
    /**
     * Get employee titles based on department name
     */
    private function getEmployeeTitles($departmentName): array
    {
        // Define department-specific job titles
        $titles = [
            'IT' => ['Software Developer', 'Network Engineer', 'System Analyst', 'IT Support'],
            'Finance' => ['Accountant', 'Financial Analyst', 'Auditor', 'Treasurer'],
            'Marketing' => ['Marketing Specialist', 'Content Creator', 'Digital Marketer', 'Brand Manager'],
            'Operations' => ['Operations Officer', 'Logistics Coordinator', 'Process Analyst', 'Quality Controller'],
            'Human Resources' => ['Recruitment Specialist', 'Training Coordinator', 'HR Assistant', 'Compensation Analyst'],
            'Sales' => ['Sales Executive', 'Account Manager', 'Business Developer', 'Sales Representative'],
            'Production' => ['Production Supervisor', 'Quality Control Officer', 'Plant Operator', 'Production Technician'],
            'Research' => ['Research Analyst', 'Data Scientist', 'Lab Technician', 'Research Assistant'],
            'Logistics' => ['Logistics Coordinator', 'Supply Chain Analyst', 'Warehouse Supervisor', 'Inventory Specialist'],
            'Maintenance' => ['Maintenance Engineer', 'Facility Technician', 'Equipment Specialist', 'Repair Technician']
        ];
        
        // Get titles for this department or default to generic titles
        $departmentTitles = $titles[str_replace('Department', '', trim($departmentName))] ?? ['Staff', 'Officer', 'Specialist', 'Assistant'];
        
        // Shuffle to randomize which titles are used
        shuffle($departmentTitles);
        
        return $departmentTitles;
    }
} 