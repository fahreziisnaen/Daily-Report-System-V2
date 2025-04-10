<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Departemen dengan nama yang masuk akal
        $departments = [
            [
                'name' => 'IT',
                'code' => 'IT',
                'description' => 'Information Technology Department'
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Finance Department'
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Marketing Department'
            ],
            [
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'Operations Department'
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Human Resources Department'
            ],
            [
                'name' => 'Sales',
                'code' => 'SLS',
                'description' => 'Sales Department'
            ],
            [
                'name' => 'Production',
                'code' => 'PRD',
                'description' => 'Production Department'
            ],
            [
                'name' => 'Research',
                'code' => 'R&D',
                'description' => 'Research and Development Department'
            ],
            [
                'name' => 'Logistics',
                'code' => 'LOG',
                'description' => 'Logistics and Supply Chain Department'
            ],
            [
                'name' => 'Maintenance',
                'code' => 'MNT',
                'description' => 'Maintenance and Facilities Department'
            ]
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
} 